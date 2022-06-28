<?php

namespace App\Http\Controllers\Api;

use App\AccessModifier;
use App\Contact;
use App\Country;
use App\CustomaryFoot;
use App\Event;
use App\Exercise;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\App\Events\ByDateResource as EventsByDateListing;
use App\Http\Resources\Api\App\GetTeamPlayerResourceListing;
use App\Http\Resources\Api\App\PlayerFollowerFollowingResource;
use App\Http\Resources\Api\TrainerApp\Events\TrainerAppByDateResource;
use App\Http\Resources\Api\App\PlayerSearchListingResource;
use App\Match;
use App\MatchDetails;
use App\MatchStat;
use App\MatchStatType;
use App\PlayerRecommendedEexercise;
use App\PlayerScore;
use App\PlayerTeam;
use App\PlayerTeamRequest;
use App\Position;
use App\Post;
use App\Review;
use App\Status;
use App\Team;
use App\Club;
use App\TeamTrainer;
use App\Trainer;
use App\TrainingSession;
use App\User;
use App\UserNotification;
use App\UserPrivacySetting;
use function Aws\map;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use stdClass;


/**
 * @authenticated
 * @group Player Apis
 * APIs For Player
 * User Auth Token is required in headers
 */
class PlayerAuthController extends Controller
{

    private $eventModel, $eventColumns, $sortingColumn, $sortingType, $status, $months, $years, $limit, $offset;

    public function __construct()
    {
        $this->eventModel = new Event();

        $this->years = [date('Y')];

        $this->limit = 3;

        $this->offset = 0;

        $this->months = [
            "01",
            "02",
            "03",
            "04",
            "05",
            "06",
            "07",
            "08",
            "09",
            "10",
            "11",
            "12"
        ];

        $this->eventColumns = [
            'id',
            'created_by',
            'category_id',
            'event_id',
            'created_type',
            'group_id',
            'title',
            'from_date_time',
            'to_date_time',
            'valid_till',
            'repetition',
            'location',
            'latitude',
            'longitude',
            'team_id',
            'details',
            'event_type',
            'assignment_id',
            'opponent_team_id',
            'playing_area',
            'action_type',
            'deleted_dates',
            'status',
            'created_at'
        ];

        $this->sortingColumn = 'created_at';

        $this->sortingType = 'desc';

        $this->status = ['active'];
    }
    /**
     * Get Trainer Profile
     *
     * @queryParam  trainer_id required
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": {
     *  "id": 1,
     *  "first_name": "Khurram",
     *  "last_name": "Munir",
     *  "profile_picture": "media/abc",
     *  "teams_trainers": [
     * {
     * "id": 1,
     * "team_name": "abc",
     * "image": "abc"
     * }
     * ]
     * }
     * }
     */
    public function get_trainer_profile(Request $request)
    {
        $trainer = User::role('trainer')->with(['teams_trainers'])->select('id', 'first_name', 'last_name', 'profile_picture')->find($request->trainer_id);

        if ($trainer) {
            return Helper::apiSuccessResponse(true, 'Success', $trainer);
        }

        return Helper::apiErrorResponse(false, 'No records found', new stdClass());
    }

//    private function date_compare($event1, $event2)
//    {
//        $datetime1 = strtotime($event1['start']);
//        $datetime2 = strtotime($event2['start']);
//        return $datetime1 - $datetime2;
//    }


    /**
     * GetPlayerProfile
     *
     * You can get user profilfe details
     *
     * @queryParam  id required user id is required to get the player profile
     *
     * @response 200
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Successfully getting player data",
     * "Result": {
     * "player_data": {
     * "id": 540,
     * "nationality_id": null,
     * "first_name": "test",
     * "middle_name": "''",
     * "last_name": "player",
     * "profile_picture": null,
     * "total_match_time": "03:55:34",
     * "nationality": null,
     * "player": {
     * "id": 1,
     * "user_id": 1,
     * "position_id": 1,
     * "customary_foot_id": 1,
     * "height": 5.8,
     * "weight": 64,
     * "jersey_number": "1",
     * "created_at": null,
     * "updated_at": null,
     * "deleted_at": null,
     * "positions": [
     * {
     * "id": 3,
     * "name": "Goal Keeper",
     * "lines": 2,
     * "pivot": {
     * "player_id": 1,
     * "position_id": 3
     * },
     * "line": {
     * "id": 2,
     * "name": "GoalKeepers"
     * }
     * }
     * ]
     * },
     * "teams": [
     * {
     * "id": 16,
     * "team_name": "teamname",
     * "image": "",
     * "description": null,
     * "pivot": {
     * "user_id": 540,
     * "team_id": 16,
     * "created_at": "2021-06-11 15:45:16"
     * }
     * }
     * ],
     * "user_sensors": []
     * },
     * "player_country": null,
     * "player_customary_foot": null,
     * "player_statistics": [
     * {
     * "title": "Tempo",
     * "icon": "media/users/tempo.png",
     * "points": 0,
     * "url": ""
     * },
     * {
     * "title": "Shot",
     * "icon": "media/users/shot.png",
     * "points": 0,
     * "url": ""
     * },
     * {
     * "title": "Leg %",
     * "icon": "media/users/leg.png",
     * "points": 0,
     * "url": ""
     * }
     * ],
     * "matches": [],
     * "player_achievements": [],
     * "recent_events": [
     * {
     * "id": 2245,
     * "category": {
     * "id": 1,
     * "title": "training"
     * },
     * "title": "First Training",
     * "start": "2021-06-15 21:00:00",
     * "end": "2021-06-20 23:00:00",
     * "repetition": "no",
     * "isAttending": null,
     * "team": {
     * "id": 16,
     * "name": "teamname",
     * "image": ""
     * }
     * },
     * {
     * "id": 2246,
     * "category": {
     * "id": 1,
     * "title": "training"
     * },
     * "title": "Second Training",
     * "start": "2021-06-16 21:00:00",
     * "end": "2021-06-20 23:00:00",
     * "repetition": "no",
     * "isAttending": null,
     * "team": {
     * "id": 16,
     * "name": "teamname",
     * "image": ""
     * }
     * },
     * {
     * "id": 2248,
     * "category": {
     * "id": 1,
     * "title": "training"
     * },
     * "title": "Third Training",
     * "start": "2021-06-22 21:00:00",
     * "end": "2021-06-23 23:00:00",
     * "repetition": "weekly",
     * "isAttending": null,
     * "team": {
     * "id": 16,
     * "name": "teamname",
     * "image": ""
     * }
     * }
     * ],
     * "player_exercises": [],
     * "player_exercises_count": 0,
     * "total_followers": 0,
     * "total_followings": 0,
     * "total_follow_requests": 0,
     * "posts": [],
     * "follow_status": false
     * }
     * }
     *
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "id": [
     *                "The selected id is invalid."
     *            ]
     *        }
     *    ]
     * }
     *
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "id": [
     *                "The id field is required."
     *            ]
     *        }
     *    ]
     * }
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "User has not been found with this id 100",
     *    "Result": []
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "You are not allowed to look into private profile",
     *     "Result": {}
     * }
     *
     * @response 401{
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Player has not been found with this id 12",
     *     "Result": {}
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "You are not listed as a follower, that's why not allowed to look into the profile",
     *     "Result": {}
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     */

    public function get_player_profile(Request $request)
    {
        Validator::make($request->all(), User::$get_player_profile_rules)->validate();

        $user_id = $request->id;
        $check_player = User::role('player')->find($user_id);
        if (!$check_player) {
            $message = "Player has not been found with this id " . $user_id;
            return Helper::apiNotFoundResponse(false, $message, new stdClass());
        }

        $auth_user_id = Auth::user()->id;

        $response = $this->checkTrainerOrPlayerRole($user_id);
        if (!$response['status']) {
            return Helper::apiNotFoundResponse(false, $response['msg'], new stdClass());
        }

        $user_privacy_settings = User::with('user_privacy_settings')->find($user_id)->user_privacy_settings;
        //$user_privacy_settings = User::with('user_privacy_settings')->find($user_id);
        $user_privacy_settings_count = count($user_privacy_settings);

        //$player_data = User::with('player', 'teams')->find($user_id);
        $player_data = User::Select('id', 'nationality_id', 'first_name', 'middle_name', 'last_name', 'profile_picture')
            ->with([
                'nationality' => function ($query) {
                    $query->select('id', 'name', 'phone_code', 'iso as flag');
                },
                'player',
                'player.positions' => function ($query) {
                    $query->select('positions.id', 'name', 'lines');
                },
                'player.positions.line' => function ($query) {
                    $query->select('lines.id', 'name');
                },
                'teams' => function ($q) {
                    $q->select('teams.id', 'teams.team_name', 'teams.image', 'teams.description');
                    $q->latest('pivot_created_at');
                },
                'user_sensors:user_id,imei'
            ])
            ->find($user_id);

        $player_data->total_match_time = Carbon::parse(strtotime(Match::where('user_id', $user_id)
                ->sum('total_ts')))->format('h:i:s') ?? '00:00:00';

        $get_player_profile['player_data'] = $player_data;


        /**
         * Getting player country
         */
        $nationality_id = $player_data['nationality_id'];
        $player_country = Country::Select('name')->find($nationality_id);
        $get_player_profile['player_country'] = $player_country;

        /**
         *  Getting player customary and position
         */

        $user_player_data = User::Select('id', 'nationality_id', 'first_name', 'middle_name', 'last_name', 'profile_picture')->with('player')->find($user_id)->player;
        if (!$user_player_data) {
            $message = "User has not been found with this id " . $user_id;
            return Helper::apiNotFoundResponse(false, $message, new stdClass());
        }
        $player_customary_foot_id = $user_player_data['customary_foot_id'];
        $player_customary_foot = CustomaryFoot::Select('id', 'name')->find($player_customary_foot_id);
        $get_player_profile['player_customary_foot'] = $player_customary_foot;


        /*$player_position_id = $user_player_data['position_id'];
        $player_position = Position::Select('id', 'name')->find($player_position_id);
        $get_player_profile['player_position'] = $player_position;*/


        //get player statistics

        $l_percentage = 0;
        $leg_percentage = MatchDetails::selectRaw('COUNT(CASE WHEN foot=\'R\' THEN 1 END) AS right_foot,COUNT(CASE WHEN foot=\'L\' THEN 1 END) AS left_foot')->where('user_id', $user_id)->first();
        if ($leg_percentage->left_foot || $leg_percentage->right_foot) {
            //get higher one
            $leg_percentage_left = ($leg_percentage->left_foot / ($leg_percentage->left_foot + $leg_percentage->right_foot)) * 100;
            $leg_percentage_right = ($leg_percentage->right_foot / ($leg_percentage->left_foot + $leg_percentage->right_foot)) * 100;
            $l_percentage = $leg_percentage_left > $leg_percentage_right ? $leg_percentage_left : $leg_percentage_right;
        }
        $statistics = [
            'tempo' => MatchStat::selectRaw('MAX(stat_value) as tempo')->join('matches_stats_types', 'matches_stats.stat_type_id', 'matches_stats_types.id')->where('player_id', $user_id)->first()->tempo ?? 0,
            'shot' => MatchDetails::selectRaw('MAX(event_magnitude) as shot')->where('user_id', $user_id)->where('event_type', 'BK')->first()->shot ?? 0,
            'skill' => 0,
            'leg_percentage' => round($l_percentage, 2) ?? 0
        ];
        $stats = [];
        $stats[] = ['title' => 'Tempo', 'icon' => 'media/users/tempo.png', 'points' => $statistics['tempo'], 'url' => ($statistics['tempo']) ? 'tempo-graph' : ''];
        $stats[] = ['title' => 'Shot', 'icon' => 'media/users/shot.png', 'points' => $statistics['shot'], 'url' => ($statistics['shot']) ? 'shots-graph' : ''];
//                $stats[] = ['title' => 'Skill', 'icon' => 'media/users/skill.png', 'points' => $statistics['skill']];
        $stats[] = ['title' => 'Leg %', 'icon' => 'media/users/leg.png', 'points' => $statistics['leg_percentage'], 'url' => ($statistics['leg_percentage']) ? 'leg-distribution-graph' : ''];
        $get_player_profile['player_statistics'] = $stats;

        $matches = Match::where('user_id', $user_id)->orderBy('id', 'desc')->select('id', 'name', 'init_ts AS date')->get();
        if (count($matches) > 0) {
            $get_player_profile['matches'] = $matches;
        } else {
            $get_player_profile['matches'] = [];
        }
        // customary_foot_id position_id


        /**
         * Get Player Acheivements
         */
        $player_achievements = User::with('achievements:achievements.id,achievements.name,achievements.image')->find($user_id);
        $get_player_profile['player_achievements'] = $player_achievements->achievements;


        /**
         * Get Player Recent Events
         */

        $request->months = $this->months;
        $request->years = $this->years;
        $request->limit = $this->limit;
        $request->offset = $this->offset;

        $events = $this->eventModel->index($request, $this->eventColumns, $this->sortingColumn, $this->sortingType, $this->status);

        $events = $events->original["Result"]["events"] ?? [];

        $filterEvents = [];

        foreach ($events as $key => $event){
            $filterEvents[] = $event;

            if ($key == 2)
            {
                break;
            }
        }
        $get_player_profile["recent_events"] = $filterEvents;

        /**
         * get player exercises (count + videos)
         */

        /*$player_exercises = User::with('exercises:exercises.id,exercises.title,exercises.image')->find($user_id);
        $get_player_profile['player_exercises'] = $player_exercises->exercises;
        $get_player_profile['player_exercises_count'] = count($player_exercises->exercises);*/


        if ($auth_user_id == $user_id) {
            $player_exercises = User::selectRaw('users.id,p.id,exercises.title,p.thumbnail,p.status_id')
                ->join('posts as p', 'p.author_id', '=', 'users.id')
                ->join('exercises', 'exercises.id', '=', 'p.exercise_id')
                ->where('users.id', $user_id)
                /*->where('exercises.video_file', '!=', NULL)
                ->where('exercises.completion_time', '>', 0)
                ->whereHas('exercises.player_scores_users')*/
                ->orderBy('p.id', 'desc')
                ->whereNull('p.deleted_at')
                ->get();
        } else {
            $player_exercises = User::selectRaw('users.id,posts.id,exercises.title,posts.thumbnail,posts.status_id')
                ->join('posts', 'posts.author_id', '=', 'users.id')
                ->join('exercises', 'exercises.id', '=', 'posts.exercise_id')
                ->where('users.id', $user_id)
                /*->where('exercises.video_file', '!=', NULL)
                ->where('exercises.completion_time', '>', 0)
                ->whereHas('exercises.player_scores_users')*/
                ->orderBy('posts.id', 'desc')
                ->where('posts.status_id', 7)
                ->whereNull('posts.deleted_at')
                ->get();
        }

        $get_player_profile['player_exercises'] = $player_exercises;
        $get_player_profile['player_exercises_count'] = count($player_exercises);

        $total_followers = User::with('followers')->find($user_id);
        $get_player_profile['total_followers'] = count($total_followers->followers);

        $total_followings = User::with('followings')->find($user_id);
        $get_player_profile['total_followings'] = count($total_followings->followings);

        $total_follow_requests = User::join('contacts', 'users.id', 'contacts.user_id')
            ->where('contacts.status_id', 2)
            ->where('contacts.contact_user_id', auth()->user()->id)
            ->count();
        $get_player_profile['total_follow_requests'] = $total_follow_requests;
        /**
         * Checking current player is following the player
         */

        $get_player_profile['posts'] = [];


//                $followings_ids = Contact::where('user_id', Auth::user()->id)->pluck('contact_user_id')->toArray();
        $access_modifier = AccessModifier::whereIn('name', ['follower', 'public'])->pluck('id')->toArray();
        $status = Helper::getStatus('shared');
        $posts = Helper::postQuery();
        $posts = $posts->where('author_id', '=', $user_id);
        if ($user_id != auth()->user()->id) {
            $posts = $posts->where('status_id', '=', 7);
        }
        $posts = $posts->latest();
        $user_contact = Contact::where('user_id', $auth_user_id)->where('contact_user_id', $user_id)->first();
        if ($user_id == Auth::user()->id) {
        } elseif ($user_contact) {
            $posts = $posts->where('status_id', $status->id);
            $posts = $posts->whereHas('author', function ($q) use ($access_modifier) {
                $q->whereHas('user_privacy_settings', function ($q2) use ($access_modifier) {
                    $q2->whereIn('access_modifiers.id', $access_modifier);
                });
            });
        } else {
            $pub_access_modifier = AccessModifier::whereIn('name', ['public'])->pluck('id')->toArray();
            $posts = $posts->whereHas('author', function ($q) use ($pub_access_modifier) {
                $q->whereHas('user_privacy_settings', function ($q2) use ($pub_access_modifier) {
                    $q2->whereIn('access_modifiers.id', $pub_access_modifier);
                });
            });
        }

        $posts = $posts->get();

        if (count($posts) > 0) {

            $posts = $posts->map(function ($ex) {
                return Helper::getPostObject($ex);
            });
            $new_posts = $posts->sortByDesc(function ($element) {
                return $element->created_at;
            });
            $new_posts = collect($new_posts);
            $get_player_profile['posts'] = $new_posts->values()->all();
        }

        $check_current_player_follower = Contact::where('user_id', $auth_user_id)->where('contact_user_id', $user_id)->first(); //pluck('contact_user_id')->toArray();
        if ($check_current_player_follower) {
            $get_player_profile['follow_status'] = true;
        } else {
            $get_player_profile['follow_status'] = false;
        }

        if ($user_privacy_settings_count > 100000000) {
//                if ($user_privacy_settings_count > 0) {
            /**
             * Getting public,private,follower
             */
            $user_settings_get_player_profile = $user_privacy_settings[0]['name'];

            /* $user_privacy_settings_position = $this->multi_array_search($user_privacy_settings, array('name' => 'GetPlayerProfile'));
             if (count($user_privacy_settings_position) == 0) {
                 return Helper::apiSuccessResponse(true, 'Successfully getting player data', $get_player_profile);
             } else {
                 $user_privacy_settings_position = $user_privacy_settings_position[0];
             }*/

            /**
             * Checking user's Api access modifier (public,private,follower)
             */


            if (strtolower($user_settings_get_player_profile) == 'private') {
                if ($user_id == $auth_user_id) {
                    return Helper::apiSuccessResponse(true, 'Successfully getting player data', $get_player_profile);
                } else {
                    $message = "You are not allowed to look into private profile";
                    return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
                }
            } else if (strtolower($user_settings_get_player_profile) == 'follower') {

                $user_contact = Contact::where('user_id', $user_id)->where('contact_user_id', $auth_user_id)->first();

                if (!$user_contact) {

                    if ($user_id == $auth_user_id) {
                        return Helper::apiSuccessResponse(true, 'Successfully getting player data', $get_player_profile);
                    } else {
                        $message = "You are not listed as a follower, that's why not allowed to look into the profile";
                        return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
                    }
                } else {
                    return Helper::apiSuccessResponse(true, 'Successfully getting player data', $get_player_profile);
                }

            } else {
                return Helper::apiSuccessResponse(true, 'Successfully getting player data', $get_player_profile);
            }

        } else {

            return Helper::apiSuccessResponse(true, 'Successfully getting player data', $get_player_profile);
        }
    }


