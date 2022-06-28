<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Exercise extends Model
{
    use SoftDeletes;

    public $timestamps = true;
    public $exerciseImg = 'media/exercises/images';
    public $exercise_video = 'media/exercises/videos';

    public $locale;
    public $defaultLocale;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->locale = App::getLocale();
        $this->defaultLocale = 'en';
    }

    public function getTitleAttribute($value){

        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function getDescriptionAttribute($value){
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function getImageAttribute($value)
    {
        if(!Storage::exists($value)){
            return $value;
        }

        return $value;
    }

    public function getVideoAttribute($value)
    {
        if(!Storage::exists($value)){
            return $value;
        }

        return $value;
    }

    /**
     * The categories that belong to the exercise.
     */
    public function categories()
    {
        return $this->belongsToMany('App\Category', 'category_exercise', 'exercise_id', 'category_id');
    }

    public function assignments()
    {
        return $this->belongsToMany('App\Assignment', 'assignment_exercises', 'exercise_id', 'assignment_id')->withPivot('sort_order');
    }

    /**
     * Exercise Types
     */
    public function types(){
        return $this->belongsToMany("App\Type","exercise_types","exercise_id","type_id");
    }

    /**
     * The users that belong to the exercise.
     */
    public function leaderboard()
    {
        return $this->belongsToMany('App\User', 'player_exercise', 'exercise_id', 'user_id')->withPivot('level_id');
    }

    /**
     * A exercise belongs to many exercise levels
     */

    public function levels()
    {
            return $this->belongsToMany(Level::class, 'exercise_levels', 'exercise_id', 'level_id')->withPivot('measure');
    }

    /**
     * skills that belong to many exercises.
     */
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'exercise_skills', 'exercise_id', 'skill_id');
    }

    /**
     * teams that belong to many exercises.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'exercise_teams', 'exercise_id', 'team_id');
    }


    /**
     * The tools that belong to the exercise.
     */
    public function tools()
    {
        return $this->belongsToMany('App\Tool', 'exercise_tools', 'exercise_id', 'tool_id');
    }

    /**
     * The privacy that belong to the exercise.
     */
    public function exercise_privacy()
    {
        return $this->belongsTo('App\ExercisePrivacy', 'privacy', 'id');
    }

    /*
    * A exercise has many posts
    **/

    public function posts()
    {
        return $this->hasMany('App\Post','exercise_id');
    }


    /*
     * Getting skills from players_scores
     * Getting exercise - user socres
     */
    public function player_scores_users()
    {
        //return $this->belongsToMany('App\User','player_scores','exercise_id','user_id')->withPivot('exercise_id','exercise_level_id','score')->withTimestamps();
        return $this->belongsToMany('App\User','player_scores','exercise_id','user_id')->withPivot('exercise_id');
    }



    /**
     * Get the tips for the exercise.
     */
    public function exercise_tips()
    {
        return $this->hasMany('App\ExerciseTip');
    }

    public function getUserExerciseDetail($request,$exe,$ex){
        return $this::select('exercises.id', 'exercises.title', 'player_exercise.video_file', 'player_exercise.completion_time',
        'player_exercise.start_time', 'player_exercise.end_time', 'player_exercise.status_id', 'exercises.unit')
        ->whereHas('leaderboard', function ($q) use ($request) {
            $q->where('user_id', auth()->user()->id)->where('exercise_id', $request->exercise_id);
        })
        ->with([
            'leaderboard' => function ($query) use ($exe) {
                $query->select('users.id', 'users.first_name', 'users.last_name', 'users.middle_name', 'users.profile_picture', DB::raw("ROUND(completion_time) as completion_time"))->groupBy('user_id');

            },
            'leaderboard.player_scores_skills' => function ($query) use ($ex) {
                $query->select('player_scores.score')->groupBy('user_id')->where('exercise_id', $ex)->where('player_scores.score', '>', 0);

            }
        ])
        ->whereHas('leaderboard.player_scores_skills')
        ->with([
            'levels:levels.id,levels.title,levels.image,exercise_levels.measure,levels.status',
        ])
        ->join('player_exercise', 'player_exercise.exercise_id', '=', 'exercises.id')
        ->latest('player_exercise.updated_at')
        ->first();
    }

    public function getExerciseDetails($ex,$exe,$request,$leaderBoardCallBack,$whereHasLeaderBoardCallback = null){
        $status = Status::where('name', 'active')->first();

        $exercise = $this::select('exercises.id', 'exercises.title', 'exercises.description', 'exercises.image',
            'exercises.video', 'exercises.leaderboard_direction', 'exercises.badge', 'android_exercise_type', 'ios_exercise_type', 'score',
            'count_down_milliseconds', 'use_questions', 'selected_camera_facing', 'unit', 'android_exercise_variation', 'ios_exercise_variation', 'question_count', 'answer_count', 'camera_mode', 'nseconds', 'question_mode')
            ->with([
                'exercise_tips' => function ($query) use ($exe) {
                    $query->select('exercise_id', 'description', 'media', 'media_type', 'orientation');
                },
                'skills' => function ($query) {
                    $query->select('skills.id', 'skills.name');
                },
                'levels' => function ($query) {
                    $query->select('levels.id', 'title', 'image', 'measure', 'status');
                },
                'leaderboard' => $leaderBoardCallBack,
                'leaderboard.player_scores_skills' => function ($query) use ($ex) {
                    $query->select('player_scores.score')->groupBy('user_id')->where('exercise_id', $ex)->where('player_scores.score', '>', 0);

                }
            ])
            ->where('is_active', $status->id ?? 0);

        if (!empty($whereHasLeaderBoardCallback))
        {
            $exercise->whereHas('leaderboard.player_scores_skills',$whereHasLeaderBoardCallback);
        }
        
        $exercise = $exercise->find($request->exercise_id);
        
        return $exercise;
    }
}