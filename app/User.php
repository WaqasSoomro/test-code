<?php

namespace App;

use App\Http\Resources\Api\Dashboard\Teams\Players\IndexResource as GeneralPlayersResource;
use App\Http\Resources\Api\ParentSharing\Profile\IndexResource as ParentsProfileResource;
use App\Http\Resources\Api\ParentSharing\Players\ListingResource as ParentsPlayersListingResource;
use App\Http\Resources\Api\Dashboard\ParentSharing\ListingResource as PlayersParentsListingResource;
use App\Http\Resources\Api\Dashboard\Profile\IndexResource as TrainersProfileResource;
use App\Http\Resources\Api\Dashboard\Clubs\Trainers\EditResource as EditTrainerResource;
use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use function React\Promise\Stream\first;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Exception;
use stdClass;
use Hash;
use Mail;

class User extends Authenticatable
{
    public static $media_path = 'media/users';

    public $timestamps = true;

    /**
     * spatie - laravel-permission
     */

    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'first_name', 'middle_name', 'last_name', 'email', 'phone', 'gender', 'date_of_birth', 'age', 'address', 'language',
        'profile_picture', 'verification_code', 'password', 'active', 'activation_token', 'device_type', 'device_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    protected $hidden = [
        'password', 'remember_token', 'activation_token'
    ];

    public function nationality()
    {
        return $this->belongsTo(Country::class, 'nationality_id');
    }

    public static $get_player_profile_rules = [
        'id' => 'required|exists:users,id',
    ];

    public static $create_player_profile_rules = [
        'follower_id' => 'required|exists:users,id',
        'following_id' => 'required|exists:users,id',
    ];

    public static $get_player_privacy_settings_rules = [
        'user_id' => 'required|exists:users,id',
    ];

    public static $get_player_recommended_exercises_rules = [
        'id' => 'required|exists:users,id',
        'skill_id' => 'required|exists:skills,id',
    ];

    public static $get_player_skill_insight_rules = [
        'user_id' => 'required|exists:users,id',
        'skill_id' => 'required|exists:skills,id',
        // 'period'  => 'in:daily,monthly,weekly,yearly'
    ];

    private $pendingStatus, $activeStatus, $parentRole, $trainerRole, $demoTrainerRole, $ownerRole, $stdClass, $newDeviceToken, $coupons, $userSubscriptions, $englishLanguage, $countries, $clubs, $teamModel;

    use Notifiable, HasApiTokens, SoftDeletes, HasRoles;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $pendingStatus = Status::select('id')
            ->where('name', 'pending')
            ->first();

        $activeStatus = Status::select('id')
            ->where('name', 'active')
            ->first();

        $parentRole = Role::select('id', 'name')
            ->where('name', 'parents')
            ->first();

        $trainerRole = Role::select('id', 'name')
            ->where('name', 'trainer')
            ->first();

        $demoTrainerRole = Role::select('id', 'name')
            ->where('name', 'demo_trainer')
            ->first();

        $this->coupons = Coupon::select('id', 'code', 'valid_from_date', 'valid_to_date', 'quantity', 'discount_trial_days');

        $englishLanguage = Language::select('id')
            ->where('code', 'en')
            ->where('status', 'active')
            ->first();

        $this->countries = Country::select('id', 'name', 'phone_code')
            ->orderBy('created_at', 'desc');

        $this->clubs = Club::with([
            'owner' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email')
                    ->withTrashed();
            },
            'trainers' => function ($query) {
                $query->select('trainer_user_id', 'first_name', 'last_name', 'email')
                    ->withTrashed();
            }
        ])
            ->select('id', 'owner_id', 'title')
            ->orderBy('created_at', 'desc');

        $this->userSubscriptions = new UserSubscription();

        if ($pendingStatus) {
            $this->pendingStatus = $pendingStatus;
        } else {
            $this->pendingStatus = new stdClass();
        }

        if ($activeStatus) {
            $this->activeStatus = $activeStatus;
        } else {
            $this->activeStatus = new stdClass();
        }

        if ($parentRole) {
            $this->parentRole = $parentRole;
        } else {
            $this->parentRole = new stdClass();
        }

        if ($trainerRole) {
            $this->trainerRole = $trainerRole;
        } else {
            $this->trainerRole = new stdClass();
        }

        if ($demoTrainerRole) {
            $this->demoTrainerRole = $demoTrainerRole;
        } else {
            $this->demoTrainerRole = new stdClass();
        }

        if ($englishLanguage) {
            $this->englishLanguage = $englishLanguage;
        } else {
            $this->englishLanguage = new stdClass();
        }

        $this->stdClass = new stdClass();

        $this->newDeviceToken = new UserDevice();

        $this->teamModel = new Team();
    }

    public function player_details()
    {
        return $this->hasOne('App\Player');
    }

    public function player()
    {
        return $this->hasOne('App\Player');
    }

    public function trainer()
    {
        return $this->hasOne('App\Trainer');
    }

    public function trainer_details()
    {
        return $this->hasOne('App\Trainer');
    }

    public function user_devices()
    {
        return $this->hasMany('App\UserDevice');
    }

    public function clubs_players()
    {
        return $this->belongsToMany('App\Club', 'club_players', 'player_user_id', 'club_id');
    }

    public function clubs_teams()
    {
        return $this->hasManyThrough(
            ClubTeam::class,
            ClubTrainer::class,
            "trainer_user_id",
            "club_id",
            "id",
            "club_id"
        );
    }

    public function clubs_trainers()
    {
        return $this->belongsToMany('App\Club', 'club_trainers', 'trainer_user_id', 'club_id');
    }

    public function user_sensors()
    {
        return $this->hasMany('App\UserSensor', 'user_id', 'id');
    }

    public function position()
    {
        return $this->belongsToMany('App\Position', 'players');
    }

    /*
    * User Actions are saved in activity log
    *
    *   Get the comments for the blog post.
    */

    public function activitylogs()
    {
        return $this->hasMany('App\Activitylog');
    }

    /*
    * A player(user) has many contacts
    **/

    public function contacts()
    {
        return $this->hasMany('App\Contact', 'user_id', 'id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'contacts', 'contact_user_id', 'user_id')->withPivot('status_id');
    }

    public function followings()
    {
        return $this->belongsToMany(User::class, 'contacts', 'user_id', 'contact_user_id')->withPivot('status_id');
        //return $this->belongsToMany('App\Contact','user_id');
    }

    /*
    * A player(user) has many posts
    **/

    public function posts()
    {
        return $this->hasMany('App\Post');
    }

    /*
    * A player(user) has many stories
    **/

    public function stories()
    {
        return $this->hasMany('App\Story');
    }

    /**
     * The teams that belong to the user.
     */

    public function teams()
    {
        return $this->belongsToMany('App\Team', 'player_team', 'user_id', 'team_id')->withPivot('created_at');
    }

    public function deleted_messages()
    {
        return $this->belongsToMany(ChatGroupMessage::class, 'chat_deleted_messages', 'deleted_by', 'message_id');
    }

    /**
     * The teams that belong to the user.
     */

    public function teams_trainers()
    {
        return $this->belongsToMany('App\Team', 'team_trainers', 'trainer_user_id', 'team_id')->withPivot('created_at');
    }

    /**
     * The exercises that belong to the user.
     */

    public function exercises()
    {
        return $this->belongsToMany('App\Exercise', 'player_exercise', 'user_id', 'exercise_id')->withPivot('level_id', 'status_id');
    }

    /**
     * The achievements that belong to the user
     */

    public function achievements()
    {
        return $this->belongsToMany('App\Achievement', 'player_achievement', 'user_id', 'achievement_id');
    }

    /**
     * The leagues that belong to the user.
     */

    public function leagues()
    {
        return $this->belongsToMany('App\League', 'leaderboards', 'user_id', 'league_id')->with('users');
    }

    public function leaderboards()
    {
        return $this->hasOne('App\Leaderboard', 'user_id');
    }

    /**
     * A user has many user_notifications
     **/

    public function user_notifications()
    {
        return $this->hasMany('App\UserNotification');
    }

    /**
     * A user(trainer) has many assignments
     *  Trainer can make numerous assignments
     **/

    public function assignments()
    {
        return $this->hasMany('App\Assignment', 'trainer_user_id');
    }

    /**
     * A user(player) has many assignments
     * assignments that assigned by trainer
     **/

    public function player_assignments()
    {
        return $this->belongsToMany(Assignment::class, 'player_assignments', 'player_user_id', 'assignment_id');
    }

    /**
     * The exercise_levels that belong to the user (player).
     */

    public function exercise_levels()
    {
        return $this->belongsToMany('App\ExerciseLevel', 'player_scores', 'user_id', 'level_id');
    }

    /**
     * Getting skills from players_scores
     */

    public function player_scores_skills()
    {
        return $this->belongsToMany('App\Skill', 'player_scores', 'user_id', 'skill_id')->withPivot('exercise_id', 'level_id', 'score')->withTimestamps();
    }

    public function player_exercises()
    {
        return $this->belongsToMany(Exercise::class, 'player_scores', 'user_id', 'exercise_id')->withPivot('skill_id', 'score')->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'contact_id');
    }

    public function player_scores_exercise()
    {
        return $this->belongsToMany('App\Skill', 'player_scores', 'user_id', 'exercise_id')->withPivot('exercise_id', 'exercise_level_id', 'score')->withTimestamps();
    }

    /*
    * A player(user) has many user_privacy_settings
    **/

    /**
     * Getting skills from players_scores
     */

    public function user_privacy_settings()
    {
        return $this->belongsToMany('App\AccessModifier', 'user_privacy_settings', 'user_id', 'access_modifier_id')->withPivot('id as user_privacy_setting_id', 'created_at');
    }

    /**
     * Human OX
     */

    /*
    * A user has many matches stats
    **/

    public function matches_stats()
    {
        return $this->hasMany('App\MatchStat', 'player_id');
    }

    public function matches()
    {
        return $this->hasMany(Match::class, 'user_id');
    }

    public function team_requests()
    {
        return $this->hasMany('App\PlayerTeamRequest', 'player_user_id', 'id');
    }

    public function player_parents()
    {
        return $this->belongsToMany(User::class, 'parent_players', 'parent_id', 'player_id');
    }

    public function parent_players()
    {
        return $this->belongsToMany(User::class, 'parent_players', 'player_id', 'parent_id');
    }

    public function user_language()
    {
        return $this->belongsTo(Language::class, 'language');
    }

    public function coupon()
    {
        return $this->hasOne(UserCoupon::class);
    }

    public function country_code()
    {
        return $this->belongsTo(Country::class, 'country_code_id');
    }

    public function package()
    {
        return $this->hasOne(UserPackage::class, 'user_id', 'id')->orderBy('id', 'DESC')->with('plan');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function direct_contacts($keyword = null)
    {
        $contacts = ChatContact::where(function ($query) {
            $query->where(function ($q) {
                $q->whereSender($this);
            })->orWhere(function ($q) {
                $q->whereReceiver($this);
            });
        })->get(['sender_id', 'receiver_id']);
        $recipients = $contacts->pluck('receiver_id')->all();
        $senders = $contacts->pluck('sender_id')->all();


        $result = $this->selectRaw('id, CONCAT(first_name," ",last_name) as name , profile_picture as picture, "user" as type')
            ->where('id', '!=', $this->id)
            ->whereIn('id', array_merge($recipients, $senders));
        if ($keyword) {
            $result = $result->where('first_name', 'LIKE', $keyword . '%')->orWhere('last_name', 'LIKE', $keyword . '%');
        }
        return $result;
    }


    public function getUserExerciseData($exerciseCallback,$hasExerciseCallback){
        $detail = $this::role('player')->select('users.id', 'users.nationality_id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.profile_picture', 'users.date_of_birth')
            ->with([
                'teams' => function ($q) {
                    $q->select('teams.id', 'teams.team_name', 'teams.image');
                },

            ])
            ->with([
                'nationality' => function ($q) {
                    $q->select('countries.id', 'countries.name');
                }
            ])
            ->with([
                'player' => function ($q1) {
                    $q1->select('players.id', 'players.user_id', 'players.position_id', 'players.customary_foot_id', 'players.height', 'players.weight', 'players.jersey_number');
                    $q1->with('customaryFoot:customary_feet.id,customary_feet.name');
                }
            ])
            ->with([
                'player.positions' => function ($query)
                {
                    $query->select('positions.id', 'name', 'lines');
                },
                'player.positions.line' => function ($query)
                {
                    $query->select('lines.id', 'name');
                }
            ])
            ->with([
                'leaderboards' => function ($q3) {
                    $q3->select('leaderboards.id', 'leaderboards.user_id', 'leaderboards.total_score', 'leaderboards.position');
                }
            ]);

        // IF GETTING EXERCISE DETAIL
        if ($hasExerciseCallback)
        {
            return $detail->with(["exercises"=>$exerciseCallback]);
        }
        // ELSE JUST RETURN USER DATA
        return $detail;
    }

    public function getUserWithEmailAndCode($request)
    {
        return $this::where('email', $request->email)->where('verification_code', $request->code)->whereHas('roles', function ($q) {
            $q->where('roles.name', 'trainer');
        })->first();
    }

    public function getTrainerClub(){
        $club = DB::table('club_trainers')
            ->where('trainer_user_id', auth()->user()->id)
            ->first();

        return $club->club_id ?? 0;

    }

    public function filters($request){
        $club_id = $this->getTrainerClub();

        if (isset($request->team_id)) {
            $filters = $this->getFiltersObject("teams",function ($q) use ($request) {
                $q->where('team_id', $request->team_id);
            });
        } else {
            $filters = $this->getFiltersObject("clubs_players",function ($q) use ($club_id) {
                $q->where('club_id', $club_id);
            });
        }
        return $filters;
    }

    public function getFiltersObject($relationName, $relationCallback){
        $players = $this::role('player')
            ->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name')
            ->whereHas($relationName, $relationCallback)->get();

        $filters = [
            'age_groups' => ['16', '20', '25', '30'],
            'positions' => Position::select('id', 'name')->get(),
            'players' => $players
        ];

        return $filters;
    }

    // FOR TRAINERAPP AND APPAUTH
    public function logoutUser($request){
        $user_device = UserDevice::where('imei', $request->device_identifier)
            ->orWhere('udid', $request->device_identifier)
            ->where('user_id', Auth::user()->id)
            ->first();

        if (!$user_device) {
            return Helper::apiNotFoundResponse(false, 'User not found', new stdClass());
        }

        $user_device->delete();
        $token = $request->user()->token();
        $token->revoke();

        return Helper::apiSuccessResponse(true, "You have been successfully logged out!", new stdClass());
    }

    public function registerWebUser($request, $sign_up = true)
    {

        DB::transaction(function () use ($request, $sign_up) {

            $status = Status::where('name', 'inactive')->first();

            $this->nationality_id = $request->nationality_id;
            $this->first_name = $request->first_name;
            $this->last_name = $request->last_name;

            if (isset($request->surname)) {
                $this->surname = $request->surname;
            }

            $this->email = $request->email;

            if (isset($request->phone) && !empty($request->phone)) {
                $this->phone = $request->phone;
            }

            if (isset($request->password)) {
                $this->password = bcrypt($request->password);
            }

            $this->verification_code = $request->verification_code;
            $this->status_id = $status->id ?? null;
            $this->who_created = Auth::user()->id ?? null;

            if (isset($request->add_explicitly) && ($request->add_explicitly == true)) {
                $this->verified_at = now();
                $this->verification_code = null;
            }
            $this->save();

            $user_device = new UserDevice();
            $user_device->user_id = $this->id;
            $user_device->device_type = $request->device_type;

            if (isset($request->ip)) {
                $user_device->ip = $request->ip;
            }

            if (isset($request->mac_id)) {
                $user_device->mac_id = $request->mac_id;
            }

            $user_device->save();

            // $team = Team::where('team_name','JOGO')->first();

            // if($team){
            //     $this->teams()->attach([$team->id]);
            // }

            // $club = Club::where('title','JOGO')->first();

            // if($club){
            //     $this->clubs_trainers()->attach($club);
            // }

            $role = Role::where('name', 'trainer')->first();
            if ($sign_up == true && $request->coupon != '') {
                $couponStatus = true;
                $coupon = Coupon::whereCode($request->coupon)->first();
                if (empty($coupon)) {
                    $couponStatus = false;
                    //return response()->json('Coupon not found');
                } else {
                    $today = date("Y-m-d");
                    $expire = $coupon->valid_to_date; //from database
                    $start = $coupon->valid_from_date; //from database

                    $today_dt = new \DateTime($today);
                    $start_dt = new \DateTime($start);
                    $expire_dt = new \DateTime($expire);

                    if ($start_dt >= $today_dt) {
                        $couponStatus = false;
                        //return response()->json('Coupon not started yet');
                    }

                    if ($expire_dt < $today_dt) {
                        $couponStatus = false;
                        //return response()->json('Coupon expire');
                    }

                    if ($coupon->quantity == 0) {
                        $couponStatus = false;
                        //return response()->json('Coupon limits exceded');
                    }
                }

                if ($couponStatus) {
                    $coupon->quantity = $coupon->quantity - 1;
                    $coupon->save();
                    $this->assignRole([$role, 'lite']);
                    $subscription = new userSubscriptions;
                    $subscription->user_id = $this->id;
                    $subscription->coupon_id = $coupon->id;
                    $subscription->no_of_days = $coupon->discount_trial_days;
                    $subscription->start_date = date('Y-m-d');
                    $subscription->end_date = date('Y-m-d', strtotime('+ ' . $coupon->discount_trial_days . ' days'));
                    $subscription->save();
                } else {
                    $this->assignRole([$role, 'freemium']);
                }
            } else {
                $this->assignRole([$role, 'freemium']);
            }
        });

        return $this;
    }

    public function registerPlayer($request)
    {

        DB::transaction(function () use ($request) {

            $status = Status::where('name', 'inactive')->first();
            $access_modifier = AccessModifier::where('name', 'public')->first();

            $this->first_name = $request->first_name ?? null;
            $this->last_name = $request->last_name ?? null;
            $this->date_of_birth = $request->date_of_birth ?? null;
            $this->country_code_id = $request->country_code_id;
            $this->phone = $request->phone;
            $this->status_id = $status->id ?? null;

            if (isset($request->age)) {
                $this->age = $request->age;
            }

            if (isset($request->gender)) {
                $this->gender = $request->gender;
            }

            if (isset($request->add_explicitly) && ($request->add_explicitly == true)) {
                $status = Status::where('name', 'active')->first();
                $this->verified_at = now();
                //$this->verification_code = $request->verification_code;
                $this->status_id = $status->id ?? null;

            }

            if (isset($request->country_code)) {
                $country_code_id = Country::wherePhoneCode($request->country_code)->first()->id;
                $this->country_code_id = $country_code_id;
            }

            $this->save();

            $user_device = UserDevice::where('user_id', $this->id)->first();
            if (!$user_device) {
                $user_device = new UserDevice();
            }
            $user_device->user_id = $this->id;
            $user_device->device_type = $request->device_type;
            $user_device->imei = ($request->device_type == 'android') ? $request->imei : null;
            $user_device->udid = ($request->device_type == 'ios') ? $request->udid : null;
            $user_device->ip = $_SERVER['REMOTE_ADDR'];
            $user_device->save();

            $role = Role::where('name', 'player')->first();
            $this->assignRole($role);

            if (isset($request->team_id) && !empty($request->team_id)) {
                $team_clubs = DB::table('club_teams')->where('team_id', $request->team_id)->get()->pluck('club_id');
                if (isset($request->club_manager) && $request->club_manager == 1) {
                    $this->clubs_players()->detach($team_clubs);
                    $this->teams()->detach([$request->team_id]);
                    $this->clubs_players()->attach($team_clubs);
                    $this->teams()->attach([$request->team_id]);
                } else {
                    $this->clubs_players()->sync($team_clubs);
                    $this->teams()->sync([$request->team_id], false);
                }
            } else {

                $team = Team::where('team_name', 'JOGO')->first();
                if ($team) {
                    $this->teams()->attach([$team->id]);
                }
                $club = Club::where('title', 'JOGO')->first();

                if ($club) {
                    $this->clubs_players()->attach($club);
                }
            }
            UserPrivacySetting::create([
                'user_id' => $this->id,
                'access_modifier_id' => $access_modifier->id
            ]);

        });

        return $this;
    }

    public function updatePlayerProfile($request)
    {
        DB::transaction(function () use ($request) {

            if (Storage::exists($this->profile_picture) && $request->profile_picture != "") {
                Storage::delete($this->profile_picture);
            }

            if ($request->hasFile('profile_picture')) {
                $this->profile_picture = Storage::putFile('media/users', $request->profile_picture);
            }

            if ($request->hasFile('cover_photo')) {
                $this->cover_photo = Storage::putFile('media/users', $request->cover_photo);
            }

            $this->first_name = $request->first_name;
            $this->last_name = $request->last_name;
            $this->date_of_birth = $request->date_of_birth;
            $this->nationality_id = $request->nationality_id;
            $this->gender = $request->gender;
            $this->username = $request->username;
            $this->save();

            $player = Player::where('user_id', $this->id)->first();

            if (!$player) {
                $player = new Player();
            }

            $player->user_id = $this->id;
            $player->height = $request->height;
            $player->weight = $request->weight;
            //$player->position_id = $request->position_id;
            $player->customary_foot_id = $request->customary_foot_id;
            $player->jersey_number = $request->jersey_number;
            $player->save();

            $player->positions()->sync($request->positionsId);
        });

        return $this;
    }

    public function getPlayers($request, $columns = '*', $limit = 10, $offset = 0, $sortingColumn = 'created_at', $sortingType = 'asc', array $status = [1, 2, 3])
    {
        try {
            $records = $this::select($columns)
                ->whereHas('player')
                ->whereIn('status_id', $status)
                ->orderBy($sortingColumn, $sortingType)
                ->limit($limit)
                ->offset($offset);

            if ($request->teamId) {
                $records->whereHas('teams', function ($query) use ($request) {
                    if (is_array($request->teamId)) {
                        $query->whereIn('team_id', $request->teamId);
                    } else {
                        $query->where('team_id', $request->teamId);
                    }
                });
            }

            if (is_array($request->positionsId) && current($request->positionsId) != 'all') {
                $records->whereHas('player.positions', function ($query) use ($request) {
                    $query->whereIn('positions.id', $request->positionsId);
                });
            }

            $totalRecords = $records->count();
            $players = $records->get();

            if ($totalRecords > 0) {
                if ($request->path() == 'api/v4/dashboard/clubs/teams/players/listing-by-positions'
                    || $request->path() == 'api/v1/trainerapp/teams/players') {
                    $records = GeneralPlayersResource::collection($players)->toArray($request);

                    $response = Helper::apiSuccessResponse(true, 'Records found successfully', $records);
                }
            } else {
                $response = Helper::apiNotFoundResponse(false, 'No records found', []);
            }
        } catch (Exception $ex) {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', []);
        }

        return $response;
    }

    public function playersGeneralColumns()
    {
        $columns = [
            'id',
            'first_name',
            'last_name',
            'profile_picture'
        ];

        return $columns;
    }

    public function user_subscriptions()
    {
        return $this->belongsToMany('App\Coupon', 'user_subscriptions', 'user_id', 'coupon_id');
    }

    public function removePlayerTeam($id, $apiType)
    {
        DB::beginTransaction();

        try {
            $clubIds = auth()->user()->clubs_trainers->pluck('id');

            $club = Club::with('teams')
                ->whereIn('id', $clubIds)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($club->teams) {
                $teamsId = $club->teams->pluck('id');
            } else {
                $teamsId[] = 0;
            }

            $record = $this::with('clubs_players', 'teams')
                ->where('id', $id)
                ->first();

            if ($record) {
                $record->clubs_players()->detach($clubIds);
                $record->teams()->detach($teamsId);

                $newClub = Club::where('title', 'like', '%jogo%')
                    ->first();

                $newTeam = Team::where('team_name', 'like', '%jogo%')
                    ->first();

                if ($newClub) {
                    $record->clubs_players()->syncWithoutDetaching([$newClub->id]);
                }

                if ($newTeam) {
                    $record->teams()->syncWithoutDetaching([$newTeam->id]);
                }

                DB::commit();

                $response = Helper::apiSuccessResponse(true, 'Record has deleted successfully', new stdClass());
            } else {
                $response = Helper::apiNotFoundResponse(false, 'Invalid Id', new stdClass());
            }
        } catch (Exception $e) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
        }

        return $response;
    }

    public function signUp($request)
    {
        DB::beginTransaction();

        try {
            $status = 1;

            //Find from deleted emails in users table
            $user = $this::where('email', $request->email)
                ->onlyTrashed()
                ->first();

            if (empty($user)) {
                //Create entry in users table
                $user = new $this;
            } else {
                $user->deleted_at = NULL;
            }

            $user->first_name = $request->firstName;
            $user->last_name = $request->lastName;
            $user->email = $request->email;
            $user->password = Hash::make($request->confirmPassword);
            $user->language = $this->englishLanguage->id ?? NULL;
            $user->verification_code = mt_rand(111111, 999999);
            $user->status_id = $this->pendingStatus->id ?? NULL;

            if ($request->path() == 'api/v4/dashboard/auth/sign-up') {
                $user->nationality_id = $request->nationalityId;
            }

            $user->save();

            //Create entry in user_devices table
            $userDevice = $this->newDeviceToken;

            $userDevice->user_id = $user->id;
            $userDevice->device_type = $request->deviceType;
            $userDevice->ip = $request->ip;
            $userDevice->mac_id = $request->macId;
            $userDevice->device_token = $request->deviceToken;
            $userDevice->save();

            if ($request->path() == 'api/v1/parent-sharing/auth/sign-up') {
                //Assign parents Id to parents players
                DB::table('parent_players')
                    ->where('parent_email', $request->email)
                    ->update([
                        'parent_id' => $user->id
                    ]);
            }

            if ($request->path() == 'api/v1/parent-sharing/auth/sign-up') {
                $roleName = $this->parentRole->name;
            } else {
                $roleName = $this->demoTrainerRole->name;
            }

            //Assign role
            $user->assignRole($roleName);

            if ($request->path() == 'api/v4/dashboard/auth/sign-up') {
                if (!empty($request->promoCode)) {
                    //find promoCode in coupons table
                    $coupon = $this->coupons->where('code', $request->promoCode)
                        ->first();

                    if (!empty($coupon)) {
                        if ($coupon->valid_from_date > date('Y-m-d')) {
                            $status = 3;
                        } else if ($coupon->valid_to_date < date('Y-m-d')) {
                            $status = 4;
                        }

                        if ($coupon->quantity == 0) {
                            $status = 5;
                        }

                        if ($status == 1) {
                            //Create entry in user_subscriptions table
                            $userSubscriptions = $this->userSubscriptions;

                            $userSubscriptions->user_id = $user->id;
                            $userSubscriptions->coupon_id = $coupon->id;
                            $userSubscriptions->no_of_days = $coupon->discount_trial_days;
                            $userSubscriptions->start_date = date('Y-m-d');
                            $userSubscriptions->end_date = date('Y-m-d', strtotime('+' . $coupon->discount_trial_days . ' days'));
                            $userSubscriptions->save();

                            $roleName = 'communication';
                        }
                    } else {
                        $status = 2;
                    }
                } else {
                    $roleName = 'freemium';
                }

                //Assign role
                $user->assignRole($roleName);
            }

            if ($status == 1) {
                $mailData = [
                    'user' => $user,
                    'otp_code' => $user->verification_code
                ];

                //send otp on email
                $sendEmail = Helper::sendMail('emails.send_otp', 'JOGO OTP-Code', $mailData, $user);
                
                /*if ($sendEmail == 'success')
                {*/
                DB::commit();

                $response = Helper::apiSuccessResponse(true, 'You\'ve sign up successfully', $this->stdClass);
                /*}
                else
                {
                    $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                }*/
            } else if ($status == 2) {
                $response = Helper::apiNotFoundResponse(false, 'Invalid promo code', $this->stdClass);
            } else if ($status == 3) {
                $response = Helper::apiNotFoundResponse(false, 'Coupon has not started yet', $this->stdClass);
            } else if ($status == 4) {
                $response = Helper::apiNotFoundResponse(false, 'Coupon has expired', $this->stdClass);
            } else if ($status == 5) {
                $response = Helper::apiNotFoundResponse(false, 'Coupon limit has reached', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function verifyOtp($request)
    {
        DB::beginTransaction();

        try {
            $record = $this::select('id', 'new_temp_email', 'verification_code', 'verified_at')
                ->where('email', $request->email);

            $record = ($this->getRecordsByRole($record,$request->path()))->first();

            if ($record) {
                if (empty($record->verification_code) && empty($record->verified_at)) {
                    $response = Helper::apiErrorResponse(false, 'Account is already verified', $this->stdClass);
                } else if ($record->verification_code == $request->otp) {
                    if ($request->path() == 'api/v4/dashboard/auth/profile/update/verify-otp' || $request->path() == 'api/v1/parent-sharing/auth/profile/update/verify-otp') {
                        $record->email = $record->new_temp_email;
                        $record->new_temp_email = NULL;
                    } else {
                        $record->verified_at = date('Y-m-d H:i:s');
                        $record->status_id = $this->activeStatus->id ?? NULL;
                    }

                    if ($request->path() != 'api/v4/dashboard/auth/forget-password/verify-otp') {
                        $record->verification_code = NULL;
                    }

                    $record->save();

                    DB::commit();

                    $response = Helper::apiSuccessResponse(true, 'Otp verified successfully', $this->stdClass);
                } else {
                    $response = Helper::apiNotFoundResponse(false, 'Invalid otp', $this->stdClass);
                }
            } else {
                $response = Helper::apiNotFoundResponse(false, 'Invalid email', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function resendOtp($request)
    {
        DB::beginTransaction();

        try {
            $record = $this::select('id', 'email', 'new_temp_email', 'first_name', 'last_name', 'email', 'verification_code')
                ->where('email', $request->email);

            $record = ($this->getRecordsByRole($record,$request->path()))->first();

            if ($record) {
                if (empty($record->verification_code) && empty($record->new_temp_email)) {
                    $response = Helper::apiErrorResponse(false, 'Account is already verified', $this->stdClass);
                } else {
                    if ($request->path() == 'api/v4/dashboard/auth/profile/update' || $request->path() == 'api/v1/parent-sharing/auth/profile/update') {
                        $record->email = $record->new_temp_email;
                        $record->new_temp_email = auth()->user()->email;
                    } else if (($request->path() == 'api/v4/dashboard/auth/profile/update/verify-otp' || $request->path() == 'api/v1/parent-sharing/auth/profile/update/verify-otp') && ($request->path() == 'api/v4/dashboard/auth/profile/update/resend-otp' || $request->path() == 'api/v1/parent-sharing/auth/profile/update/resend-otp')) {
                        $record->email = $request->newTempEmail;
                        $record->new_temp_email = auth()->user()->email;
                    }

                    $record->verification_code = mt_rand(111111, 999999);
                    $record->save();

                    $mailData = [
                        'user' => $record,
                        'otp_code' => $record->verification_code
                    ];

                    //send otp on email
                    $sendEmail = Helper::sendMail('emails.send_otp', 'JOGO OTP-Code', $mailData, $record);

                    /*if ($sendEmail == 'success')
                    {*/
                    if ($request->path() == 'api/v4/dashboard/auth/profile/update' || $request->path() == 'api/v1/parent-sharing/auth/profile/update') {
                        $record->new_temp_email = $record->email;
                        $record->email = auth()->user()->email;
                    } else if (($request->path() == 'api/v4/dashboard/auth/profile/update/verify-otp' || $request->path() == 'api/v1/parent-sharing/auth/profile/update/verify-otp') && ($request->path() == 'api/v4/dashboard/auth/profile/update/resend-otp' || $request->path() == 'api/v1/parent-sharing/auth/profile/update/resend-otp')) {
                        $record->email = auth()->user()->email;
                        $record->new_temp_email = $request->newTempEmail;
                    }

                    $record->save();

                    DB::commit();

                    $response = Helper::apiSuccessResponse(true, 'We\'ve sent again you a otp code on your email', $this->stdClass);
                    /*}
                    else
                    {
                        $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                    }*/
                }
            } else {
                $response = Helper::apiNotFoundResponse(false, 'Invalid email', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    private function basicUserRelations($user)
    {
        $user = $user->with([
            'nationality' => function ($query)
            {
                $query->select('id', 'name')
                ->withTrashed();
            },
            'user_language' => function ($query)
            {
                $query->select('id', 'name')
                ->withTrashed();
            },
            'country_code' => function ($query)
            {
                $query->select('id', 'phone_code')
                ->withTrashed();
            }
        ]);

        return $user;
    }

    public function signIn($request)
    {
        DB::beginTransaction();

        try {
            $record = $this::select('id', 'nationality_id', 'language', 'first_name', 'last_name', 'email', 'country_code_id', 'phone', 'profile_picture', 'password', 'verification_code', 'status_id')
            ->where('email', $request->email);
            
            if ($request->path() == 'api/v4/dashboard/auth/sign-in')
            {
                $this->basicUserRelations($record);
            }
            $record = ($this->getRecordsByRole($record,$request->path()))->first();

            if ($record) {
                if ($record->status_id != $this->activeStatus->id && empty($record->verification_code)) {
                    $response = Helper::apiErrorResponse(false, 'You account is not active', $this->stdClass);
                } else if ($record->status_id == $this->activeStatus->id && (empty($record->verification_code) || !empty($record->verification_code))) {
                    if (Hash::check($request->password, $record->password)) {
                        //Auth sign in
                        $credentials = [
                            'email' => $request->email,
                            'password' => $request->password
                        ];

                        if (Auth::attempt($credentials)) {
                            //Empty otp code
                            $record->verification_code = NULL;
                            $record->save();

                            //Creating barer token
                            $user = $request->user();

                            $tokenResult = $user->createToken('Personal Access Token');
                            $token = $tokenResult->token;
                            $token->save();

                            //Find userDevice in user_devices table
                            $userDevice = $this->newDeviceToken->where('device_type', $request->deviceType)
                                ->where('mac_id', $request->macId)
                                ->where('ip', $request->ip)
                                ->where('user_id', auth()->user()->id)
                                ->first();

                            if (empty($userDevice)) {
                                //Create entry in user_devices table
                                $userDevice = $this->newDeviceToken;
                            }

                            $userDevice->user_id = $record->id;
                            $userDevice->device_type = $request->deviceType;
                            $userDevice->ip = $request->ip;
                            $userDevice->mac_id = $request->macId;
                            $userDevice->device_token = $request->deviceToken;
                            $userDevice->save();

                            DB::commit();

                            $record->token = $tokenResult->accessToken;

                            if ($request->path() == 'api/v1/parent-sharing/auth/sign-in') {
                                $record = (new ParentsProfileResource($record))->resolve();
                            } else {
                                $record = (new TrainersProfileResource($record))->resolve();
                            }

                            $response = Helper::apiSuccessResponse(true, 'You\'ve sign in successfully', $record);
                        } else {
                            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                        }
                    } else {
                        $response = Helper::apiNotFoundResponse(false, 'Invalid password', $this->stdClass);
                    }
                } else {
                    //Generating otp code
                    $record->verification_code = mt_rand(111111, 999999);
                    $record->save();

                    $mailData = [
                        'user' => $record,
                        'otp_code' => $record->verification_code
                    ];

                    //send otp on email
                    $sendEmail = Helper::sendMail('emails.send_otp', 'JOGO OTP-Code', $mailData, $record);

                    /*if ($sendEmail == 'success')
                    {*/
                    DB::commit();

                    $response = Helper::apiSuccessResponse(true, 'We\'ve sent you a otp code on your email', $this->stdClass);
                    /*}
                    else
                    {
                        $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                    }*/
                }
            } else {
                $response = Helper::apiNotFoundResponse(false, 'Invalid email', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function autoSignIn($request)
    {
        DB::beginTransaction();

        try {
            $record = $this::select('id', 'nationality_id', 'language', 'first_name', 'last_name', 'email', 'country_code_id', 'phone', 'profile_picture', 'password', 'verification_code', 'status_id')
                ->with([
                    'user_devices' => function ($query) {
                        $query->select('id', 'user_id', 'device_type', 'mac_id', 'ip');
                    }
                ])
                ->where('email', $request->email);

            if ($request->path() == 'api/v4/dashboard/auth/auto-sign-in')
            {
                $this->basicUserRelations($record);
            }

            $record = ($this->getRecordsByRole($record,$request->path()))->first();

            if (!$record) {
                $response = Helper::apiNotFoundResponse(false, 'Invalid email', $this->stdClass);
            } else if ($record->status_id != ($this->activeStatus->id ?? NULL)) {
                $response = Helper::apiNotFoundResponse(false, 'Account is inactive', $this->stdClass);
            } else if (!$record->user_devices->where('device_type', $request->deviceType)->where('mac_id', $request->macId)->where('ip', $request->ip)->first()) {
                $response = Helper::apiNotFoundResponse(false, 'Invalid device details', $this->stdClass);
            } else {
                //Auth sign in
                if (Auth::loginUsingId($record->id)) {
                    //Creating barer token
                    $user = $request->user();

                    $tokenResult = $user->createToken('Personal Access Token');
                    $token = $tokenResult->token;
                    $token->save();

                    DB::commit();

                    $record->token = $tokenResult->accessToken;

                    if ($request->path() == 'api/v1/parent-sharing/auth/auto-sign-in') {
                        $record = (new ParentsProfileResource($record))->resolve();
                    } else {
                        $record = (new TrainersProfileResource($record))->resolve();
                    }

                    $response = Helper::apiSuccessResponse(true, 'You\'ve sign in successfully', $record);
                } else {
                    $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                }
            }
        } catch (Exception $ex) {
            DB::rollback();
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function signOut($request)
    {
        DB::beginTransaction();

        try {
            $userDevice = $this->newDeviceToken->where('device_type', $request->deviceType)
                ->where('mac_id', $request->macId)
                ->where('ip', $request->ip)
                ->where('user_id', auth()->user()->id)
                ->first();

            $userDevice->delete();

            $token = auth()->user()->token();
            $token->revoke();

            DB::commit();

            $response = Helper::apiSuccessResponse(true, 'You\'ve sign out successfully', $this->stdClass);
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function forgetPassword($request)
    {
        DB::beginTransaction();

        try {
            $record = $this::select('id', 'first_name', 'last_name', 'email', 'verification_code', 'status_id')
                ->where('email', $request->email);

            $record = ($this->getRecordsByRole($record,$request->path()))->first();

            if (!$record) {
                $response = Helper::apiNotFoundResponse(false, 'Invalid email', $this->stdClass);
            } else if ($record->status_id != ($this->activeStatus->id ?? NULL)) {
                $response = Helper::apiNotFoundResponse(false, 'Account is inactive', $this->stdClass);
            } else {
                $record->verification_code = mt_rand(111111, 999999);
                $record->save();

                $mailData = [
                    'user' => $record,
                    'otp_code' => $record->verification_code
                ];

                    //send otp on email
                $sendEmail = Helper::sendMail('emails.send_otp', 'JOGO OTP-Code', $mailData, $record);
                /*if ($sendEmail == 'success')
                {*/
                DB::commit();

                $response = Helper::apiSuccessResponse(true, 'We\'ve sent you a otp code on your email', $this->stdClass);
                /*}
                else
                {
                    $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                }*/
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function updatePassword($request)
    {
        DB::beginTransaction();


        try {
            $record = $this::select('id', 'first_name', 'last_name', 'email', 'verification_code', 'remember_token', 'updated_at')
                ->where('email', $request->email);

            if ($request->path() == 'api/v4/dashboard/auth/set-password') {
                $record->where('remember_token', $request->token)
                    ->where('verification_code', $request->otp);
            }

            $record = ($this->getRecordsByRole($record,$request->path()))->first();
            $status = 1;
      

            if ($record && $status == 1) {
                if ($request->path() == 'api/v4/dashboard/auth/set-password') {
                    if ($record->remember_token != $request->token) {
                        $status = 2;
                    } else if (date('H:i:s', strtotime($record->updated_at)) < date('H:i:s', strtotime('-15 Minutes'))) {
                        $status = 3;
                    }
                } else if ($record->verification_code != $request->otp) {
                    $status = 4;
                }


                if ($status == 1) {
                    $record->verification_code = NULL;
                    $record->password = Hash::make($request->confirmPassword);

                    if ($request->path() == 'api/v4/dashboard/auth/set-password') {
                        $record->remember_token = NULL;
                        $record->verified_at = date('Y-m-d H:i:s');
                        $record->status_id = $this->activeStatus->id ?? NULL;
                    }

                    $record->save();

                    DB::commit();

                    $response = Helper::apiSuccessResponse(true, 'Password has updated successfully', $this->stdClass);
                } else if ($status == 2) {
                    $response = Helper::apiNotFoundResponse(false, 'Invalid token', $this->stdClass);
                } else if ($status == 3) {
                    $response = Helper::apiErrorResponse(false, 'Your token is expired', $this->stdClass);
                } else {
                    $response = Helper::apiNotFoundResponse(false, 'Invalid otp', $this->stdClass);
                }
            } else {
                $response = Helper::apiNotFoundResponse(false, 'Invalid email', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function viewProfile($request, $id)
    {
        try {
            $record = $this::select('id', 'nationality_id', 'language', 'first_name', 'last_name', 'email', 'country_code_id', 'phone', 'profile_picture', 'updated_at')
                ->with([
                    'user_devices' => function ($query) {
                        $query->select('id', 'user_id', 'device_type', 'mac_id', 'ip');
                    }
                ]);

            if ($request->path() == 'api/v4/dashboard/auth/view-profile') {
                $record->where('remember_token', $id);
            } else {
                $record->where('id', $id);
            }

            if ($request->path() == 'api/v4/dashboard/clubs/trainers/edit/'.$id)
            {
                $record->with([
                    'country_code' => function ($query) {
                        $query->select('id', 'phone_code')
                            ->withTrashed();
                    },
                    'teams_trainers' => function ($query) {
                        $query->select('teams.id', 'team_id', 'team_name', 'trainer_user_id')
                            ->orderBy('teams.created_at', 'desc');
                    }
                ])
                ->whereHas('clubs_trainers', function ($query) use($request)
                {
                    $query->where('club_id', $request->clubId);
                });
            }
            else
            {
                $this->basicUserRelations($record);
            }

            $record = ($this->getRecordsByRole($record,$request->path()))->first();

            if ($record && date('H:i:s', strtotime($record->updated_at)) < date('H:i:s', strtotime('-15 Minutes')) && $request->path() == 'api/v4/dashboard/auth/view-profile') {
                $response = Helper::apiErrorResponse(false, 'Your token is expired', $this->stdClass);
            } else if ($record) {
                $record->token = $request->bearerToken();

                if ($request->path() == 'api/v1/parent-sharing/auth/profile/edit') {
                    $record = (new ParentsProfileResource($record))->resolve();
                } else if ($request->path() == 'api/v4/dashboard/clubs/trainers/edit/' . $id) {
                    $record = (new EditTrainerResource($record))->resolve();
                } else {
                    $record = (new TrainersProfileResource($record))->resolve();
                }

                $response = Helper::apiSuccessResponse(true, 'Success', $record);
            } else {
                if ($request->path() == 'api/v4/dashboard/auth/view-profile') {
                    $response = Helper::apiNotFoundResponse(false, 'Invalid token', $this->stdClass);
                } else {
                    $response = Helper::apiNotFoundResponse(false, 'Invalid trainer id', $this->stdClass);
                }
            }
        } catch (Exception $ex) {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function updateRecord($request, $id)
    {
        DB::beginTransaction();

        try {
            $record = $this::select('id', 'nationality_id', 'language', 'first_name', 'last_name', 'email', 'country_code_id', 'phone', 'profile_picture', 'password', 'verification_code', 'status_id')
                ->where('id', $id);

            if ($request->path() == 'api/v4/dashboard/auth/profile/update')
            {
                $this->basicUserRelations($record);
            }

            $record = ($this->getRecordsByRole($record,$request->path()))->first();

            //$record = $record->first();

            if ($record) {
                $record->first_name = $request->firstName;
                $record->last_name = $request->lastName;

                if ($request->email && auth()->user()->email != $request->email) {
                    $record->new_temp_email = $request->email;
                }

                if ($request->countryCode) {
                    $phoneCode = $this->countries
                        ->where('phone_code', $request->countryCode)
                        ->first();

                    $record->country_code_id = $phoneCode->id;
                }

                if ($request->phoneNo) {
                    $record->phone = $request->phoneNo;
                }

                if ($request->nationalityId) {
                    $record->nationality_id = $request->nationalityId;
                }

                if ($request->languageId) {
                    $record->language = $request->languageId;
                }

                if ($request->hasFile('image')) {
                    $record->profile_picture = Storage::putFile('media/users', $request->image);
                }

                $record->save();

                if ($record instanceof $this) {
                    DB::commit();

                    $record->token = $request->bearerToken();

                    if ($request->path() == 'api/v1/parent-sharing/auth/profile/update') {
                        $record = (new ParentsProfileResource($record))->resolve();
                    } else {
                        $record = (new TrainersProfileResource($record))->resolve();
                    }

                    if ($request->email && auth()->user()->email != $request->email) {
                        $request->newTempEmail = $request->email;
                        $request->email = auth()->user()->email;

                        $this->resendOtp($request);

                        $response = Helper::apiSuccessResponse(true, 'Profile has updated successfully, please verify your new email', $record);
                    } else {
                        $response = Helper::apiSuccessResponse(true, 'Profile has updated successfully', $record);
                    }
                } else {
                    $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                }
            } else {
                $response = Helper::apiNotFoundResponse(false, 'Invalid user', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function changePassword($request, $id)
    {
        DB::beginTransaction();

        try {
            $record = $this::select('id', 'password')
                ->where('id', $id);

            $record = ($this->getRecordsByRole($record,$request->path()))->first();

            if ($record) {
                if (!Hash::check($request->currentPassword, $record->password)) {
                    $response = Helper::apiNotFoundResponse(false, 'Invalid current password', $this->stdClass);
                } else {
                    $record->password = Hash::make($request->confirmPassword);
                    $record->save();

                    if ($record instanceof $this) {
                        DB::commit();

                        $this->signOut($request);

                        $response = Helper::apiSuccessResponse(true, 'Password has updated successfully', $this->stdClass);
                    } else {
                        $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                    }
                }
            } else {
                $response = Helper::apiNotFoundResponse(false, 'Invalid user', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function playersListing($request, $limit = 10, $offset = 0, $sortingColumn = 'created_at', $sortingType = 'asc', $status = 1, $apiType = 'parentsSharingApp', array $generalColumns = ['id', 'first_name', 'last_name', 'profile_picture'], array $playersColumns = ['user_id', 'position_id'], array $positionColumns = ['position_id', 'name'], array $playersTeamsColumns = ['user_id', 'team_id', 'team_name'])
    {
        try {
            $records = $this::select($generalColumns)
                ->with([
                    'player' => function ($query) use ($playersColumns) {
                        $query->select($playersColumns)
                            ->withTrashed();
                    },
                    'player.positions' => function ($query) {
                        $query->select('positions.id', 'name', 'lines');
                    },
                    'player.positions.line' => function ($query) {
                        $query->select('lines.id', 'name');
                    },
                    'teams' => function ($query) use ($playersTeamsColumns) {
                        $query->select($playersTeamsColumns)
                            ->withTrashed();
                    },
                ])
                ->whereHas('player')
                ->whereHas('parent_players', function ($query) {
                    $query->where('parent_id', auth()->user()->id);
                })
                ->orderBy($sortingColumn, $sortingType)
                ->limit($limit)
                ->offset($offset);

            $totalRecords = $records->count();

            $records = $records->get();

            if ($totalRecords > 0) {
                $records = ParentsPlayersListingResource::collection($records)->toArray($request);

                $response = Helper::apiSuccessResponse(true, 'Records found', $records);
            } else {
                $response = Helper::apiNotFoundResponse(false, 'No records found', []);
            }
        } catch (Exception $ex) {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', []);
        }

        return $response;
    }

    public function inviteParent($request)
    {
        DB::beginTransaction();

        try {
            $record = $this::select('id')
                ->where('email', $request->email)
                ->first();

            if ($record) {
                $parent = [
                    'parent_email' => $request->email,
                    'parent_id' => $record->id,
                    'player_id' => $request->playerId
                ];
            } else {
                $record = $this;

                $parent = [
                    'parent_email' => $request->email,
                    'parent_id' => NULL,
                    'player_id' => $request->playerId
                ];
            }

            $record->player_parents()->syncWithoutDetaching([$request->playerId => ['parent_email' => $request->email]]);

            $record->email = $request->email;
            $record->first_name = '';
            $record->last_name = '';

            //send otp on email
            $sendEmail = Helper::sendMail('emails.welcome_players_parents', 'JOGO Invitation', $parent, $record);

            /*if ($sendEmail == 'success')
            {*/
            DB::commit();

            $response = Helper::apiSuccessResponse(true, 'Player has shared with parent successfully', $this->stdClass);
            /*}
            else
            {
                $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
            }*/
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, $ex->getMessage(), $this->stdClass);
        }

        return $response;
    }

    public function playersParentsListing($request, $limit = 10, $offset = 0, $sortingColumn = 'created_at', $sortingType = 'asc', $status = 1, $apiType = 'parentsSharingApp')
    {
        try {
            $records = DB::table('parent_players')
                ->select('id', 'parent_email')
                ->where('player_id', $request->playerId)
                ->groupBy('parent_email');

            $totalRecords = $records->count();

            $records = $records->get();

            if ($totalRecords > 0) {
                $records = PlayersParentsListingResource::collection($records)->toArray($request);

                $response = Helper::apiSuccessResponse(true, 'Records found', $records);
            } else {
                $response = Helper::apiNotFoundResponse(false, 'No records found', []);
            }
        } catch (Exception $ex) {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', []);
        }

        return $response;
    }

    public function removePlayerParents($request)
    {
        DB::beginTransaction();

        try {
            $record = DB::table('parent_players')
                ->select('id')
                ->where('id', $request->id);

            if ($record->first()) {
                if ($record->delete()) {
                    DB::commit();

                    $response = Helper::apiSuccessResponse(true, 'Parent Email removed successfully', $this->stdClass);
                } else {
                    $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                }
            } else {
                $response = Helper::apiNotFoundResponse(false, 'Invalid id', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function setupProfile($request)
    {
        DB::beginTransaction();

        try
        {
            $record = $this::select('id', 'nationality_id', 'language', 'first_name', 'last_name', 'email', 'country_code_id', 'phone', 'profile_picture');

            $this->basicUserRelations($record);
            
            $record = $record->where('id', auth()->user()->id)
            ->whereHas('roles', function ($query)
            {
                $query->where('name', $this->demoTrainerRole->name ?? '-');
            })
            ->first();

            if ($record) {
                $record->first_name = $request->firstName;
                $record->last_name = $request->lastName;

                $phoneCode = $this->countries
                    ->where('phone_code', $request->countryCode)
                    ->first();

                $record->country_code_id = $phoneCode->id;

                $record->phone = $request->phoneNo;

                if ($request->hasFile('image')) {
                    $record->profile_picture = Storage::putFile('media/users', $request->image);
                }

                $record->save();

                if ($record instanceof $this) {
                    DB::commit();

                    $record->token = $request->bearerToken();

                    $record = (new TrainersProfileResource($record))->resolve();

                    $response = Helper::apiSuccessResponse(true, 'Profile has set successfully', $record);
                } else {
                    $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                }
            } else {
                $response = Helper::apiNotFoundResponse(false, 'Invalid user', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function joiningClub($request)
    {
        DB::beginTransaction();

        try {
            $record = $this::select('id')
                ->with([
                    'clubs_trainers' => function ($query) use ($request) {
                        $query->select('club_id', 'title')
                            ->where('clubs.id', $request->clubId);
                    }
                ])
                ->where('id', auth()->user()->id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', $this->trainerRole->name ?? '-')
                        ->orWhere('name', $this->demoTrainerRole->name ?? '-');
                })
                ->first();

            if ($record && $record->clubs_trainers()->count() > 0) {
                $response = Helper::apiNotFoundResponse(false, 'You have joined your club already, please wait for the approval from club', $this->stdClass);
            } else if ($record && $record->clubs_trainers()->count() < 1) {
                $club = $this->clubs
                    ->where('id', $request->id)
                    ->first();

                if (!empty($club->owner)) {
                    $record->clubs_trainers()->syncWithoutDetaching($request->id);

                    $mailData = [
                        'user' => auth()->user()
                    ];

                    $user = $club->owner;
                } else if (count($club->trainers) > 0) {
                    $record->clubs_trainers()->syncWithoutDetaching($request->id);

                    $mailData = [
                        'user' => auth()->user()
                    ];

                    $user = $club->trainers[0];
                } else {
                    $club->owner_id = auth()->user()->id;
                    $club->save();

                    $mailData = [
                        'user' => auth()->user()
                    ];

                    $user = $club->owner;

                    if (in_array('demo_trainer', $record->roles->pluck('name')->toArray())) {
                        $record->removeRole('demo_trainer');

                        $record->assignRole('trainer');
                    }
                }

                //send otp on email
                $sendEmail = Helper::sendMail('emails.remindClubOwner', 'Trainer Joined Your Club', $mailData, $user);

                DB::commit();

                if (count((new Club())->myCLubs($request)->original['Result']) > 0) {
                    $data = [
                        'clubId' => $request->clubId,
                        'nextScreen' => '/'
                    ];
                } else {
                    $data = [
                        'clubId' => $request->clubId,
                        'nextScreen' => '/register/requested'
                    ];
                }

                $response = Helper::apiSuccessResponse(true, 'You\'ve joined this club successfully', $data);
            } else {
                $response = Helper::apiNotFoundResponse(false, 'Invalid user', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function remindClubOwner($request)
    {
        DB::beginTransaction();

        try {
            $record = $this::with([
                'clubs_trainers' => function ($query) {
                    $query->select('club_id', 'owner_id', 'title');
                },
                'clubs_trainers.owner' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'email')
                        ->withTrashed();
                }
            ])
                ->select('id')
                ->where('id', auth()->user()->id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', $this->trainerRole->name ?? '-')
                        ->orWhere('name', $this->demoTrainerRole->name ?? '-');
                })
                ->first();

            if ($record && $record->clubs_trainers()->count() > 0) {

                $club = $this->clubs
                    ->with('trainers', 'owner')
                    ->where('id', $record->clubs_trainers[0]->club_id)
                    ->first();

                if (!empty($club->owner)) {
                    $mailData = [
                        'user' => auth()->user()
                    ];

                    $user = $club->owner;
                } else {
                    $mailData = [
                        'user' => auth()->user()
                    ];

                    $user = $club->trainers[0];
                }

                //send otp on email
                $sendEmail = Helper::sendMail('emails.remindClubOwner', 'Trainer Joined Your Club', $mailData, $user);

                /*if ($sendEmail == 'success')
                {*/
                //Remove demo trainer role
                //$record->removeRole($this->demoTrainerRole->name);

                DB::commit();

                $response = Helper::apiSuccessResponse(true, 'Email has sent successfully to the club owner', $this->stdClass);
                /*}
                else
                {
                    $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                }*/
            } else if ($record && $record->clubs_trainers()->count() < 1) {
                $response = Helper::apiNotFoundResponse(false, 'You have to join your club first', $this->stdClass);
            } else {
                $response = Helper::apiNotFoundResponse(false, 'Invalid user', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function approveTrainerRequest($request)
    {
        DB::beginTransaction();

        try {
            $trainer = $this::with([
                'clubs_trainers' => function ($query) use ($request) {
                    $query->select('club_id', 'trainer_user_id', 'title')
                        ->withPivot('is_request_accepted')
                        ->where('clubs.id', $request->clubId);
                }
            ])
                ->select('id', 'first_name', 'last_name', 'email')
                ->where('id', $request->trainerId)
                ->whereHas('clubs_trainers', function ($query) use ($request) {
                    $query->where('clubs.id', $request->clubId);
                })
                ->first();

            if ($trainer && $trainer->clubs_trainers[0]->pivot->is_request_accepted == 'no') {
                if ($request->action == 'yes') {
                    $trainer->clubs_trainers()->syncWithoutDetaching([$request->clubId => ['is_request_accepted' => 'yes']]);

                    if (!in_array('trainer', $trainer->roles->pluck('name')->toArray())) {
                        //Assign trainer role
                        $trainer->assignRole($this->trainerRole->name);
                    }

                    if (in_array('demo_trainer', $trainer->roles->pluck('name')->toArray())) {
                        //Remove demo trainer role
                        $trainer->removeRole($this->demoTrainerRole->name);
                    }

                    $msg = 'You\'ve approved the joining request successfully of ' . $trainer->first_name . ' ' . $trainer->last_name . ' for your club';
                } else {
                    $trainer->clubs_trainers()->detach($request->clubId);

                    $msg = 'You\'ve declined the joining request successfully of ' . $trainer->first_name . ' ' . $trainer->last_name . ' for your club';
                }

                $mailData = [
                    'club' => $trainer->clubs_trainers[0],
                    'isRequestAccepted' => $request->action
                ];

                //send otp on email
                $sendEmail = Helper::sendMail('emails.notifyTrainerClubJoiningRequest', 'Club Joining Request', $mailData, $trainer);

                /*if ($sendEmail == 'success')
                {*/
                DB::commit();

                $response = Helper::apiSuccessResponse(true, $msg, $this->stdClass);
                /*}
                else
                {
                    $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                }*/
            } else if ($trainer && $trainer->clubs_trainers[0]->pivot->is_request_accepted == 'yes') {
                $response = Helper::apiNotFoundResponse(false, 'Request already proceed', $this->stdClass);
            } else {
                $response = Helper::apiNotFoundResponse(false, 'Invalid user id', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function createTrainers($request, $id = NULL)
    {
        DB::beginTransaction();

        try {
            $status = 1;

            $notCreatedTrainers = [];

            $response = [];

            foreach ($request->firstNames as $index => $firstName) {
                if (!empty($id)) {
                    //find in users table
                    $trainer = $this::with([
                        'country_code' => function ($query) {
                            $query->select('id', 'phone_code')
                                ->withTrashed();
                        },
                        'clubs_trainers' => function ($query) use ($request) {
                            $query->where('clubs.id', $request->clubId);
                        },
                        'teams_trainers' => function ($query) use ($request) {
                            $query->whereHas('clubs', function ($query) use ($request) {
                                $query->where('clubs.id', $request->clubId);
                            });
                        }
                    ])
                        ->where('id', $id)
                        ->whereHas('clubs_trainers', function ($query) use ($request) {
                            $query->where('club_id', $request->clubId);
                        })
                        ->first();

                    if (empty($trainer)) {
                        $status = 0;
                    } else {
                        if ($request->role == 'owner') {
                            $club = Club::where('id', $request->clubId)
                                ->first();

                            $club->updated_by = auth()->user()->id;
                            $club->owner_id = $id;
                            $club->save();

                            $trainer->clubs_trainers()->detach($request->clubId);

                            $trainer->teams_trainers()->detach($club->teams->pluck('team_id')->toArray());

                            auth()->user()->clubs_trainers()->syncWithoutDetaching($request->clubId);

                            auth()->user()->teams_trainers()->syncWithoutDetaching($club->teams->pluck('team_id')->toArray());
                        }
                    }
                } else {
                    //Find from deleted emails in users table
                    $trainer = $this::where('email', $request->emails[$index])
                        ->onlyTrashed()
                        ->first();

                    if (empty($trainer)) {
                        //Create entry in users table
                        $trainer = new $this;
                    } else {
                        $trainer->deleted_at = NULL;
                    }
                }

                $teamsId = [];

                $notCreatedTeams = [];

                $processCheckLimit = true;

                foreach ($request->assignedTeams['trainer_' . ($index + 1)] as $teamIndex => $teamId) {
                    $teamSubscripion = TeamSubscription::where('team_id', $teamId)
                        ->where('status', '1')
                        ->first();

                    if ($teamSubscripion && !empty($teamSubscripion->coupon_id)) {
                        $coupon = Coupon::where('id', $teamSubscripion->coupon_id)
                            ->first();

                        if ($coupon && $coupon->code == env('SECRET_COUPON')) {
                            $processCheckLimit = false;
                        }
                    }

                    if ($processCheckLimit) {
                        $checkLimit = PricingPlan::checkLimit($teamId, $trainer, 'trainer');
                    } else {
                        $checkLimit = true;
                    }

                    if (gettype($checkLimit) == 'object') {
                        $status = 2;

                        $teamName = $this->teamModel->getTeamName($teamId);

                        if ($teamName) {
                            $notCreatedTeams[] = $teamName;
                        }
                    } else if (gettype($checkLimit) == 'array') {
                        $status = 3;

                        $teamName = $this->teamModel->getTeamName($teamId);

                        if ($teamName) {
                            $notCreatedTeams[] = $teamName;
                        }
                    } else {
                        $status = 1;

                        $teamsId[] = $teamId;
                    }
                }

                if ($status == 1) {
                    $phoneCode = $this->countries
                        ->where('phone_code', $request->countryCodes[$index])
                        ->first();

                    $trainer->first_name = $firstName;
                    $trainer->last_name = $request->lastNames[$index];
                    $trainer->email = $request->emails[$index];
                    $trainer->country_code_id = $phoneCode->id;
                    $trainer->phone = $request->phoneNos[$index];
                    $trainer->verification_code = mt_rand(111111, 999999);
                    $trainer->remember_token = bin2hex(openssl_random_pseudo_bytes(16));
                    $trainer->language = $this->englishLanguage->id ?? NULL;

                    if (empty($id)) {
                        $trainer->status_id = $this->pendingStatus->id ?? NULL;
                    }

                    $trainer->save();

                    //Syncing club with this trainer
                    $trainer->clubs_trainers()->syncWithoutDetaching([$request->clubId => ['is_request_accepted' => 'yes']]);

                    if (count($teamsId) > 0) {
                        //Syncing teams with this trainer
                        $trainer->teams_trainers()->syncWithoutDetaching($teamsId);
                    }

                    //Assign trainer role
                    $trainer->assignRole($this->trainerRole->name);

                    $trainer->assignRole('freemium');

                    if (empty($id)) {
                        $mailData = [
                            'trainer' => $trainer
                        ];

                        //send otp on email
                        $sendEmail = Helper::sendMail('emails.setTrainerPassword', 'Set Password for Jogo', $mailData, $trainer);
                    }

                    DB::commit();
                }

                if ($status == 2 || $status == 3) {
                    $notCreatedTrainers[] = [
                        'firstname' => $firstName,
                        'lastName' => $request->lastNames[$index],
                        'email' => $request->emails[$index],
                        'teams' => implode(', ', $notCreatedTeams)
                    ];

                    $unProccessTeam = implode(', ', $notCreatedTeams);
                }

                if ($status == 0) {
                    $response = Helper::apiNotFoundResponse(false, 'Invalid trainer id', $this->stdClass);

                    break;
                } else if ($status == 1 && !empty($id)) {
                    $response = Helper::apiSuccessResponse(true, 'You\'ve updated trainer successfully', $this->stdClass);

                    break;
                } else if ($status == 2 && !empty($id)) {
                    $response = Helper::apiErrorResponse(false, 'No pricing plan found for ' . $unProccessTeam, $this->stdClass);
                } else if ($status == 3 && !empty($id)) {
                    $response = Helper::apiErrorResponse(false, 'Please upgrade your plan for ' . $unProccessTeam, $this->stdClass);
                } else if ($status == 2 && empty($id)) {
                    $response[] = Helper::apiErrorResponse(false, 'No pricing plan found for ' . $unProccessTeam, $this->stdClass);
                } else if ($status == 3 && empty($id)) {
                    $response[] = Helper::apiErrorResponse(false, 'Please upgrade your plan for ' . $unProccessTeam, $this->stdClass);
                } else {
                    $response[] = Helper::apiSuccessResponse(true, 'Trainer ' . $trainer->first_name . ' ' . $trainer->last_name . ' has been created successfully', $this->stdClass);
                }
            }
        } catch (Exception $ex) {
            DB::rollback();

            if (!empty($id)) {
                $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
            } else {
                $response[] = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
            }
        }

        if (is_array($response)) {
            $response = array_column($response, 'original');
        }

        return $response;
    }

    public function remove($request, $id)
    {
        DB::beginTransaction();

        try {
            $user = $this::select('id');

            if ($request->path() == 'api/v1/app/profile/delete/' . $id) {
                $user->with([
                    'clubs_players' => function ($query) {
                        $query->select('clubs.id', 'player_user_id');
                    },
                    'teams' => function ($query) {
                        $query->select('teams.id', 'user_id');
                    }
                ]);
            } else {
                $user->with([
                    'clubs_trainers' => function ($query) use ($request) {
                        $query->select('club_id', 'trainer_user_id')
                            ->withPivot('is_request_accepted')
                            ->where('club_id', $request->clubId);
                    },
                    'teams_trainers' => function ($query) {
                        $query->select('team_id', 'trainer_user_id');
                    }
                ]);
            }

            $user->where('id', $id);

            if ($request->path() != 'api/v1/app/profile/delete/' . $id) {
                $user->whereHas('clubs_trainers', function ($query) use ($request) {
                    $query->where('club_id', $request->clubId);
                });
            }

            $user = $user->first();

            if (!$user) {
                if ($request->path() == 'api/v1/app/profile/delete/' . $id) {
                    $response = Helper::apiNotFoundResponse(false, 'Invalid player id', $this->stdClass);
                } else {
                    $response = Helper::apiNotFoundResponse(false, 'Invalid trainer id', $this->stdClass);
                }
            } else {
                if ($request->path() == 'api/v1/app/profile/delete/' . $id) {
                    $user->clubs_players()->detach($user->clubs_players()->pluck('clubs.id')->toArray());

                    $user->teams()->detach($user->teams_trainers()->pluck('teams.id')->toArray());

                    $user->delete();
                } else {
                    $user->clubs_trainers()->detach($user->clubs_trainers()->pluck('club_id')->toArray());

                    $user->teams_trainers()->detach($user->teams_trainers()->pluck('team_id')->toArray());

                    if (count($user->clubs_trainers) < 1) {
                        $jogoClub = Club::select('id')
                            ->with([
                                'teams' => function ($query) {
                                    $query->select('club_id', 'team_id', 'teams.id', 'team_name')
                                        ->where('team_name', 'like', '%jogo%');
                                }
                            ])
                            ->where('title', 'like', '%jogo%')
                            ->first();

                        if ($jogoClub) {
                            $jogoClub->trainers()->syncWithoutDetaching([
                                $id => [
                                    'is_request_accepted' => 'yes'
                                ]
                            ]);
                        }
                    }

                    if (count($user->teams_trainers) < 1) {
                        if (isset($jogoClub) && count($jogoClub->teams) > 0) {
                            $jogoClub->teams[0]->trainers()->syncWithoutDetaching([$user->id]);
                        }
                    }
                }

                DB::commit();

                if ($request->path() == 'api/v1/app/profile/delete/' . $id) {
                    $response = Helper::apiSuccessResponse(true, 'You\'ve successfully deleted your account', new stdClass());
                } else {
                    $response = Helper::apiSuccessResponse(true, 'You\'ve successfully deleted your trainer', new stdClass());
                }
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function resendSetupPasswordLink($request)
    {
        DB::beginTransaction();

        try {
            //find in users table
            $trainer = $this::where('remember_token', $request->token)
                ->where('status_id', $this->pendingStatus->id ?? NULL)
                ->first();

            if (empty($trainer)) {
                $status = 0;
            } else if ($trainer->verification_code != $request->otp) {
                $status = 2;
            } else {
                $trainer->verification_code = mt_rand(111111, 999999);
                $trainer->remember_token = bin2hex(openssl_random_pseudo_bytes(16));
                $trainer->save();

                DB::commit();

                $mailData = [
                    'trainer' => $trainer
                ];

                //send otp on email
                $sendEmail = Helper::sendMail('emails.setTrainerPassword', 'Set Password for Jogo', $mailData, $trainer);

                $status = 1;
            }

            if ($status == 1) {
                $response = Helper::apiSuccessResponse(true, 'We\'ve mailed you a new setup password link', $this->stdClass);
            } else if ($status == 2) {
                $response = Helper::apiNotFoundResponse(true, 'Invalid otp', $this->stdClass);
            } else {
                $response = Helper::apiNotFoundResponse(true, 'Invalid user token', $this->stdClass);
            }
        } catch (Exception $ex) {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function playerExerciseJSONFileContent($request, $relations)
    {
        try {
            $player = $this::select('id')
                ->with($relations)
                ->whereHas('exercises', function ($query) use ($request) {
                    $query->where('player_exercise.id', $request->playerExerciseId)
                        ->where('user_id', $request->playerId)
                        ->where('exercise_id', $request->exerciseId);
                })
                ->first();

            if ($player && $player->exercises && Storage::exists($player->exercises[0]->ai_json)) {
                $fileContent = Storage::get($player->exercises[0]->ai_json);

                $response = Helper::apiSuccessResponse(true, 'File found', $fileContent);
            } else {
                $response = Helper::apiNotFoundResponse(true, 'File not found', $this->stdClass);
            }
        } catch (Exception $ex) {
            $response = Helper::apiErrorResponse(false, $ex->getMessage(), $this->stdClass);
        }

        return $response;
    }

    public function send_code($request,$role){
        $user = User::where('email', $request->email)->whereHas('roles', function ($q) use ($role) {
            $q->where('roles.name', $role);
        })->first();

        if (!$user) {
            return ['status' => false,'msg' => 'User not found'];
        }

        $otp_code = Helper::generateOtp();

        $dt = [];
        if ($request->type == 'reset_pwd') {
            $dt['subject'] = 'JOGO - Reset Password Code';
            $dt['res_message'] = 'Reset code has been sent to your email';
        } else {
            $dt['subject'] = 'JOGO - Verification Code';
            $dt['res_message'] = 'Verification code has been sent to your email';
        }

        try {
            Mail::send('emails.send_code', ['user' => $user, 'otp_code' => $otp_code], function ($m) use ($user, $dt) {
                $m->to($user->email, $user->first_name)->subject($dt['subject']);
            });
        } catch (Exception $e) {
            activity()->causedBy($user)->performedOn($user)->log($e->getMessage());
        }

        $user->verification_code = $otp_code;
        $user->save();

        return ['status' => true, 'msg' => $dt['res_message']];
    }

    public function getGraph($graph_type,$request){
        $stats['heart_rate'] = ['HR_AVG'];
        $stats['speed'] = ['SPEED_WALKING', 'SPEED_SPRINTING', 'SPEED_RUNNING'];
        $stats['avg_speed'] = ['SPEED_AVG'];

        $user = User::find($request->player_id);
        $team_id = $user->teams[0]->id ?? 0;
        $duration = $request->duration;

        if ($request->from != null) {
            $from = isset($request->from) ? $request->from : '1970-01-01';
            $to = isset($request->to) ? $request->to : Carbon::today()->addDay();
            $duration = ['from' => $from,'to' => $to];
        }

        $stat_type = MatchStatType::whereIn('name', $stats[$graph_type])
            ->pluck('id');

        if ($request->filter && isset($request->filter['player2'])) {
            $response = MatchStat::getGraphData1($request->player_id, $request->filter['player2'], $stat_type, $duration, $request->filter, 'linear', 'player');
        } else {
            $response = MatchStat::getGraphData1($request->player_id, $team_id, $stat_type, $duration, $request->filter, 'linear', 'team');
        }

        return $response;
    }

    public function verifyUser($request,$role){
        $user = User::where('email', $request->email)->where('verification_code', $request->verification_code)
            ->whereHas('roles', function ($q) use ($role) {
                $q->where('roles.name', $role);
            })
            ->first();

        if (!$user) {
            return ['status' => false, 'msg' => 'Invalid verification code'];
        }

        $user->verified_at = now();
        $user->verification_code = null;
        $user->save();

        return ['status' => true, 'user' => $user];
    }

    private function getRecordsByRole($record,$path){
        $parentRoleNamePaths = [
            "api/v1/parent-sharing/auth/verify-otp",
            "api/v1/parent-sharing/auth/resend-otp",
            "api/v1/parent-sharing/auth/sign-in",
            "api/v1/parent-sharing/auth/auto-sign-in",
            "api/v1/parent-sharing/auth/forget-password",
            "api/v1/parent-sharing/auth/profile/edit",
            "api/v1/parent-sharing/auth/profile/update",
            "api/v1/parent-sharing/auth/profile/update/password",
        ];

        $demoRoleNamePaths = [
            "api/v4/dashboard/auth/profile/edit-profile"
        ];

        $record->whereHas('roles', function ($query) use ($path,$parentRoleNamePaths,$demoRoleNamePaths) {
            if (in_array($path,$parentRoleNamePaths)) {
                $query->where('name', $this->parentRole->name ?? '-');
            }
            else if (in_array($path,$demoRoleNamePaths))
                {
                    $query->where('name', $this->demoTrainerRole->name ?? '-');
                }
            else
                {
                $query->where('name', $this->trainerRole->name ?? '-')
                    ->orWhere('name', $this->demoTrainerRole->name ?? '-');
                }
        });

        return $record;
    }

    public function getUser(){
        $user = User::with(['nationality:id,name', 'roles:id,name', 'player_details' => function ($q) {
            $q->select('id', 'user_id', 'height', 'weight', 'jersey_number', 'position_id', 'customary_foot_id')
                ->with(['customaryFoot:id,name']);
        }]);

        return $user;
    }

    public function withPlayerDetail($user){
        $user = $user->with([
            'player_details.positions' => function ($query) {
                $query->select('positions.id', 'name', 'lines');
            },
            'player_details.positions.line' => function ($query) {
                $query->select('lines.id', 'name');
            }
        ]);

        return $user;
    }

    public function withTeam($user){
        $user = $user->with(['teams' => function ($team) {
            $team->select('teams.id', 'teams.team_name', 'teams.image', 'teams.description')
                ->with(['trainers' => function ($abc) {
                    $abc->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.surname', 'users.profile_picture')->orderBy('team_trainers.created_at', 'ASC')->limit(1);
                }]);
        }]);

        return $user;
    }
}