    public function multi_array_search($array, $search)
    {

        // Create the result array
        $result = array();

        // Iterate over each array element
        foreach ($array as $key => $value) {

            // Iterate over each search condition
            foreach ($search as $k => $v) {

                // If the array element does not meet the search condition then continue to the next element
                if (!isset($value[$k]) || $value[$k] != $v) {
                    continue 2;
                }

            }

            // Add the array element's key to the result array
            $result[] = $key;

        }

        // Return the result array
        return $result;

    }

    /**
     * GetPlayerSkills
     *
     * Player skills containing User Scores for each skill
     *
     * @queryParam  id required user id is required
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Successfully getting player skills points",
     *     "Result": {
     *         "get_player_skills": [
     *             {
     *                 "user_id": 1,
     *                 "skill_id": 1,
     *                 "name": "Agility ",
     *                 "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     *                 "points": "300"
     *             },
     *             {
     *                 "user_id": 1,
     *                 "skill_id": 2,
     *                 "name": "Ball Control",
     *                 "image": "https://camo.githubusercontent.com/8711e1e5b796488ab56ea297dfdc946ae709d029/68747470733a2f2f692e696d6775722e636f6d2f6c4a567a3249492e706e67",
     *                 "points": "1100"
     *             }
     *         ]
     *     }
     * }
     *
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "id": [
     *                "The id field is required."
     *            ]
     *        }
     *    ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     */
    public function get_player_skills(Request $request)
    {

        $validator = Validator::make($request->all(), User::$get_player_profile_rules);
        if ($validator->fails()) {
            $error_result[] = $validator->errors();
            return Helper::apiNotFoundResponse(false, 'apiInvalidParamResponse', $error_result);
        }

        $user_id = $request->id;
        $response = $this->checkTrainerOrPlayerRole($user_id);
        if (!$response['status']) {
            return Helper::apiNotFoundResponse(false, $response['msg'], new stdClass());
        }

        /**
         * Get Player Teams
         */

//                $users_skills_points = User::selectRaw('users.id as user_id,skills.id as skill_id,skills.name,skills.image,sum(player_scores.score) as points')
//                    ->join('player_scores', 'player_scores.user_id', '=', 'users.id')
//                    ->join('skills', 'skills.id', '=', 'player_scores.skill_id')
//                    ->where('users.id', $user_id)
//                    ->groupBy('skills.id')
//                    ->get();


        $users_skills_points = PlayerScore::select('user_id', DB::raw("(SUM(score)) as points"), 'skill_id')
            ->with('skill:id,name:image')
            ->where('user_id', $user_id)
            ->groupBy('skill_id')
            ->get();

        if (count($users_skills_points) == 0) {
            return Helper::apiNotFoundResponse(false, __('messages.skills.not_found'), new stdClass());
        }

        $users_skills_points = $users_skills_points->map(function ($item) {
            $obj = new stdClass();
            $obj->user_id = $item->user_id;
            $obj->skill_id = $item->skill_id;
            $obj->name = $item->skill->name;
            $obj->points = $item->points;
            $obj->image = $item->skill->image;

            return $obj;
        });

        $get_player_skills['get_player_skills'] = $users_skills_points;
        return Helper::apiSuccessResponse(true, 'Successfully getting player skills points', $get_player_skills);
    }


    /**
     *
     * GetPlayerSkillMetrics
     *
     * @queryParam user_id required user id is required
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Metrics found",
     * "Result": {
     * "total_distance": 22019.27,
     * "avg_speed": 49.55,
     * "avg_heart_rate": 51.18,
     * "max_heart_rate": 104.1,
     * "walking_speed": 48.2,
     * "sprinting_speed": 50.32,
     * "running_speed": 51.49,
     * "ball_kicks": 462,
     * "received_impacts": 1437,
     * "no_of_steps": 0
     * }
     * }
     *
     */


    public function get_player_skill_metrics(Request $request)
    {
        $user_id = $request->user_id;
        $distance = MatchStat::selectRaw('SUM(stat_value) AS distance')->join('matches_stats_types', 'matches_stats_types.id', '=', 'matches_stats.stat_type_id')->where('matches_stats.player_id', $user_id)->where('matches_stats_types.name', '=', 'TOTAL_DISTANCE')->first();
        $speed = MatchStat::selectRaw('AVG(stat_value) AS speed')->join('matches_stats_types', 'matches_stats_types.id', '=', 'matches_stats.stat_type_id')->where('matches_stats.player_id', $user_id)->where('matches_stats_types.name', '=', 'SPEED_AVG')->first();
        $heart_rate = MatchStat::selectRaw('AVG(stat_value) AS avg, MAX(stat_value) AS max')->join('matches_stats_types', 'matches_stats_types.id', '=', 'matches_stats.stat_type_id')->where('matches_stats.player_id', $user_id)->where('matches_stats_types.name', '=', 'HR_AVG')->first();
        $walking_speed = MatchStat::selectRaw('AVG(stat_value) AS speed')->join('matches_stats_types', 'matches_stats_types.id', '=', 'matches_stats.stat_type_id')->where('matches_stats.player_id', $user_id)->where('matches_stats_types.name', '=', 'SPEED_WALKING')->first();
        $sprinting_speed = MatchStat::selectRaw('AVG(stat_value) AS speed')->join('matches_stats_types', 'matches_stats_types.id', '=', 'matches_stats.stat_type_id')->where('matches_stats.player_id', $user_id)->where('matches_stats_types.name', '=', 'SPEED_SPRINTING')->first();
        $running_speed = MatchStat::selectRaw('AVG(stat_value) AS speed')->join('matches_stats_types', 'matches_stats_types.id', '=', 'matches_stats.stat_type_id')->where('matches_stats.player_id', $user_id)->where('matches_stats_types.name', '=', 'SPEED_RUNNING')->first();
        $ball_kicks = MatchDetails::selectRaw('COUNT(event_type) AS ball_kicks')->where('event_type', 'BK')->where('user_id', $user_id)->first();
        $received_impacts = MatchDetails::selectRaw('COUNT(event_type) AS impacts')->where('event_type', 'FK')->where('user_id', $user_id)->first();
        $metrics = [
            'total_distance' => round($distance->distance, 2),
            'avg_speed' => round($speed->speed, 2),
            'avg_heart_rate' => round($heart_rate->avg, 2),
            'max_heart_rate' => round($heart_rate->max, 2),
            'walking_speed' => round($walking_speed->speed, 2),
            'sprinting_speed' => round($sprinting_speed->speed, 2),
            'running_speed' => round($running_speed->speed, 2),
            'ball_kicks' => round($ball_kicks->ball_kicks, 2),
            'received_impacts' => round($received_impacts->impacts, 2),
            'no_of_steps' => 0,
        ];
        return Helper::apiSuccessResponse(true, 'Metrics found', $metrics);

    }

    /**
     *
     * GetPlayerSkillInsight
     *
     * Containing date wise scores of current user for given skill.
     *
     * @queryParam user_id required user id is required
     * @queryParam skill_id required skill id is required
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found",
     * "Result": {
     * "labels": [
     * "2020-10-07",
     * "2020-10-06"
     * ],
     * "data_1": [
     * 12,
     * 109
     * ],
     * "data_2": []
     * }
     * }
     *
     */

//    public function get_player_skill_insight(Request $request)
//    {
//        $validator = Validator::make($request->all(), User::$get_player_skill_insight_rules);
//
//        if ($validator->fails()) {
//            $error_result[] = $validator->errors();
//            return Helper::apiUnAuthenticatedResponse(false, 'apiInvalidParamResponse', $error_result);
//        }
//
//        $user_id = $request->user_id;
//        $skill_id = $request->skill_id;
//
//
//        //$user_id =  Auth::user()->id;
//        $user = User::find($user_id);
//        if ($user === null) {
//            $message = "User has not been found with this id " . $user_id;
//            return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
//        }
//        else {
//            /**
//             * Check User is either trainer/player
//             **/
//            $user_trainer_player_check = is_null(Trainer::where('user_id', $user_id)->first()) ? 'player' : 'trainer';
//            if ($user_trainer_player_check == 'trainer') {
//
//                $message = "Can't find records with trainer profile";
//                return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
//            }
//            elseif ($user_trainer_player_check == 'player') {
//
//                /**
//                 * Get Player Teams
//                 */
//
//                $users_skills_points = User::selectRaw('users.id,skills.id,skills.name,player_scores.score,player_scores.skill_id,player_scores.created_at')
//                    ->join('player_scores', 'player_scores.user_id', '=', 'users.id')
//                    ->join('skills', 'skills.id', '=', 'player_scores.skill_id')
//                    ->where('users.id', $user_id)
//                    ->where('skills.id', $skill_id)
//                    ->latest('player_scores.created_at')
//                    ->get();
//
//                if (count($users_skills_points) == 0) {
//                    $message = "Current user(player) is not associated to any skill";
//                    return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
//
//                }
//
//                $get_player_skills['get_player_skill_insight'] = $users_skills_points;
//                return Helper::apiSuccessResponse(true, 'Successfully getting player skills points', $get_player_skills);
//
//            }
//        }
//    }

//    public function get_player_skill_insight(Request $request)
//    {
//        Validator::make($request->all(), User::$get_player_skill_insight_rules)->validate();
//
//        $user_id = $request->user_id;
//        $skill_id = $request->skill_id;
//
//        $points = PlayerScore::select('score', 'created_at')
//            ->where('user_id', $user_id)
//            ->where('skill_id', $skill_id)
//            ->groupByRaw('YEAR(created_at),MONTH(created_at),DAY(created_at)')
//            ->orderByRaw('YEAR(created_at) DESC , MONTH(created_at) DESC , DAY(created_at) DESC')
//            ->get();
//
//        if (count($points) == 0) {
//            return Helper::apiNotFoundResponse(false, 'Records not found', []);
//        }
//
//        $labels = [];
//        $data_1 = [];
//        $data_2 = [];
//
//        foreach ($points as $key => $item) {
//
//            if ($item->created_at == "" || $item->score == "") continue;
//
//            array_push($labels, Carbon::parse($item->created_at)->format('Y-m-d'));
//            array_push($data_1, $item->score);
//        }
//
//        $results['labels'] = $labels;
//        $results['data_1'] = $data_1;
//        $results['data_2'] = $data_2;
//
//        return Helper::apiSuccessResponse(true, 'Records found', $results);
//    }

    public function get_player_skill_insight(Request $request)
    {
        Validator::make($request->all(), User::$get_player_skill_insight_rules)->validate();

        $user_id = $request->user_id;
        $skill_id = $request->skill_id;

        $points = PlayerScore::select('score', 'created_at')
            ->where('user_id', $user_id)
            ->where('skill_id', $skill_id)
            ->groupByRaw('YEAR(created_at),MONTH(created_at),DAY(created_at)')
            ->orderByRaw('YEAR(created_at) DESC , MONTH(created_at) DESC , DAY(created_at) DESC')
            ->get();

        if (count($points) == 0) {
            return Helper::apiNotFoundResponse(false, 'Records not found', []);
        }

        $labels = [];
        $data_1 = [];
        $data_2 = [];

        foreach ($points as $key => $item) {

            if ($item->created_at == "" || $item->score == "") continue;

            array_push($labels, Carbon::parse($item->created_at)->format('Y-m-d'));
            array_push($data_1, $item->score);
        }

        $results['labels'] = $labels;
        $results['data_1'] = $data_1;
        $results['data_2'] = $data_2;

        return Helper::apiSuccessResponse(true, 'Records found', $results);
    }

    /**
     * GetPlayerLeague
     *
     * containing leangue icon & list of all players in current players league along with sum of their scores along with current user's league calculated above.
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Successfully getting player league and other players data",
     *     "Result": {
     *         "league_icon": "https://www.kindpng.com/picc/m/183-1838998_trophy2-awards-and-achievements-logo-hd-png-download.png",
     *         "league_data": [
     *             {
     *                 "id": 1,
     *                 "first_name": "Fahad",
     *                 "middle_name": null,
     *                 "last_name": "Ahmed",
     *                 "email": null,
     *                 "profile_picture": null,
     *                 "leaderboards": {
     *                     "user_id": 1,
     *                     "total_score": 1200,
     *                     "position": 12
     *                 }
     *             },
     *             {
     *                 "id": 3,
     *                 "first_name": "Fahad",
     *                 "middle_name": null,
     *                 "last_name": "Coder",
     *                 "email": null,
     *                 "profile_picture": null,
     *                 "leaderboards": {
     *                     "user_id": 3,
     *                     "total_score": 1900,
     *                     "position": 19
     *                 }
     *             }
     *         ]
     *     }
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     * }
     *
     * @response 401{
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Current user(player) is not associated to any league",
     *     "Result": {}
     * }
     */

