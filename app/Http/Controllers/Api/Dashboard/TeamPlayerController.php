<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\{
    Club,
    Exercise,
    PlayerExercise,
    Position,
    Post,
    Skill,
    Status,
    MatchDetails,
    MatchStat,
    MatchStatType,
    Team,
    User,
    UserExerciseAiData
};

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\PlayerStatisticsRequest;

use Carbon\Carbon;
use Carbon\CarbonInterval;

use Illuminate\Http\{
    JsonResponse,
    Request
};

use Illuminate\Support\Facades\{
    Auth,
    DB,
    Storage,
    Validator
};
use Response;
use stdClass;


/**
 * @authenticated
 * @group Trainer Apis
 * APIs For Trainers
 * User Auth Token is required in headers
 */
class TeamPlayerController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    private function mappingSkills($ex){
        return $ex->skills->map(function ($skill) {
            $obj['id'] = $skill->id;
            $obj['name'] = $skill->name;
            return $obj;
        });
    }

    private function mappingLevels($ex){
        return $ex->levels->map(function ($level) {
            $obj['id'] = $level->id;
            $obj['title'] = $level->title . ' - ' . $level->pivot->measure . ' repetition';

            return $obj;
        });
    }

    /**
     * GetAllTeams
     *
     * @queryParam  access required option you want to access
     *
     * JSON array of all teams linked to current user
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Successfully getting teams linked to current user",
     *     "Result": [
     *         {
     *             "id": 1,
     *             "team_name": "Ajax U16",
     *             "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     *             "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
     *             "created_at": "2020-07-15 23:30:41",
     *             "updated_at": "2020-07-15 23:30:41",
     *             "deleted_at": null,
     *             "pivot": {
     *                 "trainer_user_id": 6,
     *                 "team_id": 1,
     *                 "created_at": "2020-07-20 02:24:56"
     *             }
     *         }
     *     ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "This user is not linked to any team",
     *     "Result": {}
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     * @queryParam clubId required integer
     */
    public function get_all_teams(Request $request)
    {
        $request->validate([
            "clubId" => ["required", "integer"]
        ]);

        // CHECK IF THE TRAINER IS IN THE CLUB OR NOT.
        $clubs = (new Club())->myCLubs($request);
        $club_ids = [];
        foreach ($clubs->original["Result"] as $club) {
            $club_ids[] = $club['id'];
        }
        if (!in_array($request->clubId, $club_ids)) {
            return Helper::apiErrorResponse(false, 'Add Club First', new \stdClass());
        }

        $teams_trainers = Auth::user()->teams_trainers;
        $team_ids = $teams_trainers->pluck("id");
        $teamInClubs = DB::table("club_teams")->whereIn("team_id", $team_ids)->where("club_id", $request->clubId)->pluck("team_id");
        $teams_trainers = $teams_trainers->whereIn("id", $teamInClubs);


        if (count($teams_trainers) > 0) {
            $teams = [];
            foreach ($teams_trainers as $team) {
                $team_permissions = Helper::getTeamPermissions($team->id);
                if (in_array($request->access, $team_permissions)) {
                    $obj = new \stdClass();
                    $obj->id = $team->id;
                    $obj->team_name = $team->team_name;
                    $obj->image = $team->image;
                    $obj->gender = $team->gender;
                    $obj->team_type = $team->team_type;
                    $obj->description = $team->description;
                    $obj->age_group = $team->age_group;
                    $obj->min_age_group = $team->min_age_group;
                    $obj->max_age_group = $team->max_age_group;
                    $obj->players_count = DB::table('player_team')->whereTeamId($team->id)->count();
                    $obj->pivot = $team->pivot;

                    $teams[] = $obj;
                }
            }
//            exit;
//            $teams_trainers = $teams_trainers->map(function ($team) use ($request) {
//                $team_permissions = Helper::getTeamPackage($team->id);
//                if(in_array($request->access,$team_permissions)){
//                    $obj = new \stdClass();
//                    $obj->id = $team->id;
//                    $obj->team_name = $team->team_name;
//                    $obj->image = $team->image;
//                    $obj->gender = $team->gender;
//                    $obj->team_type = $team->team_type;
//                    $obj->description = $team->description;
//                    $obj->age_group = $team->age_group;
//                    $obj->min_age_group = $team->min_age_group;
//                    $obj->max_age_group = $team->max_age_group;
//                    $obj->pivot = $team->pivot;
//
//                    return $obj;
//                }
////                return $team_permissions;
//            });
            return Helper::apiSuccessResponse(true, 'Successfully getting teams linked to current user', $teams);
        } else {
            $message = "This user is not linked to any team";
            return Helper::apiNotFoundResponse(false, $message, new stdClass());
        }
    }

    /**
    GetAllExercies

    JSON array containing all available exercises in alphabetic order &
    JSON array of leaderboard for each exercies &
    JSON array of filters

    @response
    {
        "Response": true,
        "StatusCode": 200,
        "Message": "Records found successfully!",
        "Result": {
            "exercises": [
                {
                    "id": 207,
                    "title": " Low power shot (L)",
                    "badge": "non_ai",
                    "image": "media/exercise/images/JOGO_B28.2.jpeg",
                    "description": "Push the ball diagonally in front of your left foot and strike it hard and low towards the goal. Aim for the inside of the far post.",
                    "video": "media/exercise/BV2/JOGO_B28.2_v2.mp4",
                    "is_custom": false,
                    "skills": [
                        {
                            "id": 6,
                            "name": "Technique"
                        },
                        {
                            "id": 9,
                            "name": "Accuracy"
                        }
                    ],
                    "levels": [
                        {
                            "id": 1,
                            "title": "Level 1 - 10 repetition"
                        }
                    ]
                }
            ],
            "filters": {
                "skills": [
                    {
                        "id": 1,
                        "name": "Agility"
                    }
                ]
            }
        }
    }

    @response 404 {
    "Response": false,
    "StatusCode": 404,
    "Message": "Records not found",
    "Result": {}
    }

    @response 401 {
    "Response": false,
    "StatusCode": 401,
    "Message": "Unauthenticated user to access this route",
    "Result": {}
    }

    @queryParam club_id required integer.
    @queryParam limit required integer. Example: 10
    @queryParam offset required integer. Example: 0
     **/

    public function get_all_exercises(Request $request)
    {
        ini_set('memory_limit', '-1');

        $club_id = $request->club_id;

        $status_active = Status::where('name', 'active')
            ->first();

        $teams = DB::table('club_teams')->where('club_id', $club_id)->pluck('team_id')->toArray();
        $other_teams_exercises = DB::table('exercise_teams')->distinct()->whereNotIn('team_id', $teams)->pluck('exercise_id');
        $custom_exercises = DB::table('exercise_teams')->whereIn('team_id', $teams)->pluck('exercise_id')->toArray();
        $exercises = Exercise::select('exercises.id', 'exercises.image', 'exercises.title', 'exercises.description', 'exercises.image', 'exercises.video', 'exercises.badge')
            ->with([
                'skills:skills.id,skills.name'
            ])
            ->with('levels')
            ->where('is_active', $status_active->id ?? 0)
            ->whereNotIn('id', $other_teams_exercises)
//            ->orderBy('exercises.title')
            ->limit($request->limit ?? 20)
            ->offset($request->offset ?? 0)
            ->get();

        $skills = Skill::select('id', 'name')
            ->get();
        $response = [];
        $response['custom_exercises'] = [];
        $response['non_custom_exercises'] = [];
        foreach ($exercises as $ex) {
            $obj = new stdClass();
            $obj->id = $ex->id;
            $obj->title = $ex->title;
            $obj->badge = $ex->badge;
            $obj->image = $ex->image;
            $obj->description = $ex->description;
            $obj->video = $ex->video;
            $obj->is_custom = in_array($ex->id,$custom_exercises) ? true : false;
            $obj->skills = $this->mappingSkills($ex);
            $obj->levels = $this->mappingLevels($ex);
            if(in_array($ex->id,$custom_exercises)){
                $response['custom_exercises'][] = $obj;
            }else{
                $response['non_custom_exercises'][] = $obj;
            }
        }

        $response['filters'] = [
            'skills' => $skills
        ];

//        return $response;
        $response['exercises'] = array_merge($response['custom_exercises'],$response['non_custom_exercises']);

        if (count($exercises)) {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());
    }

    /**
     * Exercise Details
     *
     * JSON array containing available exercise &
     * JSON array of leaderboard for each exercies &
     * JSON array of filters
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Record found successfully!",
     * "Result": {
     * "id": 2,
     * "title": "10 Cones dribble (R)",
     * "description": "Weave through the ten cones using only your right foot to improve your close control dribbling skills. The ball should stay in close contact with your foot throughout the entire exercise.",
     * "image": "media/exercise/images/JOGO_D3.3.jpeg",
     * "video": "media/exercise/DV2/JOGO_D3.3_v2.mp4",
     * "player_completed_count": 2,
     * "overall_time": 139,
     * "badge": "non_ai",
     * "skills": [
     * {
     * "id": 1,
     * "name": "Agility"
     * },
     * {
     * "id": 2,
     * "name": "Ball Control"
     * }
     * ],
     * "levels": [
     * {
     * "id": 1,
     * "title": "Level 1 - 90 repetition"
     * }
     * ],
     * "leaderboard": [
     * {
     * "id": 1,
     * "full_name": "muhammad. shahzaib",
     * "profile_picture": "media/users/5fa27263a93271604481635.jpeg",
     * "completion_time": 3,
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
     * ],
     * "exercise_id": 2,
     * "repetitions": 3,
     * "date": "2020-11-13",
     * "level_id": 2,
     * "thumbnail": "media/player_exercises/FNCXV0JrhpZ5jAmjfJunJJkqZXFtNwCwUkG89aC9.jpg",
     * "video_file": "media/player_exercises/C1u6CBg5M4fkgrn4av7As9MBmVPmLunmOXDDA0bw.mp4",
     * "team_name": "ManUtd U18"
     * }
     * ]
     * }
     * }
     *
     * @response 404 {
     * "Response": false,
     * "StatusCode": 404,
     * "Message": "Records not found",
     * "Result": {}
     * }
     *
     * @response 401 {
     * "Response": false,
     * "StatusCode": 401,
     * "Message": "Unauthenticated user to access this route",
     * "Result": {}
     * }
     *
     * @queryParam ID required integer. Example: 1
     **/
    public function exerciseDetails(Request $request)
    {
        ini_set('memory_limit', '-1');

        $club = DB::table('club_trainers')
            ->where('trainer_user_id', Auth::user()->id)
            ->first();

        $club_id = $club->club_id ?? 0;

        $trainer_teams_by_club = DB::table('team_trainers')
            ->where('trainer_user_id', auth()->user()->id)
            ->pluck('team_id')
            ->toArray();

        $status = Status::where('name', 'completed')
            ->first();

        $status_active = Status::where('name', 'active')
            ->first();

        $exercises = Exercise::select('exercises.id', 'exercises.title', 'exercises.description', 'exercises.image', 'exercises.video', 'exercises.badge')
            ->with([
                'skills:skills.id,skills.name',
                'leaderboard' => function ($q) use ($trainer_teams_by_club, $request) {
                    $q->select('users.id', 'users.first_name', 'users.last_name', 'users.middle_name', 'users.profile_picture', DB::raw("ROUND(completion_time) as completion_time"), 'thumbnail', 'video_file');
                    $q->with([
                        'player' => function ($q2) {
                            $q2->select('id', 'user_id', 'position_id');
                        },
                        'player.positions' => function ($query) {
                            $query->select('positions.id', 'name', 'lines');
                        },
                        'player.positions.line' => function ($query) {
                            $query->select('lines.id', 'name');
                        }
                    ])->withCount(["exercises" => function ($q3) use ($request) { // TOTAL REPETITON
                        $q3->where("exercise_id", $request->id);
                    }])->with(["exercises" => function ($q4) use ($request) { // TO GET THE START DATE OF THAT EXERCISE LATEST
                        $q4->select("start_time")->where("exercise_id", $request->id)->latest("exercises.created_at");
                    }]);
                    $q->with([
                        'teams' => function ($t) {
                            $t->select('teams.id', 'teams.team_name');
                        }
                    ]);
                    $q->whereHas('teams', function ($e) use ($trainer_teams_by_club) {
                        $e->whereIn('teams.id', $trainer_teams_by_club);
                    });
                }
            ])
            //Temporary commented for listing clubs
            //->whereHas('leaderboard.teams', function ($e) use ($trainer_teams_by_club) {
            //$e->whereIn('teams.id', $trainer_teams_by_club);
            //})
            ->with('levels')
            ->withCount([
                'leaderboard as player_completed_count' => function ($q) use ($status, $trainer_teams_by_club) {
                    $q->whereHas('teams', function ($e) use ($trainer_teams_by_club) {
                        $e->whereIn('teams.id', $trainer_teams_by_club);
                    });
                    $q->where('player_exercise.status_id', $status->id ?? 0);
                }
            ])
            ->where('is_active', $status_active->id ?? 0)
            ->where('id', $request->id)
            ->get();

        $skills = Skill::select('id', 'name')
            ->get();

        foreach ($exercises as $ex) {
            if (count($ex->teams)) {
                $teams = $ex->teams
                    ->pluck('id')
                    ->toArray();

                if (!count(array_intersect($teams, $trainer_teams_by_club))) {
                    continue;
                }
            }

            $obj = new stdClass();
            $obj->id = $ex->id;
            $obj->title = $ex->title;
            $obj->description = $ex->description;
            $obj->image = $ex->image;
            $obj->video = $ex->video;
            $obj->player_completed_count = $ex->player_completed_count;
            $obj->overall_time = PlayerExercise::where('exercise_id', $ex->id)
                ->sum('completion_time');
            $obj->badge = $ex->badge;

            $obj->skills = $this->mappingSkills($ex);
            $obj->levels = $this->mappingLevels($ex);

            $obj->leaderboard = $ex->leaderboard->map(function ($user) {

                $obj['id'] = $user->id;
                $obj['full_name'] = $user->first_name . ' ' . $user->last_name;
                $obj['profile_picture'] = $user->profile_picture;
                $obj['completion_time'] = $user->completion_time;
                $obj['positions'] = $user->player->positions ?? [];
                $obj['exercise_id'] = $user->pivot->exercise_id ?? '';
                $obj["repetitions"] = $user->exercises_count;
                $obj["date"] = count($user->exercises) > 0 ? Carbon::create($user->exercises[0]->start_time)->format("Y-m-d") : "";
                $obj['level_id'] = $user->pivot->exercise_id ?? '';
                $obj['thumbnail'] = $user->thumbnail ?? '';
                $obj['video_file'] = $user->video_file ?? '';
                $obj['team_name'] = (count($user->teams) == 0) ? '' : $user->teams[0]['team_name'];
                return $obj;
            });


            $response = $obj;


        }

        if (isset($response)) {
            return Helper::apiSuccessResponse(true, 'Record found successfully!', $response);
        }

        return Helper::apiNotFoundResponse(false, 'Record not found', new stdClass());
    }


    /**
     * GetExerciseData
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Records found successfully!",
     *     "Result": {
     *         "exercise": {
     *             "id": 1,
     *             "title": "10 Cones Free - Regular Ball",
     *             "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
     *             "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     *             "video": "https://www.youtube.com/watch?v=FLV1z9BWvyc",
     *             "leaderboard_direction": "desc",
     *             "leaderboard": [
     *                 {
     *                     "id": 1,
     *                     "first_name": "Fahad",
     *                     "last_name": "Ahmed",
     *                     "middle_name": null,
     *                     "profile_picture": null,
     *                     "time": 1320,
     *                     "pivot": {
     *                         "exercise_id": 1,
     *                         "user_id": 1
     *                     },
     *                     "teams": [
     *                         {
     *                             "id": 1,
     *                             "team_name": "Ajax U16",
     *                             "pivot": {
     *                                 "user_id": 1,
     *                                 "team_id": 1,
     *                                 "created_at": "2020-07-15 23:32:55"
     *                             }
     *                         }
     *                     ]
     *                 }
     *             ]
     *         },
     *         "average_score": [
     *             {
     *                 "total_score": "2018",
     *                 "avg_score": "336.3333",
     *                 "pivot": {
     *                     "exercise_id": 1,
     *                     "user_id": 1
     *                 }
     *             }
     *         ],
     *         "players_completed": 3,
     *         "overall_time": 1810
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "exercise_id": [
     *             "The exercise id field is required."
     *         ]
     *     }
     * }
     *
     * @response 404 {
     *     "Response": false,
     *     "StatusCode": 404,
     *     "Message": "Records not found",
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
    public function get_exercise_data(Request $request)
    {


        Validator::make($request->all(), [
            'exercise_id' => 'required|exists:exercises,id'
        ])->validate();


        $users = User::role('player')->with('user_privacy_settings')->whereHas('user_privacy_settings')->get(); // Returns only users with the role 'writer'

        $user_privacy_settings = array();
        foreach ($users as $single_record) {

            $inner_array = array();
            foreach ($single_record->user_privacy_settings as $ss) {
                $inner_array['user_id'] = $ss->pivot->user_id;
                $inner_array['access_modifier_name'] = $ss->name;
            }
            $user_privacy_settings[] = $inner_array;
        }

        $collect_user_ids = [];
        if (count($user_privacy_settings) > 0) {

            foreach ($user_privacy_settings as $user_info) {
                if (strtolower($user_info['access_modifier_name']) == 'public') {
                    $collect_user_ids[] = $user_info['user_id'];
                }

            }

        }

        //return $collect_user_ids;
        $exercise_id = $request->exercise_id;
        $exe = Exercise::find($exercise_id);
        $exercise = Exercise::join('player_scores', 'player_scores.exercise_id', '=', 'exercises.id')
            ->select('exercises.id', 'exercises.title', 'exercises.description', 'exercises.image', 'exercises.video', 'exercises.leaderboard_direction')
            ->whereHas('leaderboard')
            ->with([
                'leaderboard' => function ($q) use ($collect_user_ids, $exe) {
                    $q->selectRaw('users.id,users.first_name,users.last_name,users.middle_name,users.profile_picture,SUM(player_exercise.completion_time) as time');
                    $q->orderBy('completion_time', $exe->leaderboard_direction);
                    $q->whereIn('users.id', $collect_user_ids);
                    $q->groupBy('users.id');
                    $q->with('teams:teams.id,teams.team_name');
                },

                'player_scores_users' => function ($q1) use ($exercise_id, $collect_user_ids) {
                    $q1->selectRaw('sum(player_scores.score) as total_score,avg(player_scores.score) as avg_score');
                    $q1->where('player_scores.exercise_id', $exercise_id);
                    $q1->whereIn('player_scores.user_id', $collect_user_ids);
                }

            ])
            ->find($request->exercise_id);

        if (!$exercise) {
            return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());
        }

        $response['exercise'] = $exercise;
        $response['average_score'] = $exercise->player_scores_users ?? 0;
        unset($exercise->player_scores_users);
        $response['players_completed'] = count($exercise->leaderboard);

        $overall_time = 0;
        if (count($exercise->leaderboard) > 0) {
            foreach ($exercise->leaderboard as $get_leaderboard) {
                $overall_time += $get_leaderboard->time;
            }
        }
        $response['overall_time'] = $overall_time;

        if ($exercise) {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());
    }


    /**
     * GetAllPlayers
     *
     * JSON array containing players linked to the teams associated with current trainer OR
     * JSON array containing all players having their privacy setting as public
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Records found successfully!",
     *     "Result": {
     *         "get_all_players": [
     *             {
     *                 "id": 1,
     *                 "first_name": "Fahad",
     *                 "middle_name": null,
     *                 "last_name": "Ahmed",
     *                 "profile_picture": null,
     *                 "teams": [
     *                     {
     *                         "id": 1,
     *                         "team_name": "Ajax U16",
     *                         "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     *                         "pivot": {
     *                             "user_id": 1,
     *                             "team_id": 1,
     *                             "created_at": "2020-07-15 23:32:55"
     *                         }
     *                     }
     *                 ],
     *                 "player": {
     *                     "id": 1,
     *                     "user_id": 1,
     *                     "position_id": 1,
     *                      "positions": [
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
     *                 },
     *                 "leaderboards": {
     *                     "id": 1,
     *                     "user_id": 1,
     *                     "total_score": 1200
     *                 }
     *             }
     *         ],
     *         "filters": []
     *     }
     * }
     *
     *
     * @response 404 {
     *     "Response": false,
     *     "StatusCode": 404,
     *     "Message": "Records not found",
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

    public function get_all_players()
    {

        //$current_trainer_team_id = Auth::user()->teams_trainers[0]->id ?? 0;
//        $current_trainer_team_id = Auth::user()->teams_trainers[0]->id ?? 0;

        $current_trainer_team_id = \auth()->user()->teams_trainers ? auth()->user()->teams_trainers->pluck('id')->toArray() : [0];
//        if($current_trainer_team_id == 0){
//            return Helper::apiNotFoundResponse(false, 'players not found', []);
//        }
        if (isset(Auth::user()->teams_trainers[0])) {
            //echo $current_trainer_team_id;die;
            $get_same_team_players = $this->userModel->getUserExerciseData(false,false)
                ->orderBy('created_at')
                ->get();

            if (count($get_same_team_players) > 0) {

                $response['get_all_players'] = $get_same_team_players;
                $response['filters'] = [];
                return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);

            }
            return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

        } else {
            /*
            * JSON array containing all players having their privacy setting as public
            */

            $users = User::role('player')->with('user_privacy_settings')->get();
            $user_privacy_settings = array();
            foreach ($users as $single_record) {
                $inner_array = array();
                foreach ($single_record->user_privacy_settings as $ss) {
                    $inner_array['user_id'] = $ss->pivot->user_id;
                    $inner_array['access_modifier_name'] = $ss->name;
                }
                $user_privacy_settings[] = $inner_array;
            }

            $user_privacy_filter_settings = $user_privacy_settings;
            $user_privacy_settings = array_filter($user_privacy_filter_settings);
            $collect_user_ids = [];
            if (count($user_privacy_settings) > 0) {
                foreach ($user_privacy_settings as $user_info) {
                    if (strtolower($user_info['access_modifier_name']) == 'public') {
                        $collect_user_ids[] = $user_info['user_id'];
                    }
                }
            }

            $get_team_players_public_privacy = $this->userModel->getUserExerciseData(false,false)
                ->orderBy('created_at')
                ->whereIn('id', $collect_user_ids)
                ->get();

            if (count($get_team_players_public_privacy) > 0) {

                $response['get_all_players'] = $get_team_players_public_privacy;
                $response['filters'] = [];
                return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);

            }

            return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());
        }

    }


    /**
     * GetPlayerDetails
     *
     * All player data with addition to last three exercies data which were completed by player.(only fetch data from players which have privacy setting as public)
     *
     * @queryParam  player_id required player id is required
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Records found successfully!",
     *     "Result": {
     *         "get_player_details": {
     *             "id": 1,
     *             "nationality_id": 1,
     *             "first_name": "Fahad",
     *             "middle_name": null,
     *             "last_name": "Ahmed",
     *             "profile_picture": null,
     *             "date_of_birth": "1995-03-23 16:50:11",
     *             "teams": [
     *                 {
     *                     "id": 2,
     *                     "team_name": "Ajax U17",
     *                     "image": "https://camo.githubusercontent.com/8711e1e5b796488ab56ea297dfdc946ae709d029/68747470733a2f2f692e696d6775722e636f6d2f6c4a567a3249492e706e67",
     *                     "pivot": {
     *                         "user_id": 1,
     *                         "team_id": 2,
     *                         "created_at": "2020-07-15 23:32:55"
     *                     }
     *                 }
     *             ],
     *             "nationality": {
     *                 "id": 1,
     *                 "name": "Netherlands"
     *             },
     *             "player": {
     *                 "id": 1,
     *                 "user_id": 1,
     *                 "position_id": 1,
     *                 "customary_foot_id": 1,
     *                 "height": 9.41,
     *                 "weight": 70,
     *                 "jersey_number": "21",
     *                  "positions": [
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
     * ],
     *                 "customary_foot": {
     *                     "id": 1,
     *                     "name": "Left"
     *                 }
     *             },
     *             "leaderboards": {
     *                 "id": 1,
     *                 "user_id": 1,
     *                 "total_score": 1200,
     *                 "position": 12
     *             },
     *             "exercises": [
     *                 {
     *                     "id": 3,
     *                     "completion_time": 450,
     *                     "video_file": "https://www.youtube.com/watch?v=FLV1z9BWvyc",
     *                     "created_at": "2020-07-21 20:49:41",
     *                     "pivot": {
     *                         "user_id": 1,
     *                         "exercise_id": 3
     *                     }
     *                 },
     *                 {
     *                     "id": 1,
     *                     "completion_time": 780,
     *                     "video_file": "https://www.youtube.com/watch?v=nho6TYW2V4k",
     *                     "created_at": "2020-07-20 23:07:28",
     *                     "pivot": {
     *                         "user_id": 1,
     *                         "exercise_id": 1
     *                     }
     *                 },
     *                 {
     *                     "id": 2,
     *                     "completion_time": 90,
     *                     "video_file": "https://www.youtube.com/watch?v=5twveLmWhvI",
     *                     "created_at": "2020-07-16 00:17:51",
     *                     "pivot": {
     *                         "user_id": 1,
     *                         "exercise_id": 2
     *                     }
     *                 }
     *             ]
     *         },
     *         "get_all_skills_insights": [
     *             {
     *                 "id": 1,
     *                 "name": "Agility ",
     *                 "score": 200,
     *                 "skill_id": 1,
     *                 "created_at": "2020-07-16 01:20:30"
     *             },
     *             {
     *                 "id": 1,
     *                 "name": "Agility ",
     *                 "score": 100,
     *                 "skill_id": 1,
     *                 "created_at": "2020-07-16 01:20:17"
     *             },
     *             {
     *                 "id": 2,
     *                 "name": "Ball Control",
     *                 "score": 900,
     *                 "skill_id": 2,
     *                 "created_at": "2020-07-16 01:20:24"
     *             },
     *             {
     *                 "id": 2,
     *                 "name": "Ball Control",
     *                 "score": 90,
     *                 "skill_id": 2,
     *                 "created_at": "2020-07-16 01:18:33"
     *             },
     *             {
     *                 "id": 2,
     *                 "name": "Ball Control",
     *                 "score": 110,
     *                 "skill_id": 2,
     *                 "created_at": "2020-07-16 01:18:33"
     *             }
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "player_id": [
     *             "The player id field is required."
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "player_id": [
     *             "The selected player id is invalid."
     *         ]
     *     }
     * }
     *
     * @response 404 {
     *     "Response": false,
     *     "StatusCode": 404,
     *     "Message": "Player privacy is not public. Can't proceed further",
     *     "Result": {}
     * }
     *
     * @response 404 {
     *     "Response": false,
     *     "StatusCode": 404,
     *     "Message": "Player has not privacy setting. Can't proceed further",
     *     "Result": {}
     * }
     *
     * @response 404 {
     *     "Response": false,
     *     "StatusCode": 404,
     *     "Message": "Records not found",
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

    public function get_player_details(Request $request)
    {
        Validator::make($request->all(), [
            'player_id' => 'required|exists:users,id'
        ])->validate();


        /**
         *  Checking current user privacy setting
         */

        $player_privacy_setting = User::role('player')->with('user_privacy_settings')->find($request->player_id)->user_privacy_settings;

        if (count($player_privacy_setting) > 0) {
            $check_ispublic = $player_privacy_setting[0]->name;

            if (strtolower($check_ispublic) != 'public') {
                return Helper::apiNotFoundResponse(false, "Player privacy is not public. Can't proceed further", new stdClass());
            } else {

                $exerciseCallback = function ($q4) {
                    $q4->select('exercises.id', 'completion_time', 'video_file', 'player_exercise.created_at');
                    $q4->latest('player_exercise.created_at');
                    $q4->take(3);
                };

                $get_player_details = $this->userModel->getUserExerciseData($exerciseCallback,true)
                    ->orderBy('created_at')
                    ->find($request->player_id);

                /**
                 * Get Users Skills Insights
                 */

                $skills = Skill::Select('id')->get();
                $skills_count = count($skills);
                $skills_count_array = [];
                if ($skills_count > 0) {

                    foreach ($skills as $skill_id) {
                        $skills_count_array[] = $skill_id->id;
                    }

                    $users_skills_points = User::selectRaw('users.id,skills.id,skills.name,player_scores.score,player_scores.skill_id,player_scores.created_at')
                        ->join('player_scores', 'player_scores.user_id', '=', 'users.id')
                        ->join('skills', 'skills.id', '=', 'player_scores.skill_id')
                        ->where('users.id', $request->player_id)
                        ->whereIn('skills.id', $skills_count_array)
                        ->orderBy('player_scores.skill_id', 'asc')
                        ->get();

                    if (count($users_skills_points) > 0) {
                        $get_all_skills_insights_response = $users_skills_points;
                    } else {
                        $get_all_skills_insights_response = [];
                    }

                }

                if ($get_player_details) {
                    $response['get_player_details'] = $get_player_details;
                    $response['get_all_skills_insights'] = $get_all_skills_insights_response;
                    return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
                }
                return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

            }

        } else {
            return Helper::apiNotFoundResponse(false, "Player has not privacy setting. Can't proceed further", new stdClass());
        }

    }


    /**
     * GetPlayerAssignmentDetails
     *
     * Getting player assignment  data in which we will be getting  done exercises details.
     *
     * @queryParam  player_id required player id is required
     * @queryParam  assignment_id required assignment id is required
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Records found successfully!",
     *     "Result": {
     *         "player_assignment_details": {
     *             "id": 2,
     *             "nationality_id": 1,
     *             "first_name": "Fatima",
     *             "middle_name": null,
     *             "last_name": "Sultana",
     *             "profile_picture": "media/users/5f1959393731c1595496761.jpeg",
     *             "date_of_birth": "2020-07-17 05:00:00",
     *             "teams": [
     *                 {
     *                     "id": 2,
     *                     "team_name": "Ajax U16",
     *                     "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     *                     "pivot": {
     *                         "user_id": 2,
     *                         "team_id": 2,
     *                         "created_at": "2020-07-17 21:18:20"
     *                     }
     *                 }
     *             ],
     *             "nationality": {
     *                 "id": 1,
     *                 "name": "Netherlands"
     *             },
     *             "player": {
     *                 "id": 2,
     *                 "user_id": 2,
     *                 "position_id": 2,
     *                 "customary_foot_id": 2,
     *                 "height": 130,
     *                 "weight": 50,
     *                 "jersey_number": "12",
     *                  "positions": [
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
     * ],
     *                 "customary_foot": {
     *                     "id": 2,
     *                     "name": "Right"
     *                 }
     *             },
     *             "leaderboards": {
     *                 "id": 1,
     *                 "user_id": 2,
     *                 "total_score": 1001,
     *                 "position": 10
     *             }
     *         },
     *         "exercises_response": [
     *             {
     *                 "id": 7,
     *                 "title": "10 Cones Insides - Regular Ball",
     *                 "completion_time": 900,
     *                 "thumbnail": "media/player_exercises/yRfZfCU9kyNEdkSYv7PwMOxGIFp9qpOt7gR2somQ.png",
     *                 "video_file": "media/player_exercises/j1GBdaNMGCiHaPt51FRIqIke7AIWtknqK0RoG5hh.mp4",
     *                 "created_at": "2020-07-30T20:09:50.000000Z",
     *                 "level_id": 1,
     *                 "posts": {
     *                     "id": 37,
     *                     "level_id": 1,
     *                     "exercise_id": 7,
     *                     "post_title": "10 Cones Insides - Regular Ball",
     *                     "created_at": "2020-07-29 14:53:21",
     *                     "comments": [
     *                         {
     *                             "id": 88,
     *                             "post_id": 37,
     *                             "assignment_id": null,
     *                             "exercise_id": null,
     *                             "contact_id": 3,
     *                             "comment": "hi",
     *                             "status_id": null,
     *                             "created_at": "2020-07-25 16:48:43",
     *                             "updated_at": "2020-07-25 16:48:43",
     *                             "deleted_at": null,
     *                             "posted_at": "2 weeks ago"
     *                         }
     *                     ]
     *                 }
     *             }
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "player_id": [
     *             "The player id field is required."
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "player_id": [
     *             "The selected player id is invalid."
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "assignment_id": [
     *             "The assignment id field is required."
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "assignment_id": [
     *             "The selected assignment id is invalid."
     *         ]
     *     }
     * }
     *
     *
     * @response 404 {
     *     "Response": false,
     *     "StatusCode": 404,
     *     "Message": "Records not found",
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

    public function get_player_assignment_details(Request $request)
    {

        Validator::make($request->all(), [
            'player_id' => 'required|exists:users,id',
            'assignment_id' => 'required|exists:assignments,id'
        ])->validate();

        $player_id = $request->player_id;
        $assignment_id = $request->assignment_id;

        $current_user = Auth::user();
        $current_user_id = $current_user->id;

        $player = User::role('player')->find($player_id);
        if (!$player) {
            $message = 'Player is not found';
            return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
        }

        $exerciseCallBack = function ($q4) use ($assignment_id, $player_id) {
            $q4->select('exercises.id', 'exercises.title', 'completion_time', 'thumbnail', 'video_file', 'player_exercise.created_at', 'level_id');
            $q4->where('assignment_id', $assignment_id);
            $q4->where('user_id', $player_id);
            $q4->orderBy('created_at', 'desc');
        };

        $get_player_data = $this->userModel->getUserExerciseData($exerciseCallBack,true)
            ->find($player_id);

        if (count($get_player_data->exercises) > 0) {
            $exercises = $get_player_data->exercises->map(function ($ex) use ($player_id) {
                return Helper::getExerciseObject($ex,$player_id,[],false);
            })->reject(function ($aa) {
                return $aa == null;
            });

            $exercises_responses = $this->loadExercises($exercises);

        } else {
            $exercises_responses = 0;
        }

        unset($get_player_data->exercises);
        if ($get_player_data) {
            $response['player_assignment_details'] = $get_player_data;
            $response['exercises_response'] = $exercises_responses;
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }
        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

    }


    /**
     * Player Exercise Listing
     *
     * Getting player exercises  data in which we will be getting  done exercises details.
     *
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "player_details": {
     * "id": 4,
     * "nationality_id": 1,
     * "first_name": "Tariq",
     * "middle_name": null,
     * "last_name": "Sidd",
     * "profile_picture": "media/users/5f7b294249cca1601907010.jpeg",
     * "date_of_birth": "1991-02-03",
     * "leg_distribution": {
     * "percentage": 100,
     * "leg": "Right Leg"
     * },
     * "teams": [
     * {
     * "id": 5,
     * "team_name": "consequatur",
     * "image": "",
     * "pivot": {
     * "user_id": 4,
     * "team_id": 5,
     * "created_at": null
     * }
     * }
     * ],
     * "nationality": {
     * "id": 1,
     * "name": "Afghanistan"
     * },
     * "player": {
     * "id": 4,
     * "user_id": 4,
     * "position_id": 1,
     * "customary_foot_id": 1,
     * "height": 5.8,
     * "weight": 68,
     * "jersey_number": "9",
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
     * ],
     * "customary_foot": {
     * "id": 1,
     * "name": "Left"
     * }
     * },
     * "leaderboards": null
     * },
     * "player_exercises": [
     * {
     * "id": 215,
     * "title": "Jumping jacks",
     * "completion_time": 12.83077099609375,
     * "thumbnail": "media/player_exercises/2eQlOa0dQMamEmvhYI028ibeb3m35w0lKIxjsMk6.jpg",
     * "video_file": "media/player_exercises/Xhvv4NV85DQ9TK1vd6zjEo5mfTvl36YCQWVoxZKj.mp4",
     * "created_at": "2021-06-10T16:11:44.000000Z",
     * "level_id": null,
     * "total_comments": 0,
     * "attempts": 2
     * },
     * {
     * "id": 219,
     * "title": "Squats",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-04-16T07:38:03.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 2
     * },
     * {
     * "id": 224,
     * "title": "Push-ups",
     * "completion_time": 6,
     * "thumbnail": "media/player_exercises/fUkX9QVLZXMHKXQ996mvKPwusMGfBbbP8vZULUbT.jpg",
     * "video_file": "media/player_exercises/qjDA5ItPL2l0Z1URe0tMtTUQJ9QQmQvsogLgOzRx.mp4",
     * "created_at": "2021-04-15T17:53:33.000000Z",
     * "level_id": 1,
     * "total_comments": 35,
     * "attempts": 2
     * },
     * {
     * "id": 223,
     * "title": "Planks",
     * "completion_time": 9,
     * "thumbnail": "media/player_exercises/EnFJ2biI5It9K9zB8PWAb1h0sGdmNmvnGoUvLmfS.jpg",
     * "video_file": "media/player_exercises/fWeV9NZZxPEdZmE9nlrUUFeXW2HZ1QwOFLOOC2iz.mp4",
     * "created_at": "2021-04-15T17:52:27.000000Z",
     * "level_id": 1,
     * "total_comments": 3,
     * "attempts": 2
     * },
     * {
     * "id": 216,
     * "title": "Lunges",
     * "completion_time": 29,
     * "thumbnail": "media/player_exercises/2slxl5Gxkjp7Qpon6DLWm1Lb210FKCJq2fescXa4.jpg",
     * "video_file": "media/player_exercises/AJrlFD7cyAbcBT6Ki6AfCIbX57tIIHPgvyT7BsKE.mp4",
     * "created_at": "2021-03-04T10:42:41.000000Z",
     * "level_id": 1,
     * "total_comments": 1,
     * "attempts": 2
     * },
     * {
     * "id": 67,
     * "title": "Laces push-pull (R)",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-03-04T10:34:28.000000Z",
     * "level_id": 1,
     * "total_comments": 1,
     * "attempts": 2
     * },
     * {
     * "id": 188,
     * "title": "Chip shot (R)",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-03-04T10:20:08.000000Z",
     * "level_id": 1,
     * "total_comments": 1,
     * "attempts": 5
     * },
     * {
     * "id": 69,
     * "title": "Outside push-pull (L/R)",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-03-04T10:19:26.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 1
     * },
     * {
     * "id": 1,
     * "title": "10 Cones dribble (L/R)",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-03-04T10:18:49.000000Z",
     * "level_id": 2,
     * "total_comments": 0,
     * "attempts": 8
     * },
     * {
     * "id": 66,
     * "title": "Laces push-pull (L/R)",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-03-04T10:17:50.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 8
     * },
     * {
     * "id": 108,
     * "title": "One-touch inside pass (L)",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-03-03T13:46:49.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 1
     * },
     * {
     * "id": 70,
     * "title": "Outside push-pull (R)",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-03-03T12:53:32.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 1
     * },
     * {
     * "id": 253,
     * "title": "Russian twists",
     * "completion_time": 29,
     * "thumbnail": "media/player_exercises/q0t3uLP8JN4LVbHnSjlzwO0Y4JhG5OKF9YQiJWQL.jpg",
     * "video_file": "media/player_exercises/k9P9Lx7vzcfoDLkLt40YIWfUCGIyakQxljFWAtBP.mp4",
     * "created_at": "2021-03-01T11:08:36.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 1
     * },
     * {
     * "id": 228,
     * "title": "Bulgarian split squats",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-03-01T11:07:49.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 1
     * },
     * {
     * "id": 263,
     * "title": "5k Run",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-03-01T11:06:49.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 1
     * },
     * {
     * "id": 107,
     * "title": "Roll stops",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-03-01T10:45:59.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 2
     * },
     * {
     * "id": 163,
     * "title": "High-low juggling (L/R)",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-03-01T10:44:52.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 1
     * },
     * {
     * "id": 114,
     * "title": "Two-touch inside pass (L)",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-03-01T10:43:34.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 1
     * },
     * {
     * "id": 239,
     * "title": "High Knees",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-02-25T10:10:22.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 6
     * },
     * {
     * "id": 207,
     * "title": " Low power shot (L)",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-02-05T10:35:47.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 4
     * },
     * {
     * "id": 142,
     * "title": "Behind the leg pass (L)",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2021-01-22T11:52:12.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 1
     * },
     * {
     * "id": 11,
     * "title": "L drag dribble (L/R)",
     * "completion_time": 0,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2020-12-30T08:39:39.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 1
     * },
     * {
     * "id": 3,
     * "title": "10 Cones dribble (L)",
     * "completion_time": 8,
     * "thumbnail": "",
     * "video_file": "",
     * "created_at": "2020-12-30T08:22:46.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 1
     * },
     * {
     * "id": 148,
     * "title": "Laces juggling (L/R)",
     * "completion_time": 12,
     * "thumbnail": "media/player_exercises/vA3rAe8HiGdnu9to816PScFZeI21iEcaGmxB7Qhj.jpeg",
     * "video_file": "media/player_exercises/5fdJicSXP6xINw2RUkujqKYrALh5P56mZnqRZcD9.mp4",
     * "created_at": "2020-12-16T10:29:30.000000Z",
     * "level_id": 1,
     * "total_comments": 2,
     * "attempts": 4
     * },
     * {
     * "id": 89,
     * "title": "Out & in (L/R)",
     * "completion_time": 20,
     * "thumbnail": "media/player_exercises/200zSm8E2VMZlOB8HmjTmvab8ZA28440GgLbTB1A.jpeg",
     * "video_file": "media/player_exercises/Rze59EvThLBHqpJtDVbOPmJpL71NrLhfW6CJLIBd.mp4",
     * "created_at": "2020-11-30T10:20:38.000000Z",
     * "level_id": 1,
     * "total_comments": 0,
     * "attempts": 1
     * }
     * ]
     * }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "player_id": [
     *             "The player id field is required."
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "player_id": [
     *             "The selected player id is invalid."
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "assignment_id": [
     *             "The assignment id field is required."
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "assignment_id": [
     *             "The selected assignment id is invalid."
     *         ]
     *     }
     * }
     *
     *
     * @response 404 {
     *     "Response": false,
     *     "StatusCode": 404,
     *     "Message": "Records not found",
     *     "Result": {}
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     *
     * @queryParam player_id integer required
     * @queryParam assignment_id integer optional
     */
