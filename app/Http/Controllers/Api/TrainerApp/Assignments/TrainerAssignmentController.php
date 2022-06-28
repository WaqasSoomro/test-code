<?php

namespace App\Http\Controllers\Api\TrainerApp\Assignments;
use App\Http\Controllers\Controller;

use App\{PlayerAssignment, Post, Status, User, Assignment, Team, Club};
use App\Helpers\Helper;
use Illuminate\Http\{
    JsonResponse,
    Request
};
use Illuminate\Support\Facades\{
    Auth,
    Validator,
    DB
};
use stdClass;



/**
 * @group TrainerApp / Skill Assignment
 * 
 * APIs For Trainers Skill Assignment
 */
class TrainerAssignmentController extends Controller
{

    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Get Assignments
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": [
     * {
     * "id": 14,
     * "trainer_user_id": 2,
     * "difficulty_level": "beginner",
     * "title": "assignment 2",
     * "description": "assignment 1assignment 1assignment 1",
     * "image": "media/assignments/MBoP9g0x1WmXCf7EZMhXfiUmMg22ytFkETjkrGA8.jpeg",
     * "deadline": "2020-07-22 00:00:00",
     * "created_at": "2020-07-20 21:01:45",
     * "updated_at": "2020-07-20 21:01:45",
     * "deleted_at": null,
     * "exercises_count": 2,
     * "players_count": 1,
     * "player_completed_count": 0
     * },
     * {
     * "id": 13,
     * "trainer_user_id": 2,
     * "difficulty_level": "intermediate",
     * "title": "assignment 1",
     * "description": "assignment 1assignment 1assignment 1",
     * "image": "media/assignments/BRrvZlfyiGOWSLvyrOJpqTw8gVaXZQFF57eNIwRc.jpeg",
     * "deadline": "2020-07-24 00:00:00",
     * "created_at": "2020-07-20 21:01:28",
     * "updated_at": "2020-07-20 21:01:28",
     * "deleted_at": null,
     * "exercises_count": 0,
     * "players_count": 1,
     * "player_completed_count": 0
     * }
     * ]
     * }
     *
     * @queryParam clubId required integer
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            "clubId" => "required|integer|exists:clubs,id"
        ]);

        $user_exist_in_club = DB::table("club_trainers")
        ->where("trainer_user_id", auth()->user()->id)
        ->where("club_id",$request->clubId)
        ->first();

        if (!$user_exist_in_club)
        {
            $user_exist_in_club = Club::select('id')
            ->where('owner_id', auth()->user()->id)
            ->first();

            if (!$user_exist_in_club)
            {
                return Helper::apiNotFoundResponse(
                    false,
                    "Trainer Not In Club",
                    new stdClass()
                );
            }
        }

        $teams = Team::
        // WHERE TRAINER IS IN THAT TEAM
        whereHas("trainers",function ($trainer)
        {
            $trainer->where("trainer_user_id",auth()->user()->id);
        })
            // WHERE TEAM IS IN THE REQUEST CLUB
        ->whereHas("clubs",function ($club)
        use($request)
        {
            $club->where("club_id",$request->clubId);
            // WITH ALL THE PLAYERS IN THE TEAM
        })->with('players')
            ->get();

        // GET ALL THE PLAYERS ID FROM TEAMS
        $players = [];
        foreach ($teams as $team)
        {
            $players[] = $team->players->pluck("id")->toArray();
        }
         $players = array_flatten($players);

        if (count($players) == 0)
        {
            return Helper::apiNotFoundResponse(
                false,
                "Records Not Found",
                new stdClass()
            );
        }

        $status = Status::where('name', 'completed')->first();

        $assignments = Assignment::where('trainer_user_id', Auth::user()->id)
        ->whereHas('players',function ($player)
            use($players)
            {
                $player->whereIn("player_user_id",$players);
            })
            ->withCount('exercises')
            ->withCount('players')
            ->withCount(['players as player_completed_count' => function ($q) use ($status) {
                $q->where('player_assignments.status_id', $status->id ?? 0);
            }])->latest()->get();

        if (count($assignments) > 0) {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $assignments);
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', []);
    }

    
    /**
     * Get Assignment Detail
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Assignment found",
     * "Result": {
     * "assignment_stats": {
     * "player_assigned": 1,
     * "player_completed": 0,
     * "overall_time": 0,
     * "assignment_level": "beginner",
     * "privacy_type": "public"
     * },
     * "players_details": [
     * {
     * "first_name": null,
     * "last_name": null,
     * "profile_picture": null,
     * "positions": [
            {
                "id": 3,
                "name": "Goal Keeper",
                "lines": 2,
                "pivot": {
                    "player_id": 1,
                    "position_id": 3
                },
                "line": {
                    "id": 2,
                    "name": "GoalKeepers"
                }
            }
        ],
     * "team_name": "Team 1",
     * "total_exercises": 2,
     * "completed_exercises": 1,
     * "total_comments": 0
     * }
     * ]
     * }
     * }
     *
     * @urlParam assignment_id required
     * @return JsonResponse
     */
  
    public function detail(Request $request)
    {
        validator($request->all(), [
            'assignment_id' => 'required'
        ])->validate();

        $assigment_detail=Assignment::select('assignments.id', 'assignments.title', 'assignments.difficulty_level','assignments.description','assignments.image')
            ->where('assignments.id',$request->assignment_id)->first();

        $response = (new Assignment())->getAssignmentDetail($request,['assignments.id', 'assignments.title', 'assignments.difficulty_level']);

        if(!$response['status']){
            return Helper::apiNotFoundResponse(false, $response['msg'], new stdClass());
        }

        $response = $response['data'];
        $response['assignment_detail'] = $assigment_detail;

        return Helper::apiSuccessResponse(true, 'Assignment found', $response);
    }

    /**
     * Delete Assignment
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Record deleted successfully!",
     * "Result": {}
     * }
     *
     * @bodyParam assignment_id string required
     * @return JsonResponse
     */
    public function delete_assignment(Request $request)
    {
        validator($request->all(), [
            'assignment_id' => 'required|exists:assignments,id',
        ])->validate();

        return DB::transaction(function () use($request){
            try
            {
                $assignment = Assignment::where('id', $request->assignment_id)->where('trainer_user_id', Auth::user()->id)
                    ->first();

                if (!$assignment) {
                    return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());
                }

                PlayerAssignment::whereAssignmentId($request->assignment_id)->delete();

                $assignment->delete();

                return Helper::apiSuccessResponse(true, 'Record deleted successfully', new stdClass());
            }
            catch (\Error $er)
            {
                return Helper::apiErrorResponse(false,"Something Went Wrong",new stdClass());
            }
            catch (\Exception $ex)
            {
                return Helper::apiErrorResponse(false,"Something Went Wrong", new stdClass());
            }
        });

    }

    
    /**
     * Get Player Assignment Details
     *
     * Getting player assignment  data in which we will be getting done exercises details.
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
                            {
                                "id": 3,
                                "name": "Goal Keeper",
                                "lines": 2,
                                "pivot": {
                                    "player_id": 1,
                                    "position_id": 3
                                },
                                "line": {
                                    "id": 2,
                                    "name": "GoalKeepers"
                                }
                            }
                        ],
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
     *             },
     *             {
     *                 "id": 2,
     *                 "title": "20 Cones ",
     *                 "completion_time": 890,
     *                 "thumbnail": "media/player_exercises/yRfZfCU9kyNEdkSYv7PwMOxGIFp9qpOt7gR2somQ.png",
     *                 "video_file": "media/player_exercises/j1GBdaNMGCiHaPt51FRIqIke7AIWtknqK0RoG5hh.mp4",
     *                 "created_at": "2020-07-29T14:48:58.000000Z",
     *                 "level_id": 2,
     *                 "posts": {
     *                     "id": 36,
     *                     "level_id": 2,
     *                     "exercise_id": 2,
     *                     "post_title": "20 Cones ",
     *                     "created_at": "2020-07-29 14:53:21",
     *                     "comments": [
     *                         {
     *                             "id": 89,
     *                             "post_id": 36,
     *                             "assignment_id": null,
     *                             "exercise_id": null,
     *                             "contact_id": 3,
     *                             "comment": "good",
     *                             "status_id": null,
     *                             "created_at": "2020-07-25 16:53:09",
     *                             "updated_at": "2020-07-25 16:53:09",
     *                             "deleted_at": null,
     *                             "posted_at": "2 weeks ago"
     *                         },
     *                         {
     *                             "id": 90,
     *                             "post_id": 36,
     *                             "assignment_id": null,
     *                             "exercise_id": null,
     *                             "contact_id": 3,
     *                             "comment": "working",
     *                             "status_id": null,
     *                             "created_at": "2020-07-25 16:53:28",
     *                             "updated_at": "2020-07-25 16:53:28",
     *                             "deleted_at": null,
     *                             "posted_at": "2 weeks ago"
     *                         },
     *                         {
     *                             "id": 91,
     *                             "post_id": 36,
     *                             "assignment_id": null,
     *                             "exercise_id": null,
     *                             "contact_id": 11,
     *                             "comment": "ok",
     *                             "status_id": null,
     *                             "created_at": "2020-07-25 19:36:16",
     *                             "updated_at": "2020-07-25 19:36:16",
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
        $current_user_id = $current_user -> id;
        //$current_user_id = 1;
        
        

        $player = $this->userModel->find($player_id);
        if (!$player) {
            $message = 'Player is not found';
            return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
        }

        $exerciseCallBack = function ($q4) use ($assignment_id, $player_id) {
            $q4->select('exercises.id', 'exercises.title', 'completion_time', 'thumbnail', 'video_file', 'player_exercise.created_at', 'level_id')
                ->where('assignment_id', $assignment_id)
                ->where('user_id', $player_id)
                ->orderBy('created_at', 'desc');
        };

        $get_player_data = $this->userModel->getUserExerciseData($exerciseCallBack,true)
            ->withCount(["exercises as completed_exercise"=>function($q5) use($assignment_id){
                $q5->where("assignment_id",$assignment_id)->where("status_id",3);
            }])
            ->withCount(["exercises as total_exercise"=>function ($q6) use($assignment_id){
                $q6->where("assignment_id",$assignment_id);
            }])
            ->find($player_id);

        if (count($get_player_data->exercises) > 0) {

            $exercises = $get_player_data->exercises->map(function ($ex) use ($player_id) {
                $obj = Helper::getAssignmentObject($ex);
                $check_post = Helper::checkPost($ex,$player_id);
                if ($check_post['status']) {
                    $obj->posts = $check_post['post'];
                }

                return $obj;

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
                $exercises_responses = [];
            }

        } else {
            $exercises_responses = [];
        }
        $ex = [];
        if (count($exercises_responses) > 0){
            $ex = Helper::exerciseAttemptResponse($exercises_responses);
        }
        $exercises_responses = $ex;
        unset($get_player_data->exercises);
        if ($get_player_data) {
            $response['player_assignment_details'] = $get_player_data;
            $response['exercises_response'] = $exercises_responses;
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }
        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

    }


     /**
     * Play Exercise Video
     *
     * playing exercise video of player has uploaded it. it will get record from player_exercise table
     *
     * @queryParam  player_id required player id is required
     * @queryParam  assignment_id required assignment id is required
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
     *                 "positions": [
                            {
                                "id": 3,
                                "name": "Goal Keeper",
                                "lines": 2,
                                "pivot": {
                                    "player_id": 1,
                                    "position_id": 3
                                },
                                "line": {
                                    "id": 2,
                                    "name": "GoalKeepers"
                                }
                            }
                        ],
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
            'assignment_id' => 'required|exists:assignments,id',

        ])->validate();

        $player_id = $request->player_id;
        $exercise_id = $request->exercise_id;
        $assignment_id = $request->assignment_id;
        $current_user = Auth::user();
        $current_user_id = $current_user->id;
        

        $player = $this->userModel->find($player_id);
        if (!$player) {
            $message = 'Player is not found';
            return Helper::apiUnAuthenticatedResponse(false, $message, new stdClass());
        }

        $conversation_ids = [(int)$player_id];

        $exerciseCallBack = function ($q4) use ($exercise_id,$assignment_id, $player_id,$conversation_ids) {
            $q4->select('exercises.id', 'exercises.title', 'completion_time', 'thumbnail', 'video_file', 'player_exercise.created_at');

            $q4->with(['posts' => function ($qq) use ($player_id,$conversation_ids) {
                $qq->select('id', 'level_id', 'exercise_id', 'post_title', 'created_at');
                $qq->where('posts.author_id', $player_id);
                $qq->with(['comments' => function ($q) use ($conversation_ids) {
                    $q->whereIn('contact_id', $conversation_ids)->latest();
                }]);
                $qq->with(['comments.replies' => function($q1) {
                    $q1->orderBy('id', 'desc')->with('contact:id,first_name,last_name,profile_picture');
                }]);
                $qq->with([
                    'comments.contact' => function ($query)
                    {
                        $query->select('id', 'first_name', 'last_name', 'profile_picture');
                    }
                ]);
                $qq->orderBy("created_at", "desc");
                $qq->first();
            }]);
            $q4->where('user_id', $player_id);
            $q4->where('assignment_id', $assignment_id);
            $q4->where('exercise_id', $exercise_id);
            $q4->orderBy('created_at', 'desc')->first();

        };

        $get_player_data = $this->userModel->getUserExerciseData($exerciseCallBack,true)->find($player_id);
            
        if ($get_player_data) {
            $response['player_exercises_details'] = $get_player_data;
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }
        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

    }


    
    /**
     * Get Player Details
     *
     * All player data with addition to last three exercies data which were completed by player.(only fetch data from players which have privacy setting as public)
     *
     * @queryParam  player_id required player id is required
     *
     * @response {
     *  "Response": true,
     *  "StatusCode": 200,
     *  "Message": "Records found successfully!",
     *  "Result": {
     *     "get_player_details": {
     *       "id": 1,
     *       "nationality_id": 1,
     *       "first_name": "muhammad.",
     *       "middle_name": null,
     *       "last_name": "shahzaib",
     *       "profile_picture": "media/users/5fa27263a93271604481635.jpeg",
     *      "date_of_birth": "1995-02-05",
     *       "teams": [
     *           {
     *               "id": 3,
     *               "team_name": "ManUtd U18",
     *               "image": "https://lh3.googleusercontent.com/KNyKMfQqqVcLYAROYJ6KPW7nqmyMMcuc7npdzuzYI9KXhnZDJ3Wkfqy_apcQTDgq2QlNp9LzqQly06N5qsNxUOLT",
     *               "pivot": {
     *                   "user_id": 1,
     *                   "team_id": 3,
     *                   "created_at": "2020-07-24 10:44:46"
     *               }
     *           },
     *           {
     *               "id": 4,
     *               "team_name": "test team",
     *               "image": "https://lh3.googleusercontent.com/KNyKMfQqqVcLYAROYJ6KPW7nqmyMMcuc7npdzuzYI9KXhnZDJ3Wkfqy_apcQTDgq2QlNp9LzqQly06N5qsNxUOLT",
     *               "pivot": {
     *                   "user_id": 1,
     *                   "team_id": 4,
     *                   "created_at": null
     *               }
     *           }
     *       ],
     *       "nationality": {
     *           "id": 1,
     *           "name": "Afghanistan",
     *           "iso": "AF",
     *           "flag": "https://flagcdn.com/w160/af.png"
     *       },
     *       "player": {
     *           "id": 1,
     *           "user_id": 1,
     *           "position_id": 1,
     *           "customary_foot_id": 1,
     *           "height": 5.8,
     *           "weight": 64,
     *           "jersey_number": "1",
     *          "positions": [
                    {
                        "id": 3,
                        "name": "Goal Keeper",
                        "lines": 2,
                        "pivot": {
                            "player_id": 1,
                            "position_id": 3
                        },
                        "line": {
                            "id": 2,
                            "name": "GoalKeepers"
                        }
                    }
                ],
     *           "customary_foot": {
     *               "id": 1,
     *               "name": "Left"
     *           }
     *        }
     *    }
     *}
     *}
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

        $player_privacy_setting = $this->userModel->with('user_privacy_settings')->find($request->player_id)->user_privacy_settings;

        if (count($player_privacy_setting) > 0) {
            $check_ispublic = $player_privacy_setting[0]->name;

            if (strtolower($check_ispublic) != 'public') {
                return Helper::apiNotFoundResponse(false, "Player privacy is not public. Can't proceed further", new stdClass());
            } else {

                $get_player_details = $this->userModel->getUserExerciseData(false,false)
                    ->orderBy('created_at')
                    ->find($request->player_id);

                if ($get_player_details) {
                    $response['get_player_details'] = $get_player_details;
                    return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
                }
                return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

            }

        } else {
            return Helper::apiNotFoundResponse(false, "Player has not privacy setting. Can't proceed further", new stdClass());
        }

    }

}