    public function get_player_league(Request $request)
    {

        /*$current_user_id = Auth::user()->id;
        $current_player_league = Auth::user()->leagues;*/

        $current_player_leaderboard = Auth::user()->leaderboards;
        if (is_null($current_player_leaderboard)) {
            return Helper::apiUnAuthenticatedResponse(false, __('messages.leaderboard.not_found'), new stdClass());

        } else {
            $current_player_leaderboard_score = $current_player_leaderboard['total_score'];
            if (trim($current_player_leaderboard_score) <= 2000) {

                $get_other_players_leaderboards = User::Select('id', 'first_name', 'middle_name', 'last_name', 'email', 'profile_picture')->with(['leaderboards' => function ($q) {
                    $q->select('leaderboards.user_id', 'leaderboards.total_score', 'leaderboards.position');
                }])
                    ->whereHas('leaderboards', function ($q) {
                        $q->where('leaderboards.total_score', '<=', '2000');
                    })->get()->sortBy('leaderboards.position')->values()->all();

                $get_player_league_data['league_icon'] = 'https://www.kindpng.com/picc/m/183-1838998_trophy2-awards-and-achievements-logo-hd-png-download.png';
                $get_player_league_data['league_data'] = $get_other_players_leaderboards;

                return Helper::apiSuccessResponse(true, 'Successfully getting player league and other players data', $get_player_league_data);
                //return $get_other_players_leaderboards;
                //return 'bronze';
            } elseif ((trim($current_player_leaderboard_score) > 2000) && (trim($current_player_leaderboard_score) <= 3000)) {


                $get_other_players_leaderboards = User::Select('id', 'first_name', 'middle_name', 'last_name', 'email', 'profile_picture')->with(['leaderboards' => function ($q) {
                    $q->select('leaderboards.user_id', 'leaderboards.total_score', 'leaderboards.position');

                }])
                    ->whereHas('leaderboards', function ($q) {
                        $q->where('leaderboards.total_score', '>', '2000');
                        $q->where('leaderboards.total_score', '<=', '3000');
                    })->get()->sortBy('leaderboards.position')->values()->all();

                $get_player_league_data['league_icon'] = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSI3TQ_25G5CqAYH8RRCXOz7L2XtwXyLp1U4vemceqyFV8iaMw&s';
                $get_player_league_data['league_data'] = $get_other_players_leaderboards;

                return Helper::apiSuccessResponse(true, 'Successfully getting player league and other players data', $get_player_league_data);

                //return $get_other_players_leaderboards;
            } elseif ((trim($current_player_leaderboard_score) > 3000) && (trim($current_player_leaderboard_score) <= 4000)) {

                $get_other_players_leaderboards = User::Select('id', 'first_name', 'middle_name', 'last_name', 'email', 'profile_picture')->with(['leaderboards' => function ($q) {
                    $q->select('leaderboards.user_id', 'leaderboards.total_score', 'leaderboards.position');

                }])
                    ->whereHas('leaderboards', function ($q) {
                        $q->where('leaderboards.total_score', '>', '3000');
                        $q->where('leaderboards.total_score', '<=', '4000');
                    })->get()->sortBy('leaderboards.position')->values()->all();

                $get_player_league_data['league_icon'] = 'https://www.pinclipart.com/picdir/middle/160-1609821_recent-student-achievements-you-winner-clipart.png';
                $get_player_league_data['league_data'] = $get_other_players_leaderboards;

                return Helper::apiSuccessResponse(true, 'Successfully getting player league and other players data', $get_player_league_data);
                //return $get_other_players_leaderboards;
                //return 'gold';
            } else {

                $get_other_players_leaderboards = User::Select('id', 'first_name', 'middle_name', 'last_name', 'email', 'profile_picture')->with(['leaderboards' => function ($q) {
                    $q->select('leaderboards.user_id', 'leaderboards.total_score', 'leaderboards.position');

                }])
                    ->whereHas('leaderboards', function ($q) {
                        $q->where('leaderboards.total_score', '>', '4000');
                    })->get()->sortBy('leaderboards.position')->values()->all();

                $get_player_league_data['league_icon'] = 'https://img1.cgtrader.com/items/75207/54574752ad/winner-cup-7-3d-model-max.jpg';
                $get_player_league_data['league_data'] = $get_other_players_leaderboards;

                return Helper::apiSuccessResponse(true, 'Successfully getting player league and other players data', $get_player_league_data);

            }

        }

    }


    /**
     * GetPlayerRecommendedExercises
     *
     * getting player recommended exercises
     *
     * @queryParam  id required user id is required
     * @queryParam  skill_id required skill id is required
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Successfully getting player recommended exercises",
     *     "Result": {
     *         "get_player_recommended_exercises": [
     *             {
     *                 "id": 1,
     *                 "title": "10 Cones Free - Regular Ball",
     *                 "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     *                 "levels_count": 2,
     *                 "skills": [
     *                     {
     *                         "id": 1,
     *                         "name": "Agility ",
     *                         "pivot": {
     *                             "exercise_id": 1,
     *                             "skill_id": 1
     *                         }
     *                     },
     *                     {
     *                         "id": 2,
     *                         "name": "Ball Control",
     *                         "pivot": {
     *                             "exercise_id": 1,
     *                             "skill_id": 2
     *                         }
     *                     },
     *                     {
     *                         "id": 5,
     *                         "name": "Accuracy",
     *                         "pivot": {
     *                             "exercise_id": 1,
     *                             "skill_id": 5
     *                         }
     *                     }
     *                 ],
     *                 "tools": [
     *                     {
     *                         "id": 1,
     *                         "tool_name": "Cones",
     *                         "pivot": {
     *                             "exercise_id": 1,
     *                             "tool_id": 1
     *                         }
     *                     },
     *                     {
     *                         "id": 2,
     *                         "tool_name": "Ball",
     *                         "pivot": {
     *                             "exercise_id": 1,
     *                             "tool_id": 2
     *                         }
     *                     },
     *                     {
     *                         "id": 4,
     *                         "tool_name": "Goal",
     *                         "pivot": {
     *                             "exercise_id": 1,
     *                             "tool_id": 4
     *                         }
     *                     }
     *                 ]
     *             },
     *             {
     *                 "id": 2,
     *                 "title": "10 Cones Left - Regular Ball",
     *                 "image": "https://camo.githubusercontent.com/8711e1e5b796488ab56ea297dfdc946ae709d029/68747470733a2f2f692e696d6775722e636f6d2f6c4a567a3249492e706e67",
     *                 "levels_count": 2,
     *                 "skills": [
     *                     {
     *                         "id": 3,
     *                         "name": "Technique",
     *                         "pivot": {
     *                             "exercise_id": 2,
     *                             "skill_id": 3
     *                         }
     *                     },
     *                     {
     *                         "id": 4,
     *                         "name": "Foot Work",
     *                         "pivot": {
     *                             "exercise_id": 2,
     *                             "skill_id": 4
     *                         }
     *                     },
     *                     {
     *                         "id": 5,
     *                         "name": "Accuracy",
     *                         "pivot": {
     *                             "exercise_id": 2,
     *                             "skill_id": 5
     *                         }
     *                     }
     *                 ],
     *                 "tools": [
     *                     {
     *                         "id": 3,
     *                         "tool_name": "Wall",
     *                         "pivot": {
     *                             "exercise_id": 2,
     *                             "tool_id": 3
     *                         }
     *                     },
     *                     {
     *                         "id": 4,
     *                         "tool_name": "Goal",
     *                         "pivot": {
     *                             "exercise_id": 2,
     *                             "tool_id": 4
     *                         }
     *                     }
     *                 ]
     *             }
     *         ]
     *     }
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "id": [
     *                 "The user id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "id": [
     *                 "The selected id is invalid."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "skill_id": [
     *                 "The selected skill id is invalid."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "skill_id": [
     *                 "The skill id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     */


    public function get_player_recommended_exercises(Request $request)
    {

        $request->validate(User::$get_player_recommended_exercises_rules);

        $user_id = $request->id;
        $skill_id = $request->skill_id;

        $user = User::role("player")->with("roles")->find($user_id);
        if (!$user) {
            return Helper::apiNotFoundResponse(false, "User Not Found With The Given Id", new stdClass());
        }
        /**
         * Get Player Recommended Exercises
         */
        $player_recommended_exercises =
            PlayerRecommendedEexercise::select('id', 'user_id', 'exercise_id')
                ->where('user_id', $user_id)
                ->latest()->get();

        if (count($player_recommended_exercises) == 0) {
            $player_recommended_exercises =
                Exercise::select('id', 'title', 'image')
                    ->whereHas('skills', function ($q) use ($skill_id) {
                        $q->where('skills.id', $skill_id);
                    })
                    ->with(['skills' => function ($q2) {
                        $q2->select('skills.id', 'skills.name');
                    }])->with(['tools' => function ($q2) {
                        $q2->select('tools.id', 'tools.tool_name');
                    }])
                    ->withCount('levels')
                    ->latest()->get();
            if (count($player_recommended_exercises) == 0) {
                $message = "Given skill is not in exercise_skills entity.";
                return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
            } else {
                $get_player_skills['get_player_recommended_exercises'] = $player_recommended_exercises;
                return Helper::apiSuccessResponse(true, 'Successfully getting player recommended exercises', $get_player_skills);
            }

        }
        $store_exercises_ids = array();
        foreach ($player_recommended_exercises as $value) {
            $store_exercises_ids[] = $value['exercise_id'];
        }
        $store_exercises_unique_ids = array_unique($store_exercises_ids);
        $player_recommended_exercises =
            Exercise::select('id', 'title', 'image')
                ->with(['skills' => function ($q2) {
                    $q2->select('skills.id', 'skills.name');
                }])
                ->with(['tools' => function ($q2) {
                    $q2->select('tools.id', 'tools.tool_name');
                }])
                ->whereIn('id', $store_exercises_unique_ids)
                ->withCount('levels')
                ->latest()->get();

        $get_player_skills['get_player_recommended_exercises'] = $player_recommended_exercises;
        return Helper::apiSuccessResponse(true, 'Successfully getting player recommended exercises', $get_player_skills);

    }


    /**
     * GetPlayerFollowersFollowings
     *
     * Getting player's followers & followings list
     *
     * @queryParam  id required user id is required
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Successfully getting player followers & followings",
     * "Result": {
     * "player_followers": [
     * {
     * "id": 3,
     * "first_name": "Hasnain",
     * "middle_name": null,
     * "last_name": "Ali",
     * "profile_picture": "media/users/609bdad748e321620826839.jpeg",
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 5,
     * "first_name": "Alex",
     * "middle_name": "",
     * "last_name": "Ferguson",
     * "profile_picture": null,
     * "current_player_id": 2,
     * "follow_status": false
     * },
     * {
     * "id": 20,
     * "first_name": "falak",
     * "middle_name": "''",
     * "last_name": "Saad",
     * "profile_picture": "media/users/5f20223ea98da1595941438.jpeg",
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 44,
     * "first_name": "Jahanzeb",
     * "middle_name": "''",
     * "last_name": "Khan",
     * "profile_picture": null,
     * "current_player_id": 2,
     * "follow_status": false
     * },
     * {
     * "id": 6,
     * "first_name": "Fami",
     * "middle_name": "''",
     * "last_name": "Sultana",
     * "profile_picture": null,
     * "current_player_id": 2,
     * "follow_status": false
     * },
     * {
     * "id": 56,
     * "first_name": "david",
     * "middle_name": "''",
     * "last_name": "dwinger",
     * "profile_picture": "media/users/5f9bcfdf102801604046815.jpeg",
     * "current_player_id": 2,
     * "follow_status": false
     * },
     * {
     * "id": 50,
     * "first_name": "Xandra",
     * "middle_name": "''",
     * "last_name": "Daswani",
     * "profile_picture": "media/users/5f97dcb5129a41603787957.jpeg",
     * "current_player_id": 2,
     * "follow_status": false
     * },
     * {
     * "id": 4,
     * "first_name": "Tariq",
     * "middle_name": null,
     * "last_name": "Sidd",
     * "profile_picture": "media/users/5f7b294249cca1601907010.jpeg",
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 389,
     * "first_name": "Famiiii",
     * "middle_name": "''",
     * "last_name": "2",
     * "profile_picture": "media/users/605c7a3e1a79a1616673342.jpeg",
     * "current_player_id": 2,
     * "follow_status": false
     * },
     * {
     * "id": 11,
     * "first_name": "Saad",
     * "middle_name": "''",
     * "last_name": "Saleem",
     * "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg",
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 16,
     * "first_name": "Ali",
     * "middle_name": "''",
     * "last_name": "Mehdi",
     * "profile_picture": "media/users/609543696d2ac1620394857.jpeg",
     * "current_player_id": 2,
     * "follow_status": false
     * }
     * ],
     * "player_followings": [
     * {
     * "id": 20,
     * "first_name": "falak",
     * "middle_name": "''",
     * "last_name": "Saad",
     * "profile_picture": "media/users/5f20223ea98da1595941438.jpeg",
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 10,
     * "first_name": "Tariq",
     * "middle_name": "''",
     * "last_name": "Sidd",
     * "profile_picture": null,
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 7,
     * "first_name": "Fatima",
     * "middle_name": "''",
     * "last_name": "Sultana",
     * "profile_picture": null,
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 128,
     * "first_name": "baran",
     * "middle_name": "''",
     * "last_name": "erdogan",
     * "profile_picture": "media/users/5f99cb1c40f871603914524.jpeg",
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 26,
     * "first_name": "Cantest",
     * "middle_name": "''",
     * "last_name": "Ulkertest",
     * "profile_picture": "media/users/5f1ecc7c72c951595853948.jpeg",
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 4,
     * "first_name": "Tariq",
     * "middle_name": null,
     * "last_name": "Sidd",
     * "profile_picture": "media/users/5f7b294249cca1601907010.jpeg",
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 11,
     * "first_name": "Saad",
     * "middle_name": "''",
     * "last_name": "Saleem",
     * "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg",
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 40,
     * "first_name": "Umer",
     * "middle_name": null,
     * "last_name": "Shaikh",
     * "profile_picture": "media/users/OF4YBPrj3vkx7QtIztDKOAZod45t1sSsXQfc1cPE.jpeg",
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 3,
     * "first_name": "Hasnain",
     * "middle_name": null,
     * "last_name": "Ali",
     * "profile_picture": "media/users/609bdad748e321620826839.jpeg",
     * "current_player_id": 2,
     * "follow_status": true
     * },
     * {
     * "id": 251,
     * "first_name": "Saa",
     * "middle_name": "''",
     * "last_name": "SA",
     * "profile_picture": null,
     * "current_player_id": 2,
     * "follow_status": true
     * }
     * ]
     * }
     * }
     *
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "id": [
     *                "The id field is required."
     *            ]
     *        }
     *    ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     */

    public function get_player_followers_followings(Request $request)
    {

        $validator = Validator::make($request->all(), User::$get_player_profile_rules);
        if ($validator->fails()) {
            $error_result[] = $validator->errors();
            return Helper::apiUnAuthenticatedResponse(false, 'apiInvalidParamResponse', $error_result);
        }

        $user_id = $request->id;
        $user = User::find($user_id);
        if ($user === null) {
            $message = "User has not been found with this id " . $user_id;
            return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
        } else {
            /**
             * Check User is either trainer/player
             **/

            $user_trainer_player_check = is_null(Trainer::where('user_id', $user_id)->first()) ? 'player' : 'trainer';

            if ($user_trainer_player_check == 'trainer') {

                $message = "Can't find records with trainer profile";
                return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
            } elseif ($user_trainer_player_check == 'player') {

                /**
                 * Get Player Followers & Followings
                 */

                //$player_followers_followings = User::with('followers', 'followings')->find($user_id);

                $followers_details = $this->player_followers_followings();

                $followings_ids = $followers_details['followings_ids'];
                $followers_ids = $followers_details['followers_ids'];
                $player_followers_followings = $followers_details['player_followers_followings'];
                $player_followers = $player_followers_followings->followers;

                $request->merge([
                    "followings_ids" => $followings_ids
                ]);

                $player_followers = PlayerFollowerFollowingResource::collection($player_followers)->toArray($request);
                $player_followings = $player_followers_followings->followings;
                $player_followings = PlayerFollowerFollowingResource::collection($player_followings)->toArray($request);

                $user_followers_followings['player_followers'] = $player_followers;
                $user_followers_followings['player_followings'] = $player_followings;

                return Helper::apiSuccessResponse(true, 'Successfully getting player followers & followings', $user_followers_followings);

            }
        }

    }


