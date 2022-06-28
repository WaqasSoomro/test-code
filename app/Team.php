<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use stdClass;
use App\Helpers\Helper;

class Team extends Model
{
    use SoftDeletes, HasRoles;
    
    public $timestamps = true;

    public $media = 'media/teams';

    protected $fillable = ['team_name', 'min_age_group', 'max_age_group', 'image'];
        
    private $stdClass;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->stdClass = new stdClass();
    }

    /**
        The users that belong to the team.
    */

    public function users()
    {
        return $this->belongsToMany('App\User', 'player_team', 'team_id', 'user_id');
    }

    /**
     * A Team Has Many Requests
     */
    public function requests(){
        return $this->hasMany(PlayerTeamRequest::class,"team_id","id");
    }

    /**
        The users that belong to the team.
    */

    public function trainers()
    {
        return $this->belongsToMany('App\User', 'team_trainers', 'team_id', 'trainer_user_id')
        ->withPivot('created_at');
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_teams');
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'player_team', 'team_id', 'user_id');
    }

    /**
        exercises that belong to many teams.
    */

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'exercise_teams', 'team_id', 'exercise_id');
    }

    public function subscription()
    {
        return $this->hasOne(TeamSubscription::class,'team_id','id')
        ->whereStatus('1')
        ->orderBy('id','DESC')
        ->with('plan');
    }

    public function store($request)
    {
        $this->team_name = $request->team_name;
        $this->description = $request->description;
        //$this->age_group = $request->age_group;
        $this->min_age_group = $request->min_age_group;
        $this->max_age_group = $request->max_age_group;

        if (Storage::exists($this->image) && $request->hasFile('image')) {
            Storage::delete($this->image);
        }

        if(isset($request->team_type)){
            $this->team_type = $request->team_type;
        }

        // IF TEAM PRIVACY GIVEN
        if (isset($request->team_privacy))
        {
            $this->privacy = $request->team_privacy;
        }
        else{
            //IF NOT GIVEN
            $this->privacy = "1";
        }

        if(isset($request->gender)){
            $this->gender = $request->gender;
        }
        $path = "";
        if ($request->hasFile('image')) {
            $path = Storage::putFile($this->media, $request->image);
            $this->image = $path;
        }
        $this->save();
        return $this;
    }

    public function remove($id, $apiType)
    {
        DB::beginTransaction();

        try
        {
            $team = $this::with('trainers', 'trainers.clubs_trainers')
            ->whereHas('trainers', function ($query)
            {
                $query->where('trainer_user_id', auth()->user()->id);
            })
            ->where('id', $id)
            ->first();

            if ($team)
            {
                $trainers = $team->trainers()->pluck('trainer_user_id')->toArray();

                $team->trainers()->sync([]);

                $newClub = Club::with('trainers')
                ->where('title', 'like', '%jogo%')
                ->first();

                $newTeam = $this::with('trainers')
                ->where('team_name', 'like', '%jogo%')
                ->first();

                if ($newClub)
                {
                    $newClub->trainers()->syncWithoutDetaching($trainers);
                }

                if ($newTeam)
                {
                    $newTeam->trainers()->syncWithoutDetaching($trainers);
                }

                $team->delete();

                DB::commit();

                $response = Helper::apiSuccessResponse(true, 'Record has been deleted successfully', $this->stdClass);
            }
            else
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid Id', $this->stdClass);
            }
        }
        catch (Exception $e)
        {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function removeTrainer($request, $clubId, $teamId, $trainerId)
    {
        DB::beginTransaction();

        try
        {
            $team = $this::select('id')
            ->with([
                'clubs' => function ($query) use($clubId)
                {
                    $query->select('clubs.id', 'team_id')
                    ->where('clubs.id', $clubId);
                },
                'trainers' => function ($query) use($trainerId)
                {
                    $query->select('users.id', 'team_id')
                    ->where('users.id', $trainerId);
                } 
            ])
            ->where('id', $teamId)
            ->first();
            
            if ($team->clubs->count() < 1 && $team->trainers->count() < 1)
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid team id', $this->stdClass);
            }
            else if ($team->clubs->count() < 1)
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid club id', $this->stdClass);
            }
            else if ($team->trainers->count() < 1)
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid trainer id', $this->stdClass);
            }
            else
            {
                $team->trainers()->detach($trainerId);

                DB::commit();

                $response = Helper::apiSuccessResponse(true, 'You\'ve successfully deleted trainer from your team', $this->stdClass);
            }
        }
        catch (Exception $ex)
        {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function getTeamName($teamId)
    {
        $team = $this::select('id', 'team_name')
        ->where('id', $teamId)
        ->first();

        $team = $team->team_name ?? "";

        return $team;
    }

    public function isTrainer($request,$user_id,$club_id,$team_id){
        $clubs = (new Club())->myCLubs($request);
        $club_ids = [];
        foreach ($clubs->original["Result"] as $club)
        {
            $club_ids[] = $club['id'];
        }
        if(!in_array($club_id,$club_ids)){
            return ['status' => false, 'msg' => 'Add Club First'];
        }

        $team = Team::whereHas("trainers",function ($query) use
        ($request,$user_id,$team_id)
        {
            $query->where("trainer_user_id",$user_id)->where("team_id",$team_id);
        })->first();


        if (!$team)
        {
            return ['status' => false, 'msg' => "Trainer Not Added In The Team"];
        }

        return ['status' => true, 'team' => $team];
    }

    public function getTeamObject($team){
        $obj = new \stdClass();
        $obj->id = $team->id;
        $obj->team_name = $team->team_name;
        $obj->image = $team->image;
        $obj->gender = $team->gender;
        $obj->team_type = $team->team_type;
        $obj->description = $team->description;
        $obj->age_group = $team->age_group;
        $obj->privacy = $team->privacy;
        $obj->min_age_group = $team->min_age_group;
        $obj->max_age_group = $team->max_age_group;
        $obj->sensors = 0; // HARDCODED FOR NOW
        $obj->total_assignments = $team->players->sum("player_assignments_count"); // SUM ALL THE ASSIGNMENTS
        $obj->total_requests = DB::table("player_team_requests")->where("team_id",$team->id)->count(); // GET THE TOTAL REQUEST SENT TO THIS TEAM
        $obj->players_count = count($team->players); // COUNT TOTAL NUMBERS OF PLAYERS IN THE TEAM
        $plan = 'freemium';
        if ($team->subscription != null) {
            $plan = $team->subscription->plan->role->name;
        }
        $obj->subscription = $plan;

        return $obj;
    }
}