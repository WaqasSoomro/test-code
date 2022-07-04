<?php

namespace App;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;

class Assignment extends Model
{
    use SoftDeletes;

    public $timestamps = true;

    public $media = 'media/assignments';

    public $locale;

    private $stdClass, $crashMessage;

    private $defaultLocale;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->locale = App::getLocale();

        $this->defaultLocale = 'en';

        $this->stdClass = new stdClass();

        $this->crashMessage = 'Something wen\'t wrong';
    }

    public function getTitleAttribute($value){

        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function getDescriptionAttribute($value){
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function getDifficultyLevelAttribute($value){
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function author()
    {
        return $this->belongsTo('App\User', 'trainer_user_id');
    }

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'assignment_exercises', 'assignment_id', 'exercise_id')->withPivot('level_id','sort_order');
    }

    public function post()
    {
        return $this->hasOne(Post::class);
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'player_assignments', 'assignment_id', 'player_user_id')->withPivot('status_id');
    }

    public function player_exercises()
    {
        return $this->belongsToMany(User::class, 'player_exercise', 'assignment_id', 'user_id')->withPivot('status_id');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'assignment_skills', 'assignment_id', 'skill_id');
    }

    public function lines()
    {
        return $this->belongsToMany(Line::class, 'assignment_lines');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'assign_to');
    }

    public function store($request)
    {
        DB::transaction(function () use ($request)
        {
            $status = Status::where('name', 'pending')->first();

            $this->trainer_user_id = $request->trainer_user_id;
            $this->title = $request->title;
            $this->assign_to = $request->team_id;
            $this->description = $request->description;
            $this->deadline = $request->deadline;

            if (Storage::exists($this->image) && $request->hasFile('image')) {
                Storage::delete($this->image);
            }

            $path = "";
            if ($request->hasFile('image')) {
                $path = Storage::putFile($this->media, $request->image);
            }
            $this->image = $path;

            $this->save();

            if($request->skill_ids) {
                $skill_ids = explode(',', $request->skill_ids);

                $this->skills()->sync($skill_ids);
            } else {
                $this->skills()->sync([]);
            }

            $this->lines()->sync($request->lines);

            $this->players()->sync($request->players);

            $players = $this->players;
            
            foreach ($players as $playerIndex => $player)
            {
                $playerAssignment = PlayerAssignment::where('assignment_id', $this->id)
                ->where('player_user_id', $player->id)
                ->first();

                if (!$playerAssignment)
                {
                    $playerAssignment = new PlayerAssignment();

                    $playerAssignment->assignment_id = $this->id;
                    $playerAssignment->player_user_id = $player->player_user_id;
                }

                $playerAssignment->status_id = $status->id ?? NULL;
                $playerAssignment->save();

                $data = [];
                $data['from_user_id'] = auth()->user()->id;
                $data['to_user_id'] = $player->player_user_id;
                $data['model_type'] = 'assignment/assigned';
                $data['model_type_id'] = $this->id;
                $data['click_action'] = 'AssignmentsDetail';
                $data['message']['en'] = 'You have a new assignment by ' . auth()->user()->first_name.' '.auth()->user()->last_name;
                $data['message']['nl'] = 'Je hebt een nieuwe opdracht van ' . auth()->user()->first_name.' '.auth()->user()->last_name;
                $data['message'] = json_encode($data['message']);
                $data['badge_count'] = $player->badge_count + 1;

                foreach ($player->user_devices as $device)
                {
                    Helper::sendNotification($data, $device->onesignal_token,$device->device_type);
                }
            }

            User::whereIn('id', $request->players)->increment('badge_count', 1);
        });

        return $this;
    }

    public static function copy($assignment)
    {
        $clone = $assignment->replicate();
        $clone->save();

        foreach ($assignment->exercises as $exercise){
            $clone->exercises()->attach($exercise, [
                'level_id' => $exercise->pivot->level_id,
                'sort_order' => $exercise->pivot->sort_order
            ]);
        }

        foreach ($assignment->skills as $skill){
            $clone->skills()->attach($skill);
        }

        $author_name = $assignment->author->first_name .' '. $assignment->author->last_name;

        $notification_data = [];

        foreach ($assignment->players as $player){
            $clone->players()->attach($player, ['status_id' => $player->pivot->status_id]);

            $data = [];
            $data['from_user_id'] = $clone->trainer_user_id;
            $data['to_user_id'] = $player->id;
            $data['model_type'] = 'assignment/assigned';
            $data['model_type_id'] = $clone->id;
            $data['click_action'] = 'AssignmentsDetail';
            $data['message']['en'] = 'You have a new assignment by ' . $author_name;
            $data['message']['nl'] = 'Je hebt een nieuwe opdracht van ' . $author_name;
            $data['message'] = json_encode($data['message']);
            $data['badge_count'] = $player->badge_count + 1;

            array_push($notification_data, $data);

            foreach ($player->user_devices as $device) {
                Helper::sendNotification($data, $device->onesignal_token,$device->device_type);
            }
        }

        User::whereIn('id', $assignment->players->pluck('id'))->increment('badge_count', 1);

    }

    public function create($request)
    {
        DB::beginTransaction();

        try
        {
            if ($request->id)
            {
                $assignment = $this::where('id', $request->id)
                ->first();

                if ($assignment)
                {
                    $status = 1;
                }
                else
                {
                    $status = 0;
                }
            }
            else
            {
                $assignment = $this;
                
                $status = 1;
            }

            if ($status == 1)
            {
                
            }

            
            DB::commit();
        }
        catch (Exception $ex)
        {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, $this->crashMessage, $this->stdClass);
        }
    }

    public function getAssignmentDetail($request,$columns){
        $status = Status::where('name', 'completed')->first();

        $assignment = Assignment::select($columns)
        ->with([
            'players' => function ($query) use($status, $request)
            {
                $query->select('users.id', 'users.first_name', 'users.last_name', 'users.profile_picture')
                ->withCount([
                    'exercises as completed_exercises' => function ($query) use($request, $status)
                    {
                        $query->select(DB::raw('count(distinct(exercise_id))'))
                        ->where('status_id', $status->id ?? 0)
                        ->where('assignment_id', $request->assignment_id);
                    },
                    'comments as total_comments' => function ($query) use ($request)
                    {
                        $query->where('assignment_id', $request->assignment_id ?? 0);
                    }
                ]);
            },
            'players.player_details' => function ($query)
            {
                $query->select('id', 'user_id');
            },
            'players.player_details.positions' => function ($query)
            {
                $query->select('positions.id', 'name', 'lines');
            },
            'players.player_details.positions.line' => function ($query)
            {
                $query->select('lines.id', 'name');
            },
            'players.teams' => function ($query)
            {
                $query->select('teams.id', 'teams.team_name');
            },
            'exercises' => function ($query)
            {
                $query->select('exercises.id', 'exercises.title');
            }
        ])
        ->withCount('exercises as total_exercises')
        ->where('assignments.id', $request->assignment_id)
        ->where('trainer_user_id',auth()->user()->id)
        ->first();

        if (!$assignment) {
            return ['status' => false, 'msg' => 'Assignment not found'];
        }

        $players_details = [];
        $pl_completed_count = 0;

        foreach ($assignment->players as $player) {

            $row = new stdClass();
            $row->id = $player->id;
            $row->first_name = $player->first_name;
            $row->last_name = $player->last_name;
            $row->profile_picture = $player->profile_picture;
            $row->positions = $player->player_details->positions ?? [];
            $row->team_name = $player->teams[0]->team_name ?? "";
            $row->total_exercises = $assignment->total_exercises;
            $row->status = DB::select('(SELECT DISTINCT
	CASE
    	WHEN (SELECT COUNT(DISTINCT (exercise_id)) as c FROM player_exercise as pe WHERE assignment_id = player_exercise.assignment_id AND status_id = 3 AND pe.user_id = player_exercise.user_id) > 0 AND (SELECT COUNT(DISTINCT (exercise_id)) as c FROM player_exercise as pe WHERE assignment_id = player_exercise.assignment_id AND status_id = 3 AND pe.user_id = player_exercise.user_id) < (SELECT COUNT(DISTINCT (exercise_id)) as c FROM `assignment_exercises` WHERE assignment_id = player_exercise.assignment_id AND user_id = player_exercise.user_id )  THEN \'started\'
        WHEN (SELECT COUNT(DISTINCT (exercise_id)) as c FROM player_exercise as pe WHERE assignment_id = player_exercise.assignment_id AND status_id = 3 AND pe.user_id = player_exercise.user_id) = (SELECT COUNT(*) FROM `assignment_exercises` WHERE assignment_id = player_exercise.assignment_id AND user_id = player_exercise.user_id ) THEN \'finished\'
    END as status FROM player_exercise WHERE assignment_id = ' . $assignment->id . ' AND user_id = ' . $player->id . ')')[0]->status ?? 'not-started';

            $row->completed_exercises = $player->completed_exercises;
            $row->total_comments = $player->total_comments ?? 0;

            $pl_ex_count = PlayerExercise::where('assignment_id', $request->assignment_id)
                ->where('user_id', $player->id)->where('status_id', 3)
                ->distinct('assignment_id', 'user_id', 'exercise_id')
                ->count();

            $asign_ex_count = AssignmentExercise::where('assignment_id', $request->assignment_id)->count();

            if ($pl_ex_count == $asign_ex_count) {
                $pl_completed_count++;
            }

            array_push($players_details, $row);
        }

        $stats['player_assigned'] = PlayerAssignment::where('assignment_id', $request->assignment_id)->count();
        $stats['player_completed'] = $pl_completed_count;
        $stats['assignment_info'] = $assignment->description ?? "";

        $stats['overall_time'] = PlayerExercise::where('assignment_id', $request->assignment_id)->sum('completion_time');
        $stats['assignment_level'] = $assignment->difficulty_level ?? "";
        $stats['privacy_type'] = "public";

        $response['assignment_stats'] = $stats;
        $response['players_details'] = $players_details;
        $response['exercises'] = $assignment->exercises;

        return ['status' => true, 'data' => $response];
    }
}