    /**
     * SearchPlayers
     *
     * Getting player's followers & followings list
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Successfully getting players",
     * "Result": {
     * "data": [
     * {
     * "id": 9,
     * "first_name": "abdul",
     * "middle_name": "''",
     * "last_name": "Haseeb",
     * "profile_picture": null,
     * "current_player_id": 10,
     * "follow_status": false
     * },
     * {
     * "id": 153,
     * "first_name": "exercitationem",
     * "middle_name": "''",
     * "last_name": "harum",
     * "profile_picture": "media/users/5ff44f23dbe3b1609846563.jpeg",
     * "current_player_id": 10,
     * "follow_status": false
     * },
     * {
     * "id": 344,
     * "first_name": "hafa",
     * "middle_name": "''",
     * "last_name": "daa",
     * "profile_picture": null,
     * "current_player_id": 10,
     * "follow_status": false
     * },
     * {
     * "id": 103,
     * "first_name": "Hardy",
     * "middle_name": "''",
     * "last_name": "Little",
     * "profile_picture": "media/users/placeholder.png",
     * "current_player_id": 10,
     * "follow_status": false
     * },
     * {
     * "id": 53,
     * "first_name": "hasan",
     * "middle_name": "''",
     * "last_name": "shah",
     * "profile_picture": null,
     * "current_player_id": 10,
     * "follow_status": false
     * },
     * {
     * "id": 3,
     * "first_name": "Hasnain",
     * "middle_name": null,
     * "last_name": "Ali",
     * "profile_picture": "media/users/609bdad748e321620826839.jpeg",
     * "current_player_id": 10,
     * "follow_status": false
     * },
     * {
     * "id": 542,
     * "first_name": "Hasnain",
     * "middle_name": "''",
     * "last_name": "Father's",
     * "profile_picture": "media/users/60c769c11082e1623681473.jpeg",
     * "current_player_id": 10,
     * "follow_status": false
     * },
     * {
     * "id": 156,
     * "first_name": "Hasnain",
     * "middle_name": "''",
     * "last_name": "Ali",
     * "profile_picture": null,
     * "current_player_id": 10,
     * "follow_status": false
     * },
     * {
     * "id": 157,
     * "first_name": "Hasnain",
     * "middle_name": "''",
     * "last_name": "Ali",
     * "profile_picture": null,
     * "current_player_id": 10,
     * "follow_status": false
     * },
     * {
     * "id": 176,
     * "first_name": "Hasnain",
     * "middle_name": "''",
     * "last_name": "Ali",
     * "profile_picture": null,
     * "current_player_id": 10,
     * "follow_status": false
     * },
     * {
     * "id": 27,
     * "first_name": "Hassan",
     * "middle_name": "''",
     * "last_name": "Shah",
     * "profile_picture": null,
     * "current_player_id": 10,
     * "follow_status": false
     * },
     * {
     * "id": 92,
     * "first_name": "Nathanael",
     * "middle_name": "''",
     * "last_name": "Haag",
     * "profile_picture": "media/users/placeholder.png",
     * "current_player_id": 10,
     * "follow_status": false
     * },
     * {
     * "id": 524,
     * "first_name": "umer",
     * "middle_name": "''",
     * "last_name": "hammad",
     * "profile_picture": null,
     * "current_player_id": 10,
     * "follow_status": false
     * }
     * ],
     * "meta": {
     * "current_page": 1,
     * "first_page_url": "http://127.0.0.1:8000/api/v1/app/search-players-with-followers-followings?page=1",
     * "from": 1,
     * "last_page": 1,
     * "last_page_url": "http://127.0.0.1:8000/api/v1/app/search-players-with-followers-followings?page=1",
     * "next_page_url": null,
     * "per_page": 1000,
     * "prev_page_url": null,
     * "total": 13
     * }
     * }
     * }
     *
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "id": [
     *                "The id field is required."
     *            ]
     *        }
     *    ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     * @queryParam player_name required Player name is required
     * @queryParam limit required integer records per page
     * @queryParam page integer for page number
     */
    public function search_players_with_followers_followings(Request $request)
    {
        $request->validate([
            'player_name' => 'required',
            'limit' => 'required|integer|min:1',
//            'offset'=>'required|integer|min:0'
        ]);

        $player_name = $request->player_name;
        $followers_details = $this->player_followers_followings();

        $followings_ids = $followers_details['followings_ids'];
//        $followers_ids = $followers_details['followers_ids'];

        $request->merge([
            "followings_ids"=>$followings_ids
        ]);

        $player_followers_followings = $followers_details['player_followers_followings'];

        $player_followers = $player_followers_followings->followers;
        $player_followers = PlayerFollowerFollowingResource::collection($player_followers)->toArray($request);

        $player_followings = $player_followers_followings->followings;
        $player_followings_ids = $player_followings->pluck("id")->toArray();

        $player_followings = PlayerFollowerFollowingResource::collection($player_followings)->toArray($request);

        $user_followers_followings['player_followers'] = $player_followers;
        $user_followers_followings['player_followings'] = $player_followings;

        $all_players = User::Select('id', 'first_name', 'middle_name', 'last_name', 'profile_picture')
            ->where('first_name', 'like', $player_name . '%')
            ->orWhere('last_name', 'like', $player_name . '%')
            ->where('id', '!=', auth()->id())
            ->role('player')
            ->orderBy('first_name', 'asc')
            ->get();
        $request->merge([
            "player_followings_ids" => $player_followings_ids
        ]);

        $records = Helper::paginate($all_players, (int)$request->limit);
        $meta = $records->toArray();
        $response = [
            'data' => PlayerSearchListingResource::collection($records->values()->all())->toArray($request),
            'meta' => [
                'current_page' => $meta['current_page'],
                'first_page_url' => $meta['first_page_url'],
                'from' => $meta['from'],
                'last_page' => $meta['last_page'],
                'last_page_url' => $meta['last_page_url'],
                'next_page_url' => $meta['next_page_url'],
                'per_page' => $meta['per_page'],
                'prev_page_url' => $meta['prev_page_url'],
                'total' => $meta['total']
            ]
        ];

        if (count($records->values()->all()) == 0) {
            return Helper::apiNotFoundResponse(false, 'No players found', []);
        }
        return Helper::apiSuccessResponse(true, 'Successfully getting players', $response);

    }


    /**
     * CreateRemoveCurrentPlayerFollowing
     *
     * Creating and removing following list
     *
     * @bodyParam follower_id required user id is required
     * @bodyParam following_id required user id is required
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "You are following the player now",
     *     "Result": {
     *         "user_id": "7",
     *         "contact_user_id": "2",
     *         "status_id": 1,
     *         "updated_at": "2020-07-23 17:25:39",
     *         "created_at": "2020-07-23 17:25:39",
     *         "id": 14
     *     }
     * }
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "User has been unfollowed",
     *     "Result": {}
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "follower_id": [
     *                 "The follower id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "following_id": [
     *                 "The following id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "follower_id": [
     *                 "The follower id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "following_id": [
     *                 "The following id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     */

    public function create_remove_player_following(Request $request)
    {
        $validator = Validator::make($request->all(), User::$create_player_profile_rules);
        if ($validator->fails()) {
            $error_result[] = $validator->errors();
            return Helper::apiUnAuthenticatedResponse(false, 'apiInvalidParamResponse', $error_result);
        }

        $follower_id = $request->follower_id;
        $following_id = $request->following_id;

        $contact = Contact::where('user_id', $follower_id)->where('contact_user_id', $following_id)->first();
        if (!$contact) {
            $profile = UserPrivacySetting::where('user_id', $following_id)->first();
            if ($profile && $profile->access_modifier != 1) {
                $status_id = 4;
            } else {
                $status_id = 1;
            }
            $contact = new Contact();
            $request->request->add(
                [
                    'user_id' => $follower_id,
                    'contact_user_id' => $following_id,
                    'status_id' => $status_id
                ]
            );

            $response = $contact->store($request);

            $data['from_user_id'] = Auth::user()->id;
            $data['to_user_id'] = $following_id;
            $data['model_type'] = 'user/follow';
            $data['model_type_id'] = Auth::user()->id;
            $data['click_action'] = 'OthersProfile';
            if ($status_id == 1) {
                $data['message']['en'] = Auth::user()->first_name . ' ' . Auth::user()->last_name . ' started following you';
                $data['message']['nl'] = Auth::user()->first_name . ' ' . Auth::user()->last_name . ' volgt je nu';
            } else {
                $data['message']['en'] = Auth::user()->first_name . ' ' . Auth::user()->last_name . ' wants to follow you';
                $data['message']['nl'] = Auth::user()->first_name . ' ' . Auth::user()->last_name . ' wil je volgen';
            }
            $data['message'] = json_encode($data['message']);

            $user = User::find($following_id);

            $devices = $user->user_devices;
            $tokens = [];

            $user = User::find($following_id);
            $data['badge_count'] = $user->badge_count + 1;

            foreach ($devices as $device) {
                Helper::sendNotification($data, $device->onesignal_token, $device->device_type);
            }

            $user->badge_count = $data['badge_count'];
            $user->save();

            return Helper::apiSuccessResponse(true, 'You are following the player now', $response);

        } else {

            $contact->delete();
            return Helper::apiSuccessResponse(true, 'User has been unfollowed', new stdClass());
        }

    }

    /**
     * Get Follow Requests
     *
     * @response{
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Follow requests found",
     * "Result": [
     * {
     * "id": 1,
     * "first_name": "muhammad.",
     * "middle_name": null,
     * "last_name": "shahzaib",
     * "profile_picture": "media/users/5fa27263a93271604481635.jpeg"
     * }
     * ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "follower_id": [
     *                 "The follower id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "following_id": [
     *                 "The following id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "follower_id": [
     *                 "The follower id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "following_id": [
     *                 "The following id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     */


    public function getFollowingRequests(Request $request)
    {
        $follow_requests = User::select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.profile_picture', 'contacts.created_at as requested_at')
            ->join('contacts', 'users.id', 'contacts.user_id')
            ->where('contacts.status_id', 2)
            ->where('contacts.contact_user_id', auth()->user()->id)
            ->get();
        if ($follow_requests->count()) {
            return Helper::apiSuccessResponse(true, 'Follow requests found', $follow_requests);
        }
        return Helper::apiErrorResponse(false, 'No follow requests found', new stdClass());
    }


    /**
     * Update folllow request status
     * @bodyParam user_id required user id is required
     * @bodyParam status required
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Success",
     *     "Result": {}
     * }
     *
     *
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     */