//$obj->ai_vision_video = UserExerciseAiData::where("user_id",$player_id)->where("exercise_id",$ex->id)->first() ?? "";
    public function player_exericse_listing(Request $request)
    {
        Validator::make($request->all(), [
            'player_id' => 'required|exists:users,id',
            "assignment_id" => "integer"
        ])->validate();

        $player_id = $request->player_id;

        $exerciseCallback = function ($q4) use ($player_id, $request) {
            $q4->select('exercises.id', 'exercises.title', 'status_id', 'completion_time', 'thumbnail',
                'video_file', 'player_exercise.created_at', 'level_id');

            if ($request->assignment_id) {
                $q4->where("assignment_id", $request->assignment_id);
            }
            if ($request->view_from == "player_database") {
                $q4->whereNull('assignment_id');
            }

            $q4->where('video_file', '!=', NULL)
                ->where('completion_time', '>', 0)
                ->whereHas('player_scores_users');
            $q4->orderBy('created_at', 'desc');
            $q4->orderBy('level_id', 'desc');
        };

        $get_player_data = $this->userModel->getUserExerciseData($exerciseCallback,true)
            ->find($player_id);

        if (!$get_player_data) {
            return Helper::apiNotFoundResponse(false, "Records Not Found", new stdClass());
        }
        /**
         * GET PLAYER LEG DISTRIBUTION
         */
        $l_percentage = [];
        $leg_percentage = MatchDetails::selectRaw('COUNT(CASE WHEN foot=\'R\' THEN 1 END) AS right_foot,COUNT(CASE WHEN foot=\'L\' THEN 1 END) AS left_foot')->where('user_id', $request->player_id)->first();
        if ($leg_percentage->left_foot || $leg_percentage->right_foot) {
            //get higher one
            $leg_percentage_left = ($leg_percentage->left_foot / ($leg_percentage->left_foot + $leg_percentage->right_foot)) * 100;
            $leg_percentage_right = ($leg_percentage->right_foot / ($leg_percentage->left_foot + $leg_percentage->right_foot)) * 100;
            $l_percentage["percentage"] = $leg_percentage_left > $leg_percentage_right ? $leg_percentage_left : $leg_percentage_right;
            $l_percentage["leg"] = $leg_percentage_left > $leg_percentage_right ? "Left Leg" : "Right Leg";
        }
        if (count($l_percentage) > 0) {
            $l_percentage["percentage"] = round($l_percentage["percentage"], 2) ?? 0;
        }
        /*GET PLAYER LEG DISTRIBUTION*/


        $assignemnt_details = [
            "completion" => [],
            "total_attempts" => [],
            "total_time" => [],
        ];
        if (count($get_player_data->exercises) > 0) {
            $conversation_ids = [(int)$player_id, Auth::user()->id];

            \Session::put('posts', []);

            // GO INTO IF, IF ASSIGNMENT_ID IS GIVEN IN THE REQUEST
            if ($request->assignment_id) {
                $assignemnt_details["completion"]["completed"] = $get_player_data->exercises->where("status_id", 3)->count(); // COUNT ALL WHERE STATUS IS COMPLETED ie 3
                $assignemnt_details["completion"]["out_of"] = $get_player_data->exercises->where("status_id", 5)->count(); // COUNT ALL WHERE STATUS IS IN PROCESS ie 5

                $assignemnt_details["total_attempts"] = $get_player_data->exercises->count(); // GET TOTAL ATTEMPTS OF EXERCISES
                $assignemnt_details["total_time"] = gmdate("H:i:s", $get_player_data->exercises->sum("completion_time")); // GET TOTAL COMPLETION TIME
            }

            $exercises = $get_player_data->exercises->map(function ($ex) use ($l_percentage, $player_id, $conversation_ids) {
                return Helper::getExerciseObject($ex, $player_id, $conversation_ids,true);
            })->reject(function ($aa) {
                return $aa == null;
            });

            $res = [];
            foreach ($exercises as $key => $value) {
                $res[] = $value;
            }

            if (count($res) > 0) {
                $exercises_responses = $res;
            } else {
                $exercises_responses = 0;
            }

        } else {
            $exercises_responses = 0;
        }

        $ex = [];
        if ($exercises_responses != 0) {
            if (count($exercises_responses) > 0) {
                $ex = Helper::exerciseAttemptResponse($exercises_responses);
            }
        }

        $exercises_responses = $ex;
        unset($get_player_data->exercises);

//        $stats = MatchStatType::with(['matches_stats' => function($q) use($player_id){
//            $q->with('match')->where('player_id', $player_id);
//        }])->get();

        if ($get_player_data) {
            $get_player_data->leg_distribution = count($l_percentage) > 0 ? $l_percentage : null;
            $response['player_details'] = $get_player_data;
            $response['player_exercises'] = $exercises_responses;
            if ($request->assignment_id) {
                $response["assignment_details"] = $assignemnt_details;
            }
            //$response['stats'] = $stats;

            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }
        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

    }

    /**
     * Player Exercise Details
     *
     * @response{
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "attempts": {
     * "2020-11-09 15:25:56": [
     * {
     * "id": 98,
     * "title": "Iniesta",
     * "completion_time": 3,
     * "thumbnail": "media/player_exercises/1wvXPmG0xPbYHZ5L8R8AKcb9HrkINiY7Y8Og5Eqi.jpeg",
     * "video_file": "media/player_exercises/ZzrqkWnlQcHsviEB8iqAJCq03VhFPzw4YGSI7WN0.mp4",
     * "created_at": "2020-11-09T15:25:56.000000Z",
     * "level_id": 1,
     * "ai_json": "fsdfsdf",
     * "kpi_json": "",
     * "file_content": "",
     * "ai_json_file_content": ""
     * }
     * ]
     * }
     * }
     * }
     *
     * @queryParam player_id integer required
     * @queryParam exercise_id integer required
     * @queryParam assignment_id integer optional
     */


    public function get_player_exercise_details(Request $request)
    {
        Validator::make($request->all(), [
            'player_id' => 'required|exists:users,id',
            "exercise_id" => "required|integer|exists:exercises,id",
            "assignment_id" => "nullable|integer|exists:assignments,id"
        ])->validate();

        $player_id = $request->player_id;

        $get_player_data = User::role('player')
            ->with([
                'exercises' => function ($q4) use ($player_id, $request) {
                    $q4->select('exercises.id', 'exercises.title', 'status_id', 'completion_time', 'thumbnail', 'video_file', 'player_exercise.created_at', 'level_id','player_exercise.assignment_id', 'player_exercise.ai_json', 'kpi_json', 'html_file_name')
                        ->where('exercise_id', $request->exercise_id);
                        if($request->has('assignment_id')){
                            $q4->where('player_exercise.assignment_id', $request->assignment_id);
                        }
                        $q4->where('player_exercise.assignment_id', $request->assignment_id)
                        ->where('video_file', '!=', NULL)
                        ->where('completion_time', '>', 0)
                        ->whereHas('player_scores_users')
                        ->orderBy('created_at', 'desc');
                }
            ])
            ->find($player_id);

        if (count($get_player_data->exercises) == 0) {
            return Helper::apiNotFoundResponse(false, "No Records Found", new stdClass());
        }

        if (count($get_player_data->exercises) > 0) {
            $conversation_ids = [(int)$player_id, Auth::user()->id];

            \Session::put('posts', []);

            $exercises = $get_player_data->exercises->map(function ($ex) use ($player_id, $conversation_ids) {
                $obj = new stdClass();
                $obj->ai_json = $ex->ai_json ?? "";
                $kpi = '';
                if (!empty($ex->kpi_json)) {
                    $kpi = json_decode(unserialize($ex->kpi_json));
                    foreach ($kpi as $key => $value) {
                        if (strtolower($value) == 'nan') {
                            $kpi[$key] = '';
                        }
                    }
                }
                $obj->kpi_json = $kpi;
                $obj->file_content = $ex->html_file_name ?? "";

                if ($ex->ai_json && Storage::exists($ex->ai_json)) {
                    $obj->ai_json_file_content = Storage::get($ex->ai_json);
                } else {
                    $obj->ai_json_file_content = '';
                }
                $obj->assignment_id = $ex->assignment_id;
                return (object) array_merge_recursive((array) $obj,(array) Helper::getExerciseObject($ex,$player_id,$conversation_ids,true));
            })->reject(function ($aa) {
                return $aa == null;
            });

            $exercises_responses = $this->loadExercises($exercises);

        } else {
            $exercises_responses = 0;
        }
        unset($get_player_data->exercises);
        if ($exercises_responses != 0) {
            $AllDates = [];
            if (count($exercises_responses) > 0) {
                foreach ($exercises_responses as $key => $value) {
                    if (!in_array(date("Y-m-d H:i:s", strtotime($value->created_at)), $AllDates)) {
                        $AllDates[] = date("Y-m-d H:i:s", strtotime($value->created_at));
                    }
                }
            }
            $allExercisese = [];
            if (count($AllDates) > 0) {
                usort($AllDates, [$this, "date_sort"]);
                foreach ($AllDates as $date) {
                    foreach ($exercises_responses as $value) {
                        if ($date == date("Y-m-d H:i:s", strtotime($value->created_at))) {
                            $allExercisese[$date][] = $value;
                        } else {
                            continue;
                        }
                    }
                }
            }
        }
//        $stats = MatchStatType::with(['matches_stats' => function($q) use($player_id){
//            $q->with('match')->where('player_id', $player_id);
//        }])->get();

        if ($get_player_data) {
            $response['attempts'] = $allExercisese;
            //$response['stats'] = $stats;

            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }
        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

    }

    private function date_sort($a, $b)
    {
        return strtotime($a) - strtotime($b);
    }

    /**
     * PlayExerciseVideo
     *
     * playing exercise video of player has uploaded it. it will get record from player_exercise table
     *
     * @queryParam  player_id required player id is required
     * @queryParam  exercise_id required exercise id is required
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Records found successfully!",
     *     "Result": {
     *         "player_exercises_details": {
     *             "id": 2,
     *             "nationality_id": 1,
     *             "first_name": "Fatima",
     *             "middle_name": null,
     *             "last_name": "Sultana",
     *             "profile_picture": "media/users/5f1959393731c1595496761.jpeg",
     *             "date_of_birth": "2020-07-17 05:00:00",
     *             "teams": [
     *                 {
     *                     "id": 2,
     *                     "team_name": "Ajax U16",
     *                     "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     *                     "pivot": {
     *                         "user_id": 2,
     *                         "team_id": 2,
     *                         "created_at": "2020-07-17 21:18:20"
     *                     }
     *                 }
     *             ],
     *             "nationality": {
     *                 "id": 1,
     *                 "name": "Netherlands"
     *             },
     *             "player": {
     *                 "id": 2,
     *                 "user_id": 2,
     *                 "position_id": 2,
     *                 "customary_foot_id": 2,
     *                 "height": 130,
     *                 "weight": 50,
     *                 "jersey_number": "12",
     *                  "positions": [
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
     * ],
     *                 "customary_foot": {
     *                     "id": 2,
     *                     "name": "Right"
     *                 }
     *             },
     *             "leaderboards": {
     *                 "id": 1,
     *                 "user_id": 2,
     *                 "total_score": 1001,
     *                 "position": 10
     *             },
     *             "exercises": [
     *                 {
     *                     "id": 2,
     *                     "title": "20 Cones ",
     *                     "completion_time": 890,
     *                     "thumbnail": "media/player_exercises/yRfZfCU9kyNEdkSYv7PwMOxGIFp9qpOt7gR2somQ.png",
     *                     "video_file": "media/player_exercises/j1GBdaNMGCiHaPt51FRIqIke7AIWtknqK0RoG5hh.mp4",
     *                     "created_at": "2020-07-29 14:48:58",
     *                     "pivot": {
     *                         "user_id": 2,
     *                         "exercise_id": 2,
     *                         "status_id": 3
     *                     },
     *                     "posts": [
     *                         {
     *                             "id": 36,
     *                             "exercise_id": 2,
     *                             "post_title": "20 Cones ",
     *                             "created_at": "2020-07-29 14:53:21",
     *                             "comments": [
     *                                 {
     *                                     "id": 89,
     *                                     "post_id": 36,
     *                                     "assignment_id": null,
     *                                     "exercise_id": null,
     *                                     "contact_id": 3,
     *                                     "comment": "good",
     *                                     "status_id": null,
     *                                     "created_at": "2020-07-25 16:53:09",
     *                                     "updated_at": "2020-07-25 16:53:09",
     *                                     "deleted_at": null,
     *                                     "posted_at": "1 week ago"
     *                                 },
     *                                 {
     *                                     "id": 90,
     *                                     "post_id": 36,
     *                                     "assignment_id": null,
     *                                     "exercise_id": null,
     *                                     "contact_id": 3,
     *                                     "comment": "working",
     *                                     "status_id": null,
     *                                     "created_at": "2020-07-25 16:53:28",
     *                                     "updated_at": "2020-07-25 16:53:28",
     *                                     "deleted_at": null,
     *                                     "posted_at": "1 week ago"
     *                                 },
     *                                 {
     *                                     "id": 91,
     *                                     "post_id": 36,
     *                                     "assignment_id": null,
     *                                     "exercise_id": null,
     *                                     "contact_id": 11,
     *                                     "comment": "ok",
     *                                     "status_id": null,
     *                                     "created_at": "2020-07-25 19:36:16",
     *                                     "updated_at": "2020-07-25 19:36:16",
     *                                     "deleted_at": null,
     *                                     "posted_at": "1 week ago"
     *                                 }
     *                             ]
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }
     *     }
     * }
     *
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "player_id": [
     *             "The player id field is required."
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "player_id": [
     *             "The selected player id is invalid."
     *         ]
     *     }
     * }
     *
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "player_id": [
     *             "The exercise id field is required."
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "player_id": [
     *             "The selected exercise id is invalid."
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "player_id": [
     *             "The player id field is required."
     *         ],
     *         "exercise_id": [
     *             "The exercise id field is required."
     *         ]
     *     }
     * }
     *
     *
     *
     * @response 404 {
     *     "Response": false,
     *     "StatusCode": 404,
     *     "Message": "Records not found",
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
    public function play_exercise_video(Request $request)
    {
        Validator::make($request->all(), [
            'player_id' => 'required|exists:users,id',
            'exercise_id' => 'required|exists:exercises,id',
        ])->validate();

        $player_id = $request->player_id;
        $exercise_id = $request->exercise_id;
        $current_user = Auth::user();
        $current_user_id = $current_user->id;

        //return 'Exercise Id : '. $exercise_id.'  ,Player Id: '.$player_id;

        $player = User::role('player')->find($player_id);
        if (!$player) {
            $message = 'Player is not found';
            return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
        }

        $exerciseCallback = function ($q4) use ($exercise_id, $player_id) {
            $q4->select('exercises.id', 'exercises.title', 'completion_time', 'thumbnail', 'video_file', 'player_exercise.created_at');
            $q4->with(['posts' => function ($qq) use ($player_id) {

                $qq->select('posts.id', 'posts.exercise_id', 'posts.post_title', 'posts.created_at');
                $qq->where('posts.author_id', $player_id);
                $qq->with('comments');
                //$qq->whereHas('comments');
                $qq->orderBy("created_at", "desc");
                $qq->first();

            }]);
            $q4->whereHas('posts', function ($qa) use ($player_id) {
                $qa->where('author_id', $player_id);
            });
            $q4->where('user_id', $player_id);
            $q4->where('exercise_id', $exercise_id);
            $q4->orderBy('created_at', 'desc');

        };

        $get_player_data = $this->userModel->getUserExerciseData($exerciseCallback,true)
            ->find($player_id);

        if ($get_player_data) {
            $response['player_exercises_details'] = $get_player_data;
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }
        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

    }

    /**
     * Get Statistics Top Records
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "top_records": {
     * "kick_strength": {
     * "value": 257,
     * "min": 40,
     * "max": 257,
     * "avg": 0
     * },
     * "max_speed": {
     * "value": 83,
     * "min": 39,
     * "max": 83,
     * "avg": 0
     * },
     * "leg_distribution": {
     * "left_foot": 0.05,
     * "right_foot": 99.95,
     * "avg": 0
     * }
     * }
     * }
     * }
     * @bodyParam player_id string required
     * @bodyParam from string date Y-m-d
     * @bodyParam to string date Y-m-d
     * @return JsonResponse
     */


    public function getPlayerStatisticsTopRecords(PlayerStatisticsRequest $request)
    {
        $response = MatchStat::topRecords($request);
        return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
    }


    /**
     * Session Metrics
     *
     * @queryParam  player_id required player id is required
     * @queryParam  from string date Y-m-d
     * @queryParam  to string date Y-m-d
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "session_metrics": {
     * "ball_kick": {
     * "value": 463,
     * "min": 0,
     * "max": 463,
     * "avg": 0
     * },
     * "total_distance": {
     * "value": 3757,
     * "min": 0,
     * "max": 3757,
     * "avg": 0
     * },
     * "received_impacts": {
     * "value": 1437,
     * "min": 0,
     * "max": 1437,
     * "avg": 0
     * },
     * "heart_rate": [
     * {
     * "hr": 72,
     * "event_ts": "2020-10-15 17:17:04"
     * },
     * {
     * "hr": 72,
     * "event_ts": "2020-10-15 17:25:30"
     * },
     * {
     * "hr": 72,
     * "event_ts": "2020-10-15 17:25:31"
     * },
     * {
     * "hr": 72,
     * "event_ts": "2020-10-15 17:25:32"
     * },
     * {
     * "hr": 72,
     * "event_ts": "2020-10-15 17:25:33"
     * }
     * ]
     * }
     * }
     * }
     * */

    public function getPlayerStatisticsSessionMetrics(Request $request)
    {
        $from = isset($request->from) ? $request->from : '1970-01-01';
        $to = isset($request->to) ? $request->to : Carbon::today()->addDay();
        $response = MatchStat::getSessionMetrics($request);
        return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
    }


    /**
     * Average Speed Graph API
     *
     * @queryParam  player_id required player id is required
     * @queryParam  from string date Y-m-d send only when duration is null
     * @queryParam  to string date Y-m-d send only when duration is null
     * @queryParam  duration string day|month|3-months|6-months|year|all (select when to and from params are null)
     * @queryParam  filter array optional [height,weight,age_group,position,player2]
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "labels": [
     * "2012-01-30",
     * "2020-07-09",
     * "2020-07-28",
     * "2020-07-24",
     * "2020-07-23",
     * "2020-07-16",
     * "2020-07-15",
     * "2020-07-14",
     * "2020-07-13",
     * "2020-07-07",
     * "2020-07-30",
     * "2020-07-06",
     * "2020-07-02",
     * "2020-07-01",
     * "2020-06-26",
     * "2020-06-25",
     * "2020-06-24",
     * "2020-06-23",
     * "2020-06-22",
     * "2020-07-29",
     * "2020-08-03",
     * "2017-08-26",
     * "2020-08-15",
     * "2020-10-07",
     * "2020-10-03",
     * "2020-08-26",
     * "2020-08-25",
     * "2020-08-21",
     * "2020-08-20",
     * "2020-08-17",
     * "2020-08-14",
     * "2020-08-04",
     * "2020-08-13",
     * "2020-08-12",
     * "2020-08-11",
     * "2020-08-10",
     * "2020-08-09",
     * "2020-08-07",
     * "2020-08-06",
     * "2020-08-05",
     * "2019-12-16",
     * "2012-01-30",
     * "2017-08-26",
     * "2020-07-07",
     * "2020-07-24",
     * "2020-07-23",
     * "2020-07-16",
     * "2020-07-15",
     * "2020-07-14",
     * "2020-07-13",
     * "2020-07-09",
     * "2020-07-06",
     * "2020-07-29",
     * "2020-07-02",
     * "2020-07-01",
     * "2020-06-26",
     * "2020-06-25",
     * "2020-06-24",
     * "2020-06-23",
     * "2020-06-22",
     * "2019-12-16",
     * "2020-07-28",
     * "2020-07-30",
     * "2020-11-11",
     * "2020-08-14",
     * "2020-10-07",
     * "2020-10-03",
     * "2020-08-26",
     * "2020-08-25",
     * "2020-08-21",
     * "2020-08-20",
     * "2020-08-17",
     * "2020-08-15",
     * "2020-08-13",
     * "2020-08-03",
     * "2020-08-12",
     * "2020-08-11",
     * "2020-08-10",
     * "2020-08-09",
     * "2020-08-07",
     * "2020-08-06",
     * "2020-08-05",
     * "2020-08-04",
     * "2020-11-11"
     * ],
     * "data_1": [
     * 0,
     * 50,
     * 31.571428571428573,
     * 48,
     * 52.75,
     * 44.8,
     * 83.5,
     * 34.75,
     * 58.90909090909091,
     * 48.94444444444444,
     * 68.5,
     * 45.5,
     * 53.35,
     * 51.357142857142854,
     * 43.25,
     * 49.8,
     * 49,
     * 51.43939393939394,
     * 46.357142857142854,
     * 45.333333333333336,
     * 52.166666666666664,
     * 60.65,
     * 49.75,
     * 48.7,
     * 31,
     * 51.296052631578945,
     * 49.94444444444444,
     * 50.43181818181818,
     * 47.964285714285715,
     * 53,
     * 56.59375,
     * 49.75,
     * 48.333333333333336,
     * 57.291666666666664,
     * 49,
     * 61.5,
     * 47.45652173913044,
     * 44,
     * 39.54,
     * 55.3,
     * 33.25,
     * 88,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 52.0507
     * ],
     * "data_2": [
     * 88,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 60.65,
     * 48.94444444444444,
     * 48,
     * 52.75,
     * 44.8,
     * 83.5,
     * 34.75,
     * 58.90909090909091,
     * 50,
     * 45.5,
     * 45.333333333333336,
     * 53.35,
     * 51.357142857142854,
     * 43.25,
     * 49.8,
     * 49,
     * 51.43939393939394,
     * 46.357142857142854,
     * 33.25,
     * 31.571428571428573,
     * 68.5,
     * 52.0507,
     * 56.59375,
     * 48.7,
     * 31,
     * 51.296052631578945,
     * 49.94444444444444,
     * 50.43181818181818,
     * 47.964285714285715,
     * 53,
     * 49.75,
     * 48.333333333333336,
     * 52.166666666666664,
     * 57.291666666666664,
     * 49,
     * 61.5,
     * 47.45652173913044,
     * 44,
     * 39.54,
     * 55.3,
     * 49.75,
     * 0
     * ]
     * }
     * }
     * */


    public function getPlayerStatisticsAverageSpeed(PlayerStatisticsRequest $request)
    {
        $response = $this->getPlayerStatistices("avg_speed",$request);
        return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
    }

    /**
     * Average Speed Zone API
     *
     * @queryParam  player_id required player id is required
     * @queryParam  from string date Y-m-d send only when duration is null
     * @queryParam  to string date Y-m-d send only when duration is null
     * @queryParam  duration string day|month|3-months|6-months|year|all (select when to and from params are null)
     * @queryParam  filter array optional [height,weight,age_group,position,player2]
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "labels": [
     * "SPEED_WALKING"
     * ],
     * "data_1": [
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0,
     * 0
     * ],
     * "data_2": [
     * 54.4,
     * 53.25,
     * 38,
     * 49.38028169014085,
     * 35.77777777777778,
     * 37.76190476190476,
     * 48.53846153846154,
     * 56.6,
     * 59.785714285714285,
     * 47.857142857142854,
     * 43.54545454545455,
     * 46.44444444444444,
     * 59,
     * 54.6875,
     * 46.06666666666667,
     * 45.19047619047619,
     * 42.75,
     * 59.333333333333336,
     * 96,
     * 45,
     * 49.75,
     * 12.5,
     * 43,
     * 38.4,
     * 95,
     * 31,
     * 42.2,
     * 62.833333333333336,
     * 44,
     * 57,
     * 35,
     * 39.875,
     * 0,
     * 54.4,
     * 53.25,
     * 38,
     * 49.38028169014085,
     * 35.77777777777778,
     * 37.76190476190476,
     * 48.53846153846154,
     * 56.6,
     * 59.785714285714285,
     * 47.857142857142854,
     * 43.54545454545455,
     * 46.44444444444444,
     * 59,
     * 54.6875,
     * 46.06666666666f667,
     * 45.19047619047619,
     * 42.75,
     * 59.333333333333336,
     * 96,
     * 45,
     * 49.75,
     * 12.5,
     * 43,
     * 38.4,
     * 95,
     * 31,
     * 42.2,
     * 62.833333333333336,
     * 44,
     * 57,
     * 35,
     * 39.875,
     * 0,
     * 54.4,
     * 53.25,
     * 38,
     * 49.38028169014085,
     * 35.77777777777778,
     * 37.76190476190476,
     * 48.53846153846154,
     * 56.6,
     * 59.785714285714285,
     * 47.857142857142854,
     * 43.54545454545455,
     * 46.44444444444444,
     * 59,
     * 54.6875,
     * 46.06666666666667,
     * 45.19047619047619,
     * 42.75,
     * 59.333333333333336,
     * 96,
     * 45,
     * 49.75,
     * 12.5,
     * 43,
     * 38.4,
     * 95,
     * 31,
     * 42.2,
     * 62.833333333333336,
     * 44,
     * 57,
     * 35,
     * 39.875,
     * 0
     * ]
     * }
     * }
     * */
    public function getPlayerStatisticsSpeedZone(PlayerStatisticsRequest $request)
    {
        $response = $this->getPlayerStatistices("speed",$request);
        return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
    }


    public function getPlayerStatisticsHeartRate(PlayerStatisticsRequest $request)
    {
        $response = $this->getPlayerStatistices("heart_rate",$request);

        if (count($response)) {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }
        return Helper::apiSuccessResponse(false, 'Records not found!', $response);

    }

    private function getPlayerStatistices($graph_type,$request){
        return (new User())->getGraph($graph_type,$request);
    }


    /**
     * Get Filters
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "age_groups": [
     * "23",
     * "211",
     * "43"
     * ],
     * "positions": [
     * {
     * "id": 1,
     * "name": "Left Back"
     * },
     * {
     * "id": 2,
     * "name": "Right Back"
     * },
     * {
     * "id": 3,
     * "name": "Goal Keeper"
     * },
     * {
     * "id": 4,
     * "name": "Center Back"
     * },
     * {
     * "id": 5,
     * "name": "Center Midfield"
     * },
     * {
     * "id": 6,
     * "name": "Left Midfield"
     * },
     * {
     * "id": 7,
     * "name": "Left Wing"
     * },
     * {
     * "id": 8,
     * "name": "Right midfield"
     * },
     * {
     * "id": 9,
     * "name": "Right wing"
     * },
     * {
     * "id": 10,
     * "name": "Striker "
     * }
     * ],
     * "players": [
     * {
     * "id": 128,
     * "first_name": "baran",
     * "middle_name": "''",
     * "last_name": "erdogan"
     * },
     * {
     * "id": 131,
     * "first_name": "att",
     * "middle_name": "''",
     * "last_name": "att"
     * },
     * {
     * "id": 132,
     * "first_name": null,
     * "middle_name": "''",
     * "last_name": null
     * },
     * {
     * "id": 134,
     * "first_name": null,
     * "middle_name": "''",
     * "last_name": null
     * },
     * {
     * "id": 135,
     * "first_name": null,
     * "middle_name": "''",
     * "last_name": null
     * },
     * {
     * "id": 136,
     * "first_name": "erik",
     * "middle_name": "''",
     * "last_name": "eijgenstein"
     * },
     * {
     * "id": 137,
     * "first_name": null,
     * "middle_name": "''",
     * "last_name": null
     * },
     * {
     * "id": 138,
     * "first_name": "Baran",
     * "middle_name": "''",
     * "last_name": "Erdogan"
     * },
     * {
     * "id": 140,
     * "first_name": "Christiano",
     * "middle_name": "''",
     * "last_name": "Ronaldo"
     * },
     * {
     * "id": 141,
     * "first_name": "Fahad",
     * "middle_name": "''",
     * "last_name": "Paapi"
     * },
     * {
     * "id": 142,
     * "first_name": "Fami",
     * "middle_name": "''",
     * "last_name": "sultana"
     * },
     * {
     * "id": 143,
     * "first_name": "Bram",
     * "middle_name": "''",
     * "last_name": "Vijgen"
     * },
     * {
     * "id": 150,
     * "first_name": null,
     * "middle_name": "''",
     * "last_name": null
     * }
     * ]
     * }
     * }
     * @bodyParam team_id string optional to get players of specific team
     * @return JsonResponse
     */

    public function getFilters(Request $request)
    {
        try{
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $this->userModel->filters($request));
        }catch (\Exception $ex){
            return Helper::apiErrorResponse(false,"Something Went Wrong",new stdClass());
        }

    }

}