    public function updateFollowRequestStatus(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'status' => 'required'
        ]);
        $follow_request = Contact::where('contact_user_id', auth()->user()->id)->where('user_id', $request->user_id)->first();
        if (!$follow_request) {
            return Helper::apiErrorResponse(false, 'Invalid request', new stdClass());
        }
        if ($request->user_id === auth()->user()->id) {
            return Helper::apiErrorResponse(false, 'Invalid request', new stdClass());
        }
        if ($request->status === $follow_request->status_id) {
            return Helper::apiErrorResponse(false, 'Status is invalid', new stdClass());
        }
        $follow_request->status_id = $request->status;
        $follow_request->save();
        $status_id = $request->status;
        if ($status_id == 1) {
            $data['from_user_id'] = Auth::user()->id;
            $data['to_user_id'] = $follow_request->user_id;
            $data['model_type'] = 'user/follow';
            $data['model_type_id'] = Auth::user()->id;
            $data['click_action'] = 'OthersProfile';
            $data['message']['en'] = Auth::user()->first_name . ' ' . Auth::user()->last_name . ' accepted your follow request';
            $data['message']['nl'] = Auth::user()->first_name . ' ' . Auth::user()->last_name . ' heeft je volgverzoek geaccepteerd';

            //todo apply conditions for other status
            $data['message'] = json_encode($data['message']);
            $user = User::find($follow_request->user_id);
            $devices = $user->user_devices;
            $tokens = [];

            $data['badge_count'] = $user->badge_count + 1;
            foreach ($devices as $device) {
                Helper::sendNotification($data, $device->onesignal_token, $device->device_type);
            }
            $user->badge_count = $data['badge_count'];
            $user->save();
        }
        return Helper::apiSuccessResponse(true, 'Success', new \stdClass());
    }

    /**
     * GetPlayerAchievements
     *
     * Containing player achievement records.
     *
     * @queryParam  id required user id is required
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Successfully getting player achievements",
     *     "Result": {
     *         "player_achievements": [
     *             {
     *                 "id": 1,
     *                 "name": "Abc",
     *                 "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     *                 "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
     *                 "created_at": "2020-07-06 13:57:01",
     *                 "updated_at": "2020-07-06 13:57:01",
     *                 "deleted_at": null,
     *                 "pivot": {
     *                     "user_id": 1,
     *                     "achievement_id": 1
     *                 }
     *             },
     *             {
     *                 "id": 4,
     *                 "name": "WWW",
     *                 "image": "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSI3TQ_25G5CqAYH8RRCXOz7L2XtwXyLp1U4vemceqyFV8iaMw&s",
     *                 "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
     *                 "created_at": "2020-07-06 14:03:20",
     *                 "updated_at": "2020-07-06 14:03:20",
     *                 "deleted_at": null,
     *                 "pivot": {
     *                     "user_id": 1,
     *                     "achievement_id": 4
     *                 }
     *             },
     *             {
     *                 "id": 5,
     *                 "name": "MMM",
     *                 "image": "https://www.pinclipart.com/picdir/middle/160-1609821_recent-student-achievements-you-winner-clipart.png",
     *                 "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
     *                 "created_at": "2020-07-06 14:03:20",
     *                 "updated_at": "2020-07-06 14:03:20",
     *                 "deleted_at": null,
     *                 "pivot": {
     *                     "user_id": 1,
     *                     "achievement_id": 5
     *                 }
     *             }
     *         ]
     *     }
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "id": [
     *                 "The id field is required."
     *             ]
     *         }
     *     ]
     * }
     */
    public function get_player_achievements(Request $request)
    {
        $request->validate(User::$get_player_profile_rules);
        $user_id = $request->id;
        $user = User::role("player")->find($user_id);
        if (!$user) {
            return Helper::apiNotFoundResponse(false, "User Not Found With The Given Id", new stdClass());
        }
        /**
         * Get Player Teams
         */
        $player_achievements = User::with('achievements')->find($user_id);

        if (count($player_achievements->achievements) == 0) {
            $message = "Current player is not associated to any achievement";
            return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());

        }
        $get_player_achievements['player_achievements'] = $player_achievements->achievements;
        return Helper::apiSuccessResponse(true, 'Successfully getting player achievements', $get_player_achievements);

    }


    /**
     * GetPlayerTeams
     *
     * Containing Player Teams Record
     *
     * @queryParam  id required user id is required
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Successfully getting player teams",
     *     "Result": {
     *         "player_teams": [
     *             {
     *                 "id": 1,
     *                 "team_name": "Ajax U16",
     *                 "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     *                 "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
     *                 "created_at": "2020-07-07 17:23:45",
     *                 "updated_at": "2020-07-07 17:23:45",
     *                 "deleted_at": null,
     *                  "approved" : 1
     *             }
     *         ]
     *     }
     * }
     *
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "id": [
     *                "The id field is required."
     *            ]
     *        }
     *    ]
     * }
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "id": [
     *                "The selected id is invalid."
     *            ]
     *        }
     *    ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     */

    public function get_player_teams(Request $request)
    {

        $request->validate(User::$get_player_profile_rules);
        $user_id = $request->id;
        $user = User::role("player")->find($user_id);
        if (!$user) {
            return Helper::apiNotFoundResponse(false, "User Not Found With The Given Id", new stdClass());
        }
        $player_teams = User::with('teams')->find($user_id);
        $merge_teams = [];

        $check_requests = PlayerTeamRequest::where('player_user_id', $user_id)->whereIn('status', [1, 2])->get()->pluck('team_id')->toArray();
        if (count($player_teams->teams) > 0 || count($check_requests) > 0) {
            $player_teams = $player_teams->teams()->pluck('team_id')->toArray();
            $merge_teams = array_merge($player_teams, $check_requests);
        }
        if (count($merge_teams)) {
            $teams = Team::with('clubs')->whereIn('id', $merge_teams)->get()->map(function ($t) {
                $team_obj = new \stdClass();
                $team_obj->id = $t->id;
                $team_obj->team_name = $t->team_name;
                $team_obj->image = $t->clubs[0]->image ?? '';
                $team_obj->age_group = $t->age_group;
                $team_obj->created_at = $t->created_at;
                if (auth()->user()->teams->contains($t->id)) {
                    $team_obj->approved = 1; //request is accepted and player is member of team
                } else {
                    //1 - pending
                    //2= accepted
                    //3 = rejected
//                  $st = auth()->user()->team_requests()->find($t->id);
                    $st = PlayerTeamRequest::where('player_user_id', auth()->user()->id)->where('team_id', $t->id)->first();
                    if ($st) {
                        if ($st->status == 1) {
                            $team_obj->approved = 2;
                        } elseif ($st->status == 2) {
                            $team_obj->approved = 1;
                        } else {
                            $team_obj->approved = $st->status;
                        }
                    } else {
                        $team_obj->approved = 1;
                    }
                }
                return $team_obj;
            });
            $get_player_teams['player_teams'] = $teams;
            return Helper::apiSuccessResponse(true, 'Successfully getting player teams', $get_player_teams);
        } else {
            $get_player_teams['player_teams'] = [];
            return Helper::apiSuccessResponse(true, 'Current player is not associated to any team', $get_player_teams);
        }
    }


    /**
     * Send Team Requests
     *
     *
     * @queryParam  teams required array [1,2,3]
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Successfully getting player teams",
     *     "Result": {}
     * }
     *
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "teams": [
     *                "The teams field is required."
     *            ]
     *        }
     *    ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     */

    public function sendTeamRequest(Request $request)
    {
        $this->validate($request, [
            'teams' => 'required|array'
        ]);
//        dd(auth()->user()->teams);

        $player_teams = DB::table('player_team')
            ->join('teams', 'player_team.team_id', '=', 'teams.id')
            ->where('user_id', auth()->user()->id)
            ->whereIn('team_id', $request->teams)->select('teams.team_name')->pluck('team_name')->toArray();
        if (count($player_teams)) {
            $teams = implode(',', $player_teams);
            return Helper::apiErrorResponse(false, 'You are already member of ' . $teams, new \stdClass());
        }

        $team_requests = DB::table('player_team_requests')
            ->join('teams', 'player_team_requests.team_id', '=', 'teams.id')
            ->where('player_user_id', auth()->user()->id)
            ->whereIn('status', [1])
            ->whereIn('team_id', $request->teams)
            ->select('teams.team_name')->pluck('team_name')->toArray();
        if (count($team_requests)) {
            $teams = implode(',', $team_requests);
            return Helper::apiErrorResponse(false, 'You have already sent request to ' . $teams, new \stdClass());
        }


        foreach ($request->teams as $team) {
            PlayerTeamRequest::updateOrCreate([
                'player_user_id' => auth()->user()->id,
                'team_id' => $team
            ], [
                'player_user_id' => auth()->user()->id,
                'team_id' => $team,
                'status' => 1,
            ]);
        }
        return Helper::apiSuccessResponse(true, 'Request sent successfully', new \stdClass());
    }


    /**
     * Leave team
     * @queryParam  team_id required
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Successfully left team",
     *     "Result": {}
     * }
     *
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "team_id": [
     *                "The team_id field is required."
     *            ]
     *        }
     *    ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     */


    public function leaveTeam(Request $request)
    {
        $this->validate($request, [
            'team_id' => 'required'
        ]);
        $team = PlayerTeam::where('team_id', $request->team_id)->where('user_id', auth()->user()->id)->first();
        if (!$team) {
            $team = PlayerTeamRequest::where('team_id', $request->team_id)->where('player_user_id', auth()->user()->id)->first();
            if (!$team) {
                $message = "Invalid team selected";
                return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
            } else {
                $team->delete();
                return Helper::apiSuccessResponse(true, 'Request deleted Successfully', new \stdClass());
            }
        }
        $team->delete();
        return Helper::apiSuccessResponse(true, 'Successfully left team', new \stdClass());
    }


    /**
     * GetTeamPlayers
     *
     *
     * @queryParam  team_id
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "players found",
     * "Result": {
     * "data": {
     * "id": 6,
     * "team_name": "Test",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "mixed",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": "23",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2020-12-14 15:02:44",
     * "updated_at": "2021-01-11 16:06:20",
     * "deleted_at": null,
     * "players": [
     * {
     * "id": 155,
     * "first_name": "M",
     * "last_name": "J",
     * "profile_picture": "",
     * "follow_status": false
     * },
     * {
     * "id": 210,
     * "first_name": "Test",
     * "last_name": "Trainer",
     * "profile_picture": "",
     * "follow_status": false
     * },
     * {
     * "id": 211,
     * "first_name": "Trainer",
     * "last_name": "Name",
     * "profile_picture": "",
     * "follow_status": false
     * },
     * {
     * "id": 212,
     * "first_name": "Trainer",
     * "last_name": "Testing",
     * "profile_picture": "",
     * "follow_status": false
     * },
     * {
     * "id": 213,
     * "first_name": "a",
     * "last_name": "v",
     * "profile_picture": "",
     * "follow_status": false
     * }
     * ],
     * "trainers": [
     * {
     * "id": 40,
     * "nationality_id": 164,
     * "first_name": "Umer",
     * "middle_name": null,
     * "last_name": "Shaikh",
     * "surname": "Shaikh",
     * "email": "umer@jogo.ai",
     * "new_temp_email": null,
     * "humanox_username": null,
     * "humanox_user_id": null,
     * "humanox_pin": null,
     * "humanox_auth_token": null,
     * "country_code_id": 152,
     * "phone": "+934342336633",
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": "media/users/OF4YBPrj3vkx7QtIztDKOAZod45t1sSsXQfc1cPE.jpeg",
     * "date_of_birth": null,
     * "age": null,
     * "badge_count": 0,
     * "verification_code": "437554",
     * "verified_at": "2021-01-06 08:50:51",
     * "active": 0,
     * "status_id": 2,
     * "who_created": 40,
     * "last_seen": "2021-07-27 07:17:34",
     * "online_status": "1",
     * "created_at": "2020-07-30 21:26:43",
     * "updated_at": "2021-07-27 07:17:34",
     * "deleted_at": null,
     * "pivot": {
     * "team_id": 6,
     * "trainer_user_id": 40,
     * "created_at": "2021-01-11 13:56:19"
     * }
     * },
     * {
     * "id": 212,
     * "nationality_id": 152,
     * "first_name": "Trainer",
     * "middle_name": "''",
     * "last_name": "Testing",
     * "surname": "",
     * "email": "testingtrainer@gmail.com",
     * "new_temp_email": null,
     * "humanox_username": null,
     * "humanox_user_id": null,
     * "humanox_pin": null,
     * "humanox_auth_token": null,
     * "country_code_id": 152,
     * "phone": null,
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": null,
     * "date_of_birth": null,
     * "age": null,
     * "badge_count": 44,
     * "verification_code": null,
     * "verified_at": "2021-01-11 16:32:17",
     * "active": 0,
     * "status_id": 2,
     * "who_created": 40,
     * "last_seen": null,
     * "online_status": "0",
     * "created_at": "2021-01-11 16:32:17",
     * "updated_at": "2021-06-17 08:59:23",
     * "deleted_at": null,
     * "pivot": {
     * "team_id": 6,
     * "trainer_user_id": 212,
     * "created_at": null
     * }
     * },
     * {
     * "id": 214,
     * "nationality_id": 152,
     * "first_name": "First",
     * "middle_name": "''",
     * "last_name": "Last",
     * "surname": "",
     * "email": "qwerty@a.com",
     * "new_temp_email": null,
     * "humanox_username": null,
     * "humanox_user_id": null,
     * "humanox_pin": null,
     * "humanox_auth_token": null,
     * "country_code_id": 152,
     * "phone": null,
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": null,
     * "date_of_birth": null,
     * "age": null,
     * "badge_count": 0,
     * "verification_code": null,
     * "verified_at": "2021-01-11 17:26:28",
     * "active": 0,
     * "status_id": 2,
     * "who_created": 40,
     * "last_seen": null,
     * "online_status": "0",
     * "created_at": "2021-01-11 17:26:28",
     * "updated_at": "2021-01-11 17:26:28",
     * "deleted_at": null,
     * "pivot": {
     * "team_id": 6,
     * "trainer_user_id": 214,
     * "created_at": "2021-01-11 17:26:28"
     * }
     * },
     * {
     * "id": 215,
     * "nationality_id": 152,
     * "first_name": "ABC",
     * "middle_name": "''",
     * "last_name": "DEF",
     * "surname": "",
     * "email": "1@a.com",
     * "new_temp_email": null,
     * "humanox_username": null,
     * "humanox_user_id": null,
     * "humanox_pin": null,
     * "humanox_auth_token": null,
     * "country_code_id": 152,
     * "phone": null,
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": null,
     * "date_of_birth": null,
     * "age": null,
     * "badge_count": 0,
     * "verification_code": null,
     * "verified_at": "2021-01-11 17:41:25",
     * "active": 0,
     * "status_id": 2,
     * "who_created": 40,
     * "last_seen": null,
     * "online_status": "0",
     * "created_at": "2021-01-11 17:41:25",
     * "updated_at": "2021-01-11 17:41:25",
     * "deleted_at": null,
     * "pivot": {
     * "team_id": 6,
     * "trainer_user_id": 215,
     * "created_at": "2021-01-11 17:41:25"
     * }
     * },
     * {
     * "id": 382,
     * "nationality_id": 152,
     * "first_name": "Saad",
     * "middle_name": "''",
     * "last_name": "Saleem",
     * "surname": "",
     * "email": "ssaad.sm@gmail.com",
     * "new_temp_email": null,
     * "humanox_username": null,
     * "humanox_user_id": null,
     * "humanox_pin": null,
     * "humanox_auth_token": null,
     * "country_code_id": 152,
     * "phone": null,
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": null,
     * "date_of_birth": null,
     * "age": null,
     * "badge_count": 0,
     * "verification_code": null,
     * "verified_at": "2021-03-16 12:49:04",
     * "active": 0,
     * "status_id": 2,
     * "who_created": 40,
     * "last_seen": null,
     * "online_status": "0",
     * "created_at": "2021-03-16 12:49:04",
     * "updated_at": "2021-03-16 12:49:04",
     * "deleted_at": null,
     * "pivot": {
     * "team_id": 6,
     * "trainer_user_id": 382,
     * "created_at": "2021-03-16 12:49:04"
     * }
     * },
     * {
     * "id": 383,
     * "nationality_id": 152,
     * "first_name": "Waleed",
     * "middle_name": "''",
     * "last_name": "waqar",
     * "surname": "",
     * "email": "ta.waleed1@gmail.com",
     * "new_temp_email": null,
     * "humanox_username": null,
     * "humanox_user_id": null,
     * "humanox_pin": null,
     * "humanox_auth_token": null,
     * "country_code_id": 152,
     * "phone": null,
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": null,
     * "date_of_birth": null,
     * "age": null,
     * "badge_count": 0,
     * "verification_code": null,
     * "verified_at": "2021-03-16 12:49:51",
     * "active": 0,
     * "status_id": 2,
     * "who_created": 40,
     * "last_seen": null,
     * "online_status": "0",
     * "created_at": "2021-03-16 12:49:51",
     * "updated_at": "2021-03-16 12:49:51",
     * "deleted_at": null,
     * "pivot": {
     * "team_id": 6,
     * "trainer_user_id": 383,
     * "created_at": "2021-03-16 12:49:51"
     * }
     * },
     * {
     * "id": 447,
     * "nationality_id": 164,
     * "first_name": "Shahzaib",
     * "middle_name": null,
     * "last_name": "Trainer",
     * "surname": null,
     * "email": "shahzaib.imran@jogo.ai",
     * "new_temp_email": null,
     * "humanox_username": null,
     * "humanox_user_id": null,
     * "humanox_pin": null,
     * "humanox_auth_token": null,
     * "country_code_id": 152,
     * "phone": "+923482302450",
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": null,
     * "date_of_birth": null,
     * "age": null,
     * "badge_count": 14,
     * "verification_code": null,
     * "verified_at": "2021-04-09 13:27:31",
     * "active": 0,
     * "status_id": 2,
     * "who_created": null,
     * "last_seen": "2021-06-21 06:49:47",
     * "online_status": "1",
     * "created_at": "2021-04-09 13:24:46",
     * "updated_at": "2021-06-21 06:49:47",
     * "deleted_at": null,
     * "pivot": {
     * "team_id": 6,
     * "trainer_user_id": 447,
     * "created_at": null
     * }
     * }
     * ]
     * },
     * "meta": {
     * "current_page": 1,
     * "next_page": 2
     * }
     * }
     * }
     *
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "team_id": [
     *                "The team_id field is required."
     *            ]
     *        }
     *    ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     * @queryParam limit required integer
     * @queryParam page required integer for page number
     */
    public function getTeamPlayers(Request $request)
    {
//        players:first_name,last_name,middle_name,profile_picture,users.id
        $request->validate([
            "limit" => ["required", "min:1", "integer"]
        ]);
//        $team =Team::has('players')->with("trainers")
//            ->with('players')
//            ->find($request->team_id);

        $team = Team::has("players")->with("trainers")->find($request->team_id);
        if (!$team) {
            return Helper::apiUnAuthenticatedResponse(false, 'Players not found', new stdClass());
        }

        $data = Helper::getTeamPlayers($team,$request,true);

        return Helper::apiSuccessResponse(true, 'players found', $data);
    }

    /**
     * Search Teams
     * @queryParam keyword string optional
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Teams found",
     * "Result": [
     * {
     * "id": 2,
     * "team_name": "Ajax U16",
     * "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     * "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
     * "age_group": "211",
     * "created_at": "2020-07-17 16:17:06",
     * "updated_at": "2020-07-17 16:17:06",
     * "deleted_at": null
     * }
     * ]
     * }
     *
     *
     */


    public function searchTeams(Request $request)
    {
        $teams = [];
        $player_teams = User::with('teams')->find(auth()->user()->id);
        if (isset($player_teams->teams)) {
            $teams = $player_teams->teams->pluck('id')->toArray();
        }
        $team_requests = DB::table('player_team_requests')->select('team_id')->where('player_user_id', auth()->user()->id)->get()->pluck('team_id')->toArray();
        // get teams where user has sent requests
        if (count($team_requests)) {
            foreach ($team_requests as $req) {
                $teams[] = $req;
            }
        }
        $get_teams = Team::where('team_name', 'LIKE', '%' . $request->keyword . '%')->whereNotIn('id', $teams)->get();
        if (count($get_teams) == 0) {
            return Helper::apiErrorResponse(false, 'No teams found', []);
        }
        return Helper::apiSuccessResponse(true, 'Teams found', $get_teams);
    }

    /**
     * Get All Teams
     * @queryParam clubId required integer
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Successfully getting teams",
     * "Result": [
     * {
     * "id": 23,
     * "team_name": "11 m",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": "11",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-01-08 13:29:56",
     * "updated_at": "2021-07-02 13:54:48",
     * "deleted_at": null
     * },
     * {
     * "id": 25,
     * "team_name": "a",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "11",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-01-11 13:51:09",
     * "updated_at": "2021-01-11 13:51:09",
     * "deleted_at": null
     * },
     * {
     * "id": 92,
     * "team_name": "ABC Tamaa",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 9,
     * "max_age_group": 14,
     * "created_at": "2021-06-30 14:28:05",
     * "updated_at": "2021-06-30 14:28:05",
     * "deleted_at": null
     * },
     * {
     * "id": 116,
     * "team_name": "Accc",
     * "privacy": "open_to_invites",
     * "image": null,
     * "gender": "",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 16,
     * "max_age_group": 9,
     * "created_at": "2021-07-09 15:02:32",
     * "updated_at": "2021-07-09 15:02:32",
     * "deleted_at": null
     * },
     * {
     * "id": 7,
     * "team_name": "Argentina ABC",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "U16",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2020-12-14 15:03:34",
     * "updated_at": "2021-07-02 13:53:07",
     * "deleted_at": null
     * },
     * {
     * "id": 119,
     * "team_name": "asasassa",
     * "privacy": "open_to_invites",
     * "image": null,
     * "gender": "",
     * "team_type": "outdoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 10,
     * "max_age_group": 13,
     * "created_at": "2021-07-09 15:07:17",
     * "updated_at": "2021-07-09 15:07:17",
     * "deleted_at": null
     * },
     * {
     * "id": 117,
     * "team_name": "Bcccc",
     * "privacy": "open_to_invites",
     * "image": null,
     * "gender": "",
     * "team_type": "outdoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 10,
     * "max_age_group": 17,
     * "created_at": "2021-07-09 15:03:18",
     * "updated_at": "2021-07-09 15:03:18",
     * "deleted_at": null
     * },
     * {
     * "id": 118,
     * "team_name": "Ccccc",
     * "privacy": "open_to_invites",
     * "image": null,
     * "gender": "woman",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 17,
     * "max_age_group": 8,
     * "created_at": "2021-07-09 15:03:52",
     * "updated_at": "2021-07-09 15:03:52",
     * "deleted_at": null
     * },
     * {
     * "id": 88,
     * "team_name": "Closed To V3",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 9,
     * "max_age_group": 14,
     * "created_at": "2021-06-30 14:23:08",
     * "updated_at": "2021-06-30 14:23:08",
     * "deleted_at": null
     * },
     * {
     * "id": 5,
     * "team_name": "consequatur",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": "tempore",
     * "age_group": null,
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": null,
     * "updated_at": "2021-01-11 13:37:22",
     * "deleted_at": null
     * },
     * {
     * "id": 90,
     * "team_name": "Creating new Team",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 9,
     * "max_age_group": 12,
     * "created_at": "2021-06-30 14:26:13",
     * "updated_at": "2021-06-30 14:26:13",
     * "deleted_at": null
     * },
     * {
     * "id": 89,
     * "team_name": "Creeed",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 10,
     * "max_age_group": 15,
     * "created_at": "2021-06-30 14:24:30",
     * "updated_at": "2021-06-30 14:24:30",
     * "deleted_at": null
     * },
     * {
     * "id": 98,
     * "team_name": "d s",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "woman",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 8,
     * "max_age_group": 13,
     * "created_at": "2021-07-01 15:41:51",
     * "updated_at": "2021-07-02 12:23:45",
     * "deleted_at": null
     * },
     * {
     * "id": 97,
     * "team_name": "Hasnain Team 123 556",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 10,
     * "max_age_group": 15,
     * "created_at": "2021-07-01 15:31:49",
     * "updated_at": "2021-07-02 12:42:53",
     * "deleted_at": null
     * },
     * {
     * "id": 93,
     * "team_name": "Hasnain Team A",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "outdoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 5,
     * "max_age_group": 8,
     * "created_at": "2021-07-01 10:02:51",
     * "updated_at": "2021-07-02 13:51:36",
     * "deleted_at": null
     * },
     * {
     * "id": 21,
     * "team_name": "Indoor team",
     * "privacy": "open_to_invites",
     * "image": "media/teams/JHc2WXWYa8Jq3QYVhgcUHqMhFsAarUQiVnZy9Fjr.jpg",
     * "gender": "woman",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": "10",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-01-08 13:14:21",
     * "updated_at": "2021-07-08 11:48:28",
     * "deleted_at": null
     * },
     * {
     * "id": 96,
     * "team_name": "jj 1",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 10,
     * "max_age_group": 11,
     * "created_at": "2021-07-01 15:31:39",
     * "updated_at": "2021-07-02 12:45:21",
     * "deleted_at": null
     * },
     * {
     * "id": 102,
     * "team_name": "koko",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "woman",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 9,
     * "max_age_group": 14,
     * "created_at": "2021-07-02 12:57:25",
     * "updated_at": "2021-07-09 14:56:41",
     * "deleted_at": null
     * },
     * {
     * "id": 115,
     * "team_name": "Koko Closed",
     * "privacy": "closed_to_invites",
     * "image": null,
     * "gender": "",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 10,
     * "max_age_group": 15,
     * "created_at": "2021-07-09 14:58:51",
     * "updated_at": "2021-07-09 14:58:51",
     * "deleted_at": null
     * },
     * {
     * "id": 100,
     * "team_name": "MJ 123",
     * "privacy": "open_to_invites",
     * "image": "media/teams/K2XtFf8oXF6i9sgEVNQ84AHo2OIlipstm7wIe0TX.jpg",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 9,
     * "max_age_group": 11,
     * "created_at": "2021-07-01 16:13:24",
     * "updated_at": "2021-07-16 12:16:02",
     * "deleted_at": null
     * },
     * {
     * "id": 101,
     * "team_name": "mj 22",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "woman",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 10,
     * "max_age_group": 16,
     * "created_at": "2021-07-02 11:51:57",
     * "updated_at": "2021-07-02 12:23:14",
     * "deleted_at": null
     * },
     * {
     * "id": 80,
     * "team_name": "my team",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "woman",
     * "team_type": "outdoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 12,
     * "max_age_group": 15,
     * "created_at": "2021-06-22 12:49:23",
     * "updated_at": "2021-07-02 14:27:58",
     * "deleted_at": null
     * },
     * {
     * "id": 113,
     * "team_name": "new temmm updt",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 5,
     * "max_age_group": 9,
     * "created_at": "2021-07-05 17:40:33",
     * "updated_at": "2021-07-05 17:41:01",
     * "deleted_at": null
     * },
     * {
     * "id": 78,
     * "team_name": "newTeams 123",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "woman",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 17,
     * "max_age_group": 19,
     * "created_at": "2021-06-22 10:36:55",
     * "updated_at": "2021-07-02 14:37:21",
     * "deleted_at": null
     * },
     * {
     * "id": 22,
     * "team_name": "Outdoor team",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "mixed",
     * "team_type": "outdoor",
     * "description": null,
     * "age_group": "10",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-01-08 13:15:06",
     * "updated_at": "2021-01-08 13:15:06",
     * "deleted_at": null
     * },
     * {
     * "id": 66,
     * "team_name": "s",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "10",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-04-19 11:27:01",
     * "updated_at": "2021-04-19 11:27:01",
     * "deleted_at": null
     * },
     * {
     * "id": 94,
     * "team_name": "sa la",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 9,
     * "max_age_group": 10,
     * "created_at": "2021-07-01 15:12:42",
     * "updated_at": "2021-07-02 12:58:29",
     * "deleted_at": null
     * },
     * {
     * "id": 99,
     * "team_name": "salam",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "woman",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 10,
     * "max_age_group": 11,
     * "created_at": "2021-07-01 16:12:29",
     * "updated_at": "2021-07-01 16:12:29",
     * "deleted_at": null
     * },
     * {
     * "id": 81,
     * "team_name": "sdfs",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 6,
     * "max_age_group": 11,
     * "created_at": "2021-06-22 12:54:54",
     * "updated_at": "2021-07-02 14:27:38",
     * "deleted_at": null
     * },
     * {
     * "id": 31,
     * "team_name": "T1",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "11",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-01-12 14:07:25",
     * "updated_at": "2021-01-12 14:07:25",
     * "deleted_at": null
     * },
     * {
     * "id": 33,
     * "team_name": "T2",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": "10",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-01-14 10:34:37",
     * "updated_at": "2021-01-14 10:34:37",
     * "deleted_at": null
     * },
     * {
     * "id": 34,
     * "team_name": "T2",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": "10",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-01-14 13:00:32",
     * "updated_at": "2021-01-14 13:00:32",
     * "deleted_at": null
     * },
     * {
     * "id": 55,
     * "team_name": "Team",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "11",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-03-16 09:01:01",
     * "updated_at": "2021-03-16 09:01:01",
     * "deleted_at": null
     * },
     * {
     * "id": 114,
     * "team_name": "TEAM TEST",
     * "privacy": "open_to_invites",
     * "image": "media/teams/8sqa093DEQtAiNnhj3AgaPctXJofMXkpEyP7EHjD.png",
     * "gender": "man",
     * "team_type": "outdoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 10,
     * "max_age_group": 16,
     * "created_at": "2021-07-07 12:59:55",
     * "updated_at": "2021-07-09 13:20:30",
     * "deleted_at": null
     * },
     * {
     * "id": 87,
     * "team_name": "Team V3",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 11,
     * "max_age_group": 17,
     * "created_at": "2021-06-30 14:15:26",
     * "updated_at": "2021-07-02 14:26:55",
     * "deleted_at": null
     * },
     * {
     * "id": 69,
     * "team_name": "team3",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 9,
     * "max_age_group": 12,
     * "created_at": "2021-04-21 11:51:09",
     * "updated_at": "2021-04-21 11:51:09",
     * "deleted_at": null
     * },
     * {
     * "id": 79,
     * "team_name": "teamchat",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 10,
     * "max_age_group": 13,
     * "created_at": "2021-06-22 10:38:02",
     * "updated_at": "2021-07-02 14:28:08",
     * "deleted_at": null
     * },
     * {
     * "id": 112,
     * "team_name": "teammmm testtt2updatedd Ali",
     * "privacy": "open_to_invites",
     * "image": "media/teams/0F1vUqFfp1jtwQlu7cUyNEdh3XBLeDYlVDJD3WlM.png",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 12,
     * "max_age_group": 14,
     * "created_at": "2021-07-05 17:30:07",
     * "updated_at": "2021-07-06 14:40:56",
     * "deleted_at": null
     * },
     * {
     * "id": 16,
     * "team_name": "teamname OO",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "22",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2020-12-21 15:03:41",
     * "updated_at": "2021-07-02 13:50:49",
     * "deleted_at": null
     * },
     * {
     * "id": 82,
     * "team_name": "the",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "woman",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 11,
     * "max_age_group": 14,
     * "created_at": "2021-06-22 12:56:03",
     * "updated_at": "2021-07-02 14:27:30",
     * "deleted_at": null
     * },
     * {
     * "id": 76,
     * "team_name": "wew",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 11,
     * "max_age_group": 12,
     * "created_at": "2021-05-05 10:15:22",
     * "updated_at": "2021-05-05 10:15:22",
     * "deleted_at": null
     * },
     * {
     * "id": 91,
     * "team_name": "Xyz",
     * "privacy": "closed_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 9,
     * "max_age_group": 14,
     * "created_at": "2021-06-30 14:27:41",
     * "updated_at": "2021-07-02 14:24:10",
     * "deleted_at": null
     * },
     * {
     * "id": 51,
     * "team_name": "yyyy",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "11",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-03-04 17:48:30",
     * "updated_at": "2021-03-04 17:48:30",
     * "deleted_at": null
     * },
     * {
     * "id": 52,
     * "team_name": "yyyyp",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "11",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-03-04 17:49:48",
     * "updated_at": "2021-03-04 17:49:48",
     * "deleted_at": null
     * }
     * ]
     * }
     *
     *
     */

    public function getAllTeams(Request $request)
    {
        $request->validate([
            "clubId" => ["required", "integer"]
        ]);

        $club_id = $request->clubId;

        $teams = Team::whereHas('clubs', function ($q) use ($club_id) {
            $q->where('club_id', $club_id);
        })->orderBy('team_name', 'asc')->get();

        if ($teams->isNotEmpty()) {
            return Helper::apiSuccessResponse(true, 'Successfully getting teams', $teams);
        } else {
            return Helper::apiErrorResponse(false, 'No teams were found', new stdClass());
        }

    }

    /**
     * GetUserNotifications
     * @queryParam comments optional 1
     * @queryParam follows optional 1
     * @queryParam likes optional 1
     * @queryParam requests optional 1
     * @queryParam assignments optional 1
     *
     * Containing user notifications for current user
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Notifications found",
     * "Result": [
     * {
     * "id": 2,
     * "profile_picture": "media/users/5f20343a56be11595946042.jpeg",
     * "description": "muhammad shahzaib liked your post",
     * "click_action": "VideoAndComments",
     * "model_type": "posts/like",
     * "model_type_id": 1,
     * "role": "player",
     * "status": "read",
     * "created_at": "3 hours ago"
     * }
     * ]
     * }
     *
     *
     */

    public function get_user_notifications(Request $request)
    {
        $notifications = UserNotification::where('to_user_id', '=', Auth::user()->id)
            ->where("description", "!=", "{\"en\":null,\"nl\":null}")
            ->with('status')
            ->with('from_user.roles');
        $types = [];
        if ($request->comments == 1) {
            $types[] = 'posts/comment';
        }
        if ($request->likes == 1) {
            $types[] = 'posts/like';
        }
        if ($request->assignments == 1) {
            $types[] = 'assignment/assigned';
        }
        if ($request->follows == 1) {
            $types[] = 'user/follow';
        }
        if ($request->requests == 1) {
            $types[] = 'request/team';
        }
        if (count($types)) {
            $notifications = $notifications->whereIn('model_type', $types);
        }
        $notifications = $notifications->latest()->get();
        if (count($notifications) == 0) {
            return Helper::apiNotFoundResponse(false, 'Notifications not found', []);
        }

        $_notifications = $notifications->map(function ($item) {
            return Helper::getUserNotificationObject($item);
        });

        return Helper::apiSuccessResponse(true, 'Notifications found', $_notifications);
    }


    /**
     * UpdateUserNotification
     *
     * @queryParam notification_id required notification id is required
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Notification has been updated",
     * "Result": {}
     * }
     *
     */
    public function update_user_notification(Request $request)
    {
        Validator::make($request->all(), [
            'notification_id' => 'required'
        ])->validate();

        $notification = UserNotification::where('to_user_id', Auth::user()->id)
            ->where('id', $request->notification_id)->first();

        if (!$notification) {
            return Helper::apiNotFoundResponse(true, 'Notification not found', new stdClass());
        }

        $status = Status::where('name', 'read')->first();

        $notification->status_id = $status->id ?? 0;
        $notification->save();

        return Helper::apiSuccessResponse(true, 'Notification has been updated', new stdClass());

    }

    /**
     * Delete Notification
     *
     * @bodyParam notification_id required notification id is required
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Notification has been deleted",
     * "Result": {}
     * }
     *
     */
    public function deleteNotification(Request $request)
    {
        Validator::make($request->all(), ['notification_id' => 'required'])->validate();

        $is_deleted = UserNotification::whereId($request->notification_id)->whereToUserId(Auth::user()->id)->delete();

        if (!$is_deleted) {
            return Helper::apiNotFoundResponse(true, 'Notification not found', new stdClass());
        }

        return Helper::apiSuccessResponse(true, 'Notification has been deleted', new stdClass());
    }

    /**
     * CreateUserNotification
     *
     * create user notification status_id will be by default 2 which  means not seen by a user
     *
     * @bodyParam user_id required user id is required
     * @bodyParam name required title/name of notification is required
     * @bodyParam description required description of notification is required
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Record has been saved",
     *     "Result": {
     *         "user_id": 1,
     *         "name": "JJJ",
     *         "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
     *         "image": "",
     *         "status_id": 2,
     *         "updated_at": "2020-07-15 23:36:34",
     *         "created_at": "2020-07-15 23:36:34",
     *         "id": 5
     *     }
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "user_id": [
     *                 "The user id field is required."
     *             ],
     *             "name": [
     *                 "The name field is required."
     *             ],
     *             "description": [
     *                 "The description field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "user_id": [
     *                 "The user id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "user_id": [
     *                 "The selected user id is invalid."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "name": [
     *                 "The name field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "description": [
     *                 "The description field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     * }
     *
     */
    public function create_user_notification(Request $request)
    {

        $validator = Validator::make($request->all(), UserNotification::$create_user_notification_rules);
        if ($validator->fails()) {
            $error_result[] = $validator->errors();
            return Helper::apiUnAuthenticatedResponse(false, 'apiInvalidParamResponse', $error_result);
        }

        $user_id = $request->user_id;
        $current_auth_id = Auth::user()->id;
        $request->request->add(
            [
                'status_id' => 2
            ]

        );

        $user_notification = new UserNotification();
        $response = $user_notification->store($request);
        return Helper::apiSuccessResponse(true, 'Record has been saved', $response);

    }

    /**
     * Get User Privacy Settings
     *
     * You can get user privacy settings
     *
     * @queryParam  user_id required user id is required to get the player profile
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Successfully getting user settings and access modifiers ",
     * "Result": {
     * "user_privacy_settings": [
     * {
     * "id": 1,
     * "name": "public",
     * "display_name": "Public",
     * "description": "Anyone on JOGO can see your profile, training videos and progress. Follow requests are accepted automatically.",
     * "created_at": "2020-07-17 22:20:52",
     * "updated_at": "2020-07-17 22:20:52",
     * "deleted_at": null,
     * "user_privacy_setting_id": 4,
     * "pivot": {
     * "user_id": 1,
     * "access_modifier_id": 1,
     * "created_at": null
     * }
     * }
     * ],
     * "access_modifiers": [
     * {
     * "id": 1,
     * "name": "public",
     * "display_name": "Public",
     * "description": "Anyone on JOGO can see your profile, training videos and progress. Follow requests are accepted automatically.",
     * "created_at": "2020-07-17 22:20:52",
     * "updated_at": "2020-07-17 22:20:52",
     * "deleted_at": null
     * },
     * {
     * "id": 2,
     * "name": "private",
     * "display_name": "Private",
     * "description": "Only you can see your profile, training videos and progress. Follow requests need to be approved by you.",
     * "created_at": "2020-07-17 22:21:01",
     * "updated_at": "2020-07-17 22:21:01",
     * "deleted_at": null
     * },
     * {
     * "id": 3,
     * "name": "follower",
     * "display_name": "Followers",
     * "description": "Followers can see your profile, training videos and progress. Follow requests need to be approved by you.",
     * "created_at": "2020-07-17 22:21:10",
     * "updated_at": "2020-07-17 22:21:10",
     * "deleted_at": null
     * }
     * ]
     * }
     * }
     *
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "id": [
     *                "The selected user id is invalid."
     *            ]
     *        }
     *    ]
     * }
     *
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "id": [
     *                "The user id field is required."
     *            ]
     *        }
     *    ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "User id is not matched with auth user id",
     *     "Result": {}
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     */

    public function get_user_privacy_settings(Request $request)
    {
        $validator = Validator::make($request->all(), User::$get_player_privacy_settings_rules);
        if ($validator->fails()) {
            $error_result[] = $validator->errors();
            return Helper::apiUnAuthenticatedResponse(false, 'apiInvalidParamResponse', $error_result);
        }

        $user_id = $request->user_id;
        $auth_user_id = Auth::user()->id;

        if ($user_id != $auth_user_id) {
            $message = 'User id is not matched with auth user id';
            return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
        }

        $user_privacy_settings = User::with('user_privacy_settings')->find($user_id)->user_privacy_settings;
        $user_privacy_settings_count = count($user_privacy_settings);

        if ($user_privacy_settings_count > 0) {
            /**
             * Getting public,private,follower
             */

            $user_settings['user_privacy_settings'] = $user_privacy_settings;
            $access_modifiers = AccessModifier::all();
            $user_settings['access_modifiers'] = $access_modifiers;

            return Helper::apiSuccessResponse(true, 'Successfully getting user settings and access modifiers ', $user_settings);

        } else {

            return Helper::apiSuccessResponse(true, 'User has not set any privacy settings yet ', new stdClass());
        }


    }

    /**
     * Update User Privacy Setting
     *
     * updating user privacy setting, we need user_privacy_setting id from user_privacy_settings table, access_modifier id from access_modifiers table and user_id which must be matched to current auth user id
     *
     * @queryParam  user_id required user id is required
     * @queryParam  user_privacy_setting_id required user privacy setting id is required
     * @queryParam  access_modifier_id required access modifier id is required
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Record has been saved",
     *     "Result": {
     *         "id": 1,
     *         "user_id": 1,
     *         "access_modifier_id": 2,
     *         "created_at": "2020-07-16 05:05:24",
     *         "updated_at": "2020-07-16 01:42:20",
     *         "deleted_at": null
     *     }
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "user_id": [
     *                 "The user id field is required."
     *             ],
     *             "user_privacy_setting_id": [
     *                 "The user privacy setting id field is required."
     *             ],
     *             "access_modifier_id": [
     *                 "The access modifier id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "user_id": [
     *                 "The user id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "user_privacy_setting_id": [
     *                 "The user privacy setting id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "access_modifier_id": [
     *                 "The access modifier id field is required."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "user_id": [
     *                 "The selected user id is invalid."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "user_privacy_setting_id": [
     *                 "The selected user privacy setting id is invalid."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "apiInvalidParamResponse",
     *     "Result": [
     *         {
     *             "access_modifier_id": [
     *                 "The selected access modifier id is invalid."
     *             ]
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "User id is not matched with auth user id",
     *     "Result": {}
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     * }
     *
     */
    public function update_user_privacy_setting(Request $request)
    {

        $validator = Validator::make($request->all(), UserPrivacySetting::$update_user_privacy_seting_rules);
        if ($validator->fails()) {
            $error_result[] = $validator->errors();
            return Helper::apiUnAuthenticatedResponse(false, 'apiInvalidParamResponse', $error_result);
        }

        $user_id = $request->user_id;
        $user_privacy_setting_id = $request->user_privacy_setting_id;
        $access_modifier_id = $request->access_modifier_id;


        $current_auth_id = Auth::user()->id;
        //return 'Current Auth Id : '.$current_auth_id;

        if ($user_id != $current_auth_id) {
            $message = 'User id is not matched with auth user id';
            return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
        }


        //$user_privacy_setting = UserPrivacySetting::where('user_id', $current_auth_id)->where('id', $user_privacy_setting_id)->get();
        $user_privacy_setting = UserPrivacySetting::where('user_id', $current_auth_id)->where('id', $user_privacy_setting_id)->first();

        if (!$user_privacy_setting) {

            $user_privacy_setting = new UserPrivacySetting();
            //$response = $user_privacy_setting->store($request);
            //return Helper::apiSuccessResponse(true, 'Record has been saved', $response);
            $message = "Can't update! user_privacy_setting_id is wrong.";
            return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
        }

        /*return $user_privacy_setting[0]['id'];
        return 'found';*/

        $request->request->add(
            [
                //'id' => $user_privacy_setting_id,
                'user_id' => Auth::user()->id,
                'access_modifier_id' => $access_modifier_id
            ]

        );


        $response = $user_privacy_setting->store_update($request);
        return Helper::apiSuccessResponse(true, 'Record has been saved', $response);

    }


    /**
     * getPlayerSprintSpeed
     *
     * @queryParam  player_id required player id is required
     * @queryParam  filter required string recent/daily/weekly/monthly/yearly
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found",
     * "Result": {
     * "labels": [
     * 10,
     * 20,
     * 30,
     * 40
     * ],
     * "data_1": [
     * 34,
     * 22,
     * 278,
     * 98
     * ]
     * }
     * }
     *
     *
     */
    public function getPlayerSprintSpeed(Request $request)
    {
        $this->validate($request, [
            'player_id' => 'required',
            'filter' => 'required|in:recent,monthly,yearly,weekly,daily'
        ]);
        $match_stats = MatchDetails::select(
            DB::raw('AVG(speed) AS speed, date(event_ts), hour(event_ts), floor(minute(event_ts) / 10) AS minutes'))
            ->groupBy('minutes')
            ->where('user_id', $request->player_id);
//        $match_stats = MatchStat::select(
//            DB::raw('SUM(stat_value) AS speed, ROUND(UNIX_TIMESTAMP(created_at)/(10 * 60)) AS minutes'))
//            ->where('player_id',$request->player_id)
//            ->where('stat_type_id',6);
        if ($request->filter === 'latest') {
            $last_match_id = MatchDetails::where('user_id', $request->player_id)->orderBy('id', 'DESC')->first();
            $match_stats = $match_stats->where('user_id', $last_match_id->match_id)->groupBy(DB::raw('minutes'));
        } elseif ($request->filter === 'daily') {
            $match_stats = $match_stats->whereDate('event_ts', Carbon::today())->groupBy(DB::raw('minutes'));
        } elseif ($request->filter === 'weekly') {
            $match_stats = $match_stats->whereRaw(DB::raw('WEEK(event_ts) = WEEK(NOW())'))->groupBy(DB::raw('minutes'));
        } elseif ($request->filter === 'monthly') {
            $match_stats = $match_stats->whereRaw(DB::raw('MONTH(event_ts) = MONTH(NOW())'))->groupBy(DB::raw('minutes'));
        } elseif ($request->filter === 'yearly') {
            $match_stats = $match_stats->whereRaw(DB::raw('YEAR(event_ts) = YEAR(NOW())'))->groupBy(DB::raw('minutes'));
        }
        $match_stats = $match_stats->orderBy('event_ts', 'ASC')
            ->get();
        $data = $match_stats->pluck('speed');
        if (count($data) <= 0) {
            return Helper::apiErrorResponse(false, 'No Records found', new stdClass());
        }
        $min = 0;
        $labels = [];
        foreach ($match_stats as $match_stat) {
            $labels[] = $min += 10;
        }
        $response = ['labels' => $labels, 'data_1' => $data];
        return Helper::apiSuccessResponse(true, 'Records found', $response);
    }


    /**
     * reviewTrainer
     *
     * @bodyParam  trainer_id required trainer id is required
     * @bodyParam  rating required rating  is required
     * @bodyParam  review optional
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Trainer reviewed",
     * "Result": {}
     * }
     *
     *
     */


    public function rateTrainer(Request $request)
    {
        $this->validate($request, [
            'trainer_id' => 'required',
            'rating' => 'required|lte:5'
        ]);
        $user_teams_trainer = auth()->user()->teams()->whereHas('trainers', function ($t) use ($request) {
            $t->where('trainer_user_id', $request->trainer_id);
        })->get();
        if ($user_teams_trainer->count()) {
            $review_exist = Review::where('reviewer_id', auth()->user()->id)->where('reviewed_id', $request->trainer_id)->first();
            if ($review_exist) {
                return Helper::apiErrorResponse(false, 'Trainer already reviewed', new \stdClass());
            }
            $review = new Review();
            $review->reviewer_id = auth()->user()->id;
            $review->reviewed_id = $request->trainer_id;
            $review->rating = $request->rating;
            $review->review = $request->review;
            if ($review->save()) {
                return Helper::apiSuccessResponse(true, 'Trainer reviewed', new \stdClass());
            }
        }
        return Helper::apiErrorResponse(false, 'Invalid trainer', new \stdClass());

    }

    /**
     * getSessionDetails
     *
     * @queryParam  session_id required session id is required
     * @response {
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "success",
     * "Result": {
     * "session_details": {
     * "date": "11.26.2019",
     * "sensor": "JOGO",
     * "name": "RTV AIRTIFICIAL 1",
     * "session_time": "1h 1m"
     * },
     * "top_records": [],
     * "movement_details": [
     * {
     * "name": "Total Distance",
     * "image": "media/stats_icons/total_distance.png",
     * "badge": "",
     * "value": "0.268 m",
     * "key": "distance"
     * },
     * {
     * "name": "Total Ball Kicks",
     * "image": "",
     * "badge": "",
     * "value": 70,
     * "key": "ball_kicks"
     * },
     * {
     * "name": "Max Shot Speed",
     * "image": "",
     * "badge": "media/stats_icons/badge.png",
     * "value": "100Km/h",
     * "key": "max_shot_speed"
     * },
     * {
     * "name": "Total Sprints",
     * "image": "",
     * "badge": "",
     * "value": 0,
     * "key": "sprints"
     * },
     * {
     * "name": "Impacts",
     * "image": "media/stats_icons/received_impact.png",
     * "badge": "",
     * "value": 2,
     * "key": "impacts"
     * },
     * {
     * "name": "Max Speed",
     * "image": "",
     * "badge": "media/stats_icons/badge.png",
     * "value": "18 Km/h",
     * "key": "max_speed"
     * }
     * ],
     * "tempo_records": [
     * {
     * "name": "Low Tempo",
     * "image": "media/stats_icons/walking_speed.png",
     * "percentage": "37 %",
     * "time": "22.6 min",
     * "key": "low_tempo"
     * },
     * {
     * "name": "Mid Tempo",
     * "image": "media/stats_icons/running_speed.png",
     * "percentage": "50 %",
     * "time": "30.5 min",
     * "key": "mid_tempo"
     * },
     * {
     * "name": "High Tempo",
     * "image": "media/stats_icons/speed_sprinting.png",
     * "percentage": "13 %",
     * "time": "7.9 min",
     * "key": "high_tempo"
     * }
     * ],
     * "total_touches": 10,
     * "ball_touches": [
     * {
     * "name": "Shots",
     * "value": 7,
     * "percentage": "70 %"
     * },
     * {
     * "name": "Passes",
     * "value": 3,
     * "percentage": "30 %"
     * }
     * ],
     * "leg_distribution": {
     * "left_foot": 1,
     * "left_foot_percentage": "9.09%",
     * "right_foot": 10,
     * "right_foot_percentage": "90.91%"
     * }
     * }
     * }
     * }
     *
     *
     */

    public function getSessionDetails(Request $request)
    {
        $match_id = $request->session_id;
        $match = Match::find($match_id);
        if (!$match || !$match->user_id) {
            return Helper::apiErrorResponse(false, 'match not found', new \stdClass());
        }
        $session_details = [
            'date' => Carbon::parse($match->init_ts)->format('m.d.Y'),
            'sensor' => 'JOGO',
            'name' => $match->name,
            'session_time' => Carbon::parse($match->init_ts)->diff(Carbon::parse($match->end_ts))->format('%hh %im'),
        ];

        $previous_records = DB::query()->selectRaw("max_shot_speed,total_ball_touches,max_speed")
            ->fromSub(function ($query) use ($match) {
                $query->selectRaw("
                COUNT(CASE WHEN  event_type IN ('BK','FK') THEN 1 END) AS total_ball_touches,
                MAX(CASE WHEN  event_type IN ('BK') THEN event_magnitude END) AS max_shot_speed,
                MAX(speed) AS max_speed
                FROM match_details
                WHERE user_id = ({$match->user_id}) AND event_id!=({$match->id})
                GROUP BY event_id
                ");
            }, "f")
            ->first();

        $match_records = DB::query()->selectRaw('low_tempo , mid_tempo, high_tempo, total, start_time, end_time,
            ROUND(low_tempo/total*100) AS low_percentege,
            ROUND(mid_tempo/total*100) AS mid_percentege,
            ROUND(high_tempo/total*100) AS high_percentege , total_distance, max_speed,ball_kicks, received_impacts')->fromSub(function ($query) use ($match_id) {
            $query->selectRaw("
                SUM(CASE WHEN stat_type_id=4 THEN stat_value END) AS low_tempo,
                SUM(CASE WHEN stat_type_id=6 THEN stat_value END) AS mid_tempo,
                SUM(CASE WHEN stat_type_id=17 THEN stat_value END) AS high_tempo,
                SUM(CASE WHEN stat_type_id=1 THEN stat_value END) AS total_distance,
                MAX(CASE WHEN stat_type_id=7 THEN stat_value END) AS max_speed,
                SUM(CASE WHEN stat_type_id=8 THEN stat_value END) AS ball_kicks,
                SUM(CASE WHEN stat_type_id=14 THEN stat_value END) AS received_impacts,
                SUM(CASE WHEN stat_type_id IN (4,6,17) THEN stat_value END) AS total,
                matches.`init_ts` AS start_time ,matches.`end_ts` AS end_time
                FROM matches_stats INNER JOIN matches ON matches_stats.`match_id`=matches.`id`
                WHERE match_id = ({$match_id}) AND stat_type_id IN (1,4,6,7,8,14,17)
            ");
        }, 'stat')->first();
        if (!$match_records) {
            return Helper::apiErrorResponse(false, 'match records not found', new \stdClass());
        }
        $diff_in_minutes = Carbon::parse($match_records->start_time)->diffInMinutes(Carbon::parse($match_records->end_time));
        $match_details = DB::query()->selectRaw("right_foot, left_foot,
        (left_foot/total*100) AS left_percentage, (right_foot/total*100) AS right_percentage,
        (total_shots/total_ball_touches*100) AS total_shots_percentage, (total_passes/total_ball_touches*100) AS total_passes_percentage,
        max_shot_speed,total_ball_touches,total_shots,total_passes,  max_speed")
            ->fromSub(function ($query) use ($match_id) {
                $query->selectRaw("
                COUNT(CASE WHEN foot='R' THEN 1 END) AS right_foot,
                COUNT(CASE WHEN foot='L' THEN 1 END) AS left_foot,
                COUNT(CASE WHEN  foot IN ('L','R') THEN 1 END) AS total,
                COUNT(CASE WHEN  event_type IN ('BK','FK') THEN 1 END) AS total_ball_touches,
                MAX(CASE WHEN  event_type IN ('BK') THEN event_magnitude END) AS max_shot_speed,
                COUNT(CASE WHEN  event_type='BK' THEN 1 END) AS total_shots,
                COUNT(CASE WHEN  event_type='FK' THEN 1 END) AS total_passes,
                MAX(speed) AS max_speed
                FROM match_details  WHERE event_id = ({$match_id})
                ");
            }, "f")
            ->first();
        if (!$match_details) {
            return Helper::apiErrorResponse(false, 'match details not found', new \stdClass());
        }

        $records = [];
        if ($previous_records) {
            if ($match_details->total_ball_touches > $previous_records->total_ball_touches) {
                $records[] = [
                    'name' => 'Ball Touch',
                    "image" => "",
                    "value" => $previous_records->total_ball_touches
                ];
            }
            if ($match_details->max_shot_speed > $previous_records->max_shot_speed) {
                $records[] = [
                    'name' => 'New Shot Speed',
                    "image" => "media/stats_icons/ball_kicks.png",
                    "value" => $previous_records->max_shot_speed
                ];
            }
            if ($match_details->max_speed > $previous_records->max_speed) {
                $records[] = [
                    'name' => 'New Max Speed',
                    "image" => "media/stats_icons/max_speed.png",
                    "value" => $previous_records->max_speed
                ];
            }
        }

        $data = [
            'session_details' => $session_details,
            'top_records' => $records,
            'movement_details' => [
                ['name' => 'Total Distance', 'image' => 'media/stats_icons/total_distance.png', 'badge' => '', 'value' => $match_records->total_distance ? $match_records->total_distance . ' m' : '0 m', 'key' => 'distance'],
                ['name' => 'Total Ball Kicks', 'image' => '', 'badge' => '', 'value' => $match_records->ball_kicks ? $match_records->ball_kicks : 0, 'key' => 'ball_kicks'],
                ['name' => 'Max Shot Speed', 'image' => '', 'badge' => 'media/stats_icons/badge.png', 'value' => isset($match_details->max_shot_speed) ? $match_details->max_shot_speed . 'Km/h' : '0 Km/h', 'key' => 'max_shot_speed'],//event magnitutde in bk
                ['name' => 'Total Sprints', 'image' => '', 'badge' => '', 'value' => 0, 'key' => 'sprints'],
                ['name' => 'Impacts', 'image' => 'media/stats_icons/received_impact.png', 'badge' => '', 'value' => $match_records->received_impacts ? $match_records->received_impacts : 0, 'key' => 'impacts'],
                ['name' => 'Max Speed', 'image' => '', 'badge' => 'media/stats_icons/badge.png', 'value' => $match_records->max_speed ? $match_records->max_speed . ' Km/h' : '0 Km/h', 'key' => 'max_speed'],
            ],
            'tempo_records' => [
                ['name' => 'Low Tempo', 'image' => 'media/stats_icons/walking_speed.png', 'percentage' => $match_records->low_percentege ? $match_records->low_percentege . ' %' : ' 0 %', 'time' => round($match_records->low_percentege / 100 * $diff_in_minutes, 1) . ' min', 'key' => 'low_tempo'],
                ['name' => 'Mid Tempo', 'image' => 'media/stats_icons/running_speed.png', 'percentage' => $match_records->mid_percentege ? $match_records->mid_percentege . ' %' : ' 0 %', 'time' => round($match_records->mid_percentege / 100 * $diff_in_minutes, 1) . ' min', 'key' => 'mid_tempo'],
                ['name' => 'High Tempo', 'image' => 'media/stats_icons/speed_sprinting.png', 'percentage' => $match_records->high_percentege ? $match_records->high_percentege . ' %' : ' 0 %', 'time' => round($match_records->high_percentege / 100 * $diff_in_minutes, 1) . ' min', 'key' => 'high_tempo'],
            ],
            'total_touches' => $match_details->total_ball_touches ?? 0 . ' Ball Touches',//bk+fk

            'ball_touches' => [
                ['name' => 'Shots', 'value' => $match_details->total_shots ?? 0, 'percentage' => round($match_details->total_shots_percentage, 2) . ' %'],//bk
                ['name' => 'Passes', 'value' => $match_details->total_passes ?? 0, 'percentage' => round($match_details->total_passes_percentage, 2) . ' %'],//fk
            ],
            'leg_distribution' => [
                ['name' => 'left foot', 'value' => $match_details->left_foot ?? 0, 'percentage' => $match_details->left_percentage ? round($match_details->left_percentage, 2) . '%' : '0 %'],
                ['name' => 'right foot', 'value' => $match_details->right_foot ?? 0, 'percentage' => $match_details->right_percentage ? round($match_details->right_percentage, 2) . '%' : '0 %'],
            ]

        ];

        return Helper::apiSuccessResponse(true, 'success', $data);
    }


    /**
     * getPlayerTrainingSessions
     *
     * @queryParam  player_id required player id is required
     * @queryParam  start_date optional Y-m-d
     * @queryParam  end_date optional Y-m-d
     * @response{
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "success",
     * "Result": [
     * {
     * "id": 1819,
     * "started_at": "24.11.2020",
     * "type": "match",
     * "player_name": "Tariq Sidd",
     * "player_id": 4,
     * "sensor_type": "shinguard"
     * },
     * {
     * "id": 1818,
     * "started_at": "24.11.2020",
     * "type": "match",
     * "player_name": "Tariq Sidd",
     * "player_id": 4,
     * "sensor_type": "shinguard"
     * },
     * {
     * "id": 1817,
     * "started_at": "24.11.2020",
     * "type": "match",
     * "player_name": "Tariq Sidd",
     * "player_id": 4,
     * "sensor_type": "shinguard"
     * },
     * {
     * "id": 1143,
     * "started_at": "28.10.2020",
     * "type": "match",
     * "player_name": "Tariq Sidd",
     * "player_id": 4,
     * "sensor_type": "shinguard"
     * }
     * ]
     * }
     *
     *
     */

    public function getTrainingSessions(Request $request)
    {
        $this->validate($request, [
            'player_id' => 'required'
        ]);
        $start_date = '1970-01-01';
        $end_date = date('Y-m-d');
        if (isset($request->start_date)) {
            $start_date = $request->start_date;
        }
        if (isset($request->end_date)) {
            $end_date = $request->end_date;
        }
        $matches = Match::whereHas('player')
            ->with('player:id,first_name,middle_name,last_name')
            ->select('id as match_id', 'match_type', 'user_id', DB::raw("DATE_FORMAT(init_ts, '%Y-%m-%d') as start_time"))
            ->where('user_id', $request->player_id)
            ->whereDate('init_ts', '>=', $start_date)
            ->whereDate('init_ts', '<=', $end_date)
            ->orderBy('init_ts', 'DESC');
        $matches = $matches->get()->map(function ($match) {
            return [
                'id' => $match->match_id,
                'started_at' => $match->start_time,
                'type' => $match->match_type ?? 'training',
                'player_name' => $match->player->first_name . ' ' . $match->player->last_name,
                'player_id' => $match->user_id,
                'sensor_type' => 'shinguard'
            ];
        });
        if (count($matches)) {
            return Helper::apiSuccessResponse(true, 'success', $matches);
        }
        return Helper::apiErrorResponse(false, 'no data found', new \stdClass());
    }


    /**
     * getTrainingSessionDates
     *
     * @queryParam  player_id required player id is required
     * @response{
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "success",
     * "Result": [
     * {
     * "11-11-2020": {
     * "dots": [
     * {
     * "key": "training",
     * "color": "red",
     * "selectedDotColor": "blue"
     * },
     * {
     * "key": "match",
     * "color": "blue",
     * "selectedDotColor": "red"
     * }
     * ]
     * }
     * },
     * {
     * "29-10-2020": {
     * "dots": [
     * {
     * "key": "match",
     * "color": "blue",
     * "selectedDotColor": "red"
     * }
     * ]
     * }
     * },
     * {
     * "25-10-2020": {
     * "dots": [
     * {
     * "key": "match",
     * "color": "blue",
     * "selectedDotColor": "red"
     * }
     * ]
     * }
     * },
     * {
     * "19-10-2020": {
     * "dots": [
     * {
     * "key": "match",
     * "color": "blue",
     * "selectedDotColor": "red"
     * }
     * ]
     * }
     * }
     * ]
     * }
     *
     *
     */
    public function getTrainingSessionDates(Request $request)
    {
        $this->validate($request, [
            'player_id' => 'required'
        ]);
        $matches = Match::select(DB::raw("DATE_FORMAT(init_ts, '%d-%m-%Y') as date"), 'id', 'match_type')
            ->where('user_id', $request->player_id)
            ->orderBy('init_ts', 'DESC')
            ->get()->groupBy("date");
        $data = [];

        foreach ($matches as $date => $match) {
            $user_matches = collect($match)->groupBy('match_type');
            $dots = [];
            if (isset($user_matches['training'])) {
                $dots[] = ['key' => 'training', 'color' => 'red', 'selectedDotColor' => 'blue'];
            }
            if (isset($user_matches['match'])) {
                $dots[] = ['key' => 'match', 'color' => 'blue', 'selectedDotColor' => 'red'];
            }
            $data[] = [
                $date => ['dots' => $dots]
            ];
        }
        if (count($data)) {
            return Helper::apiSuccessResponse(true, 'success', $data);
        }
        return Helper::apiErrorResponse(false, 'no data found', new \stdClass());
    }


    /**
     * getSuggested Escersices
     *
     * @queryParam  player_id required player id is required
     * @response{
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Successfully getting player suggested exercises",
     * "Result": [
     * {
     * "id": 1,
     * "title": "10 Cones dribble (L/R)",
     * "image": "media/exercise/images/JOGO_D3.2.jpeg",
     * "skills": [
     * {
     * "id": 1,
     * "name": "Agility",
     * "pivot": {
     * "exercise_id": 1,
     * "skill_id": 1
     * }
     * },
     * {
     * "id": 2,
     * "name": "Ball Control",
     * "pivot": {
     * "exercise_id": 1,
     * "skill_id": 2
     * }
     * },
     * {
     * "id": 3,
     * "name": "Change of Direction",
     * "pivot": {
     * "exercise_id": 1,
     * "skill_id": 3
     * }
     * }
     * ],
     * "tools": [
     * {
     * "id": 1,
     * "tool_name": "Cones",
     * "pivot": {
     * "exercise_id": 1,
     * "tool_id": 1
     * }
     * },
     * {
     * "id": 2,
     * "tool_name": "Ball",
     * "pivot": {
     * "exercise_id": 1,
     * "tool_id": 2
     * }
     * }
     * ]
     * },
     * {
     * "id": 2,
     * "title": "10 Cones dribble (R)",
     * "image": "media/exercise/images/JOGO_D3.3.jpeg",
     * "skills": [
     * {
     * "id": 1,
     * "name": "Agility",
     * "pivot": {
     * "exercise_id": 2,
     * "skill_id": 1
     * }
     * },
     * {
     * "id": 2,
     * "name": "Ball Control",
     * "pivot": {
     * "exercise_id": 2,
     * "skill_id": 2
     * }
     * }
     * ],
     * "tools": [
     * {
     * "id": 1,
     * "tool_name": "Cones",
     * "pivot": {
     * "exercise_id": 2,
     * "tool_id": 1
     * }
     * },
     * {
     * "id": 2,
     * "tool_name": "Ball",
     * "pivot": {
     * "exercise_id": 2,
     * "tool_id": 2
     * }
     * }
     * ]
     * }
     * ]
     * }
     *
     *
     */


    public function getSuggestedExcersices(Request $request)
    {
        $player_recommended_exercises =
            Exercise::select('id', 'title', 'image')
                ->whereHas('skills')
                ->with(['skills' => function ($q2) {
                    $q2->select('skills.id', 'skills.name');
                }])->with(['tools'
                => function ($q2) {
                        $q2->select('tools.id', 'tools.tool_name');
                    }])
                ->limit(2)->get();
        if (count($player_recommended_exercises) == 0) {
            return Helper::apiUnAuthenticatedResponse(false, 'no data found', new stdClass());
        } else {
            return Helper::apiSuccessResponse(true, 'Successfully getting player recommended exercises', $player_recommended_exercises);
        }

    }


    /**
     * getSuggested Skills Escersices
     *
     * @queryParam  skills[] required
     * @response{
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Successfully getting skills suggested exercises",
     * "Result": [
     * {
     * "id": 1,
     * "title": "10 Cones dribble (L/R)",
     * "image": "media/exercise/images/JOGO_D3.2.jpeg",
     * "skills": [
     * {
     * "id": 1,
     * "name": "Agility",
     * "pivot": {
     * "exercise_id": 1,
     * "skill_id": 1
     * }
     * },
     * {
     * "id": 2,
     * "name": "Ball Control",
     * "pivot": {
     * "exercise_id": 1,
     * "skill_id": 2
     * }
     * },
     * {
     * "id": 3,
     * "name": "Change of Direction",
     * "pivot": {
     * "exercise_id": 1,
     * "skill_id": 3
     * }
     * }
     * ],
     * "tools": [
     * {
     * "id": 1,
     * "tool_name": "Cones",
     * "pivot": {
     * "exercise_id": 1,
     * "tool_id": 1
     * }
     * },
     * {
     * "id": 2,
     * "tool_name": "Ball",
     * "pivot": {
     * "exercise_id": 1,
     * "tool_id": 2
     * }
     * }
     * ]
     * },
     * {
     * "id": 2,
     * "title": "10 Cones dribble (R)",
     * "image": "media/exercise/images/JOGO_D3.3.jpeg",
     * "skills": [
     * {
     * "id": 1,
     * "name": "Agility",
     * "pivot": {
     * "exercise_id": 2,
     * "skill_id": 1
     * }
     * },
     * {
     * "id": 2,
     * "name": "Ball Control",
     * "pivot": {
     * "exercise_id": 2,
     * "skill_id": 2
     * }
     * }
     * ],
     * "tools": [
     * {
     * "id": 1,
     * "tool_name": "Cones",
     * "pivot": {
     * "exercise_id": 2,
     * "tool_id": 1
     * }
     * },
     * {
     * "id": 2,
     * "tool_name": "Ball",
     * "pivot": {
     * "exercise_id": 2,
     * "tool_id": 2
     * }
     * }
     * ]
     * }
     * ]
     * }
     *
     *
     */


    public function getSuggestedSkillsExcersices(Request $request)
    {
        $locale = App::getLocale();
        $skills = $request->skills ?? [];
        $limit = $request->limit ?? 2;
        $offset = $request->offset ?? 0;
        $player_recommended_exercises =
            Exercise::select('id', 'title', 'image')
                ->whereHas('skills', function ($q) use ($skills, $locale) {
                    $q->whereIn('name->' . $locale, $skills);
                })
                ->with(['skills' => function ($q2) {
                    $q2->select('skills.id', 'skills.name');
                }])->with(['tools'
                => function ($q2) {
                        $q2->select('tools.id', 'tools.tool_name');
                    }])->limit($limit)->get();
        if (count($player_recommended_exercises) == 0) {
            return Helper::apiUnAuthenticatedResponse(false, 'no data found', new stdClass());
        } else {
            return Helper::apiSuccessResponse(true, 'Successfully getting player recommended exercises', $player_recommended_exercises);
        }

    }

    public function player_followers_followings(){
        $followings_ids = Contact::where('user_id', Auth::user()->id)->pluck('contact_user_id')->toArray();
        $followers_ids = Contact::where('contact_user_id', Auth::user()->id)->pluck('user_id')->toArray();

        $player_followers_followings = User::with([
            'followers' => function ($q1) {
                $q1->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.profile_picture');
            },
            'followings' => function ($q) {
                $q->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.profile_picture');
            }])->find(Auth::user()->id);

        return ['followings_ids' => $followings_ids, 'followers_ids' => $followers_ids, 'player_followers_followings' => $player_followers_followings];
    }

    public function checkTrainerOrPlayerRole($user_id){
        $user = User::find($user_id);
        if (empty($user)) {
            return ['status' => false, 'msg' => "User has not been found with this id " . $user_id];
        } else {
            $user_trainer_player_check = is_null(Trainer::where('user_id', $user_id)->first()) ? 'player' : 'trainer';
            if ($user_trainer_player_check == 'trainer') {

                return ['status' => false, 'msg' => "Can't find records with trainer profile"];
            }
        }

        return ['status' => true, 'role' => $user_trainer_player_check];
    }
}