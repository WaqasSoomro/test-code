<?php

namespace App\Http\Controllers\Api\ParentSharing\Players;

use App\Http\Requests\Api\ParentSharing\Players\ListingRequest;
use App\Helpers\Helper;
use App\User;
use App\Status;
use App\MatchStat;
use App\MatchStatType;
use App\Position;
use App\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use stdClass;
use Carbon\Carbon;

/**
 * @group Parent Sharing / Players
 */
class IndexController extends Controller
{
    private $userModel, $generalColumns, $playersColumns, $positionColumns, $playersTeamsColumns, $limit, $offset, $sortingColumn, $sortingType, $status, $apiType;

    public function __construct(Request $request)
    {
        $this->userModel = new User();

        $this->generalColumns = [
            'id',
            'first_name',
            'last_name',
            'profile_picture'
        ];

        $this->playersColumns = [
            'id',
            'user_id'
        ];

        $this->positionColumns = [
            'position_id',
            'name'
        ];

        $this->playersTeamsColumns = [
            'user_id',
            'team_id',
            'team_name'
        ];

        $this->limit = $request->limit;

        $this->offset = $request->offset;

        $this->sortingColumn = 'created_at';

        $this->sortingType = 'asc';

        $this->status = Status::select('id')
            ->where('name', 'active')
            ->first()
            ->id;

        $this->apiType = 'parentsSharingApp';
    }

    /**
     * Listing
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found",
     * "Result": [
     * {
     * "id": 1,
     * "firstName": "Shahzaib",
     * "lastName": "Imran",
     * "image": "media/users/img.png",
     * "positions": [
     * {
     * "id": 1,
     * "name": "Keeper"
     * }
     * ],
     * "teams": [
     * {
     * "id": 1,
     * "name": "U19"
     * },
     * {
     * "id": 2,
     * "name": "Champs Shim"
     * }
     * ]
     * }
     * ]
     * }
     *
     * @response 422{
     * "Response": false,
     * "StatusCode": 422,
     * "Message": "Invalid Parameters",
     * "Result": {
     * "limit": [
     * "The limit must be a number."
     * ]
     * }
     * }
     *
     * @response 404
     * {
     * "Response": false,
     * "StatusCode": 404,
     * "Message": "No records found",
     * "Result": []
     * }
     *
     * @response 500
     * {
     * "Response": false,
     * "StatusCode": 500,
     * "Message": "Something wen't wrong",
     * "Result": []
     * }
     *
     * @queryParam limit required integer. Example: 10
     * @queryParam offset required integer. Example: 0
     */

    public function index(ListingRequest $request)
    {
        $response = $this->userModel->playersListing($request, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType, $this->status, $this->apiType, $this->generalColumns, $this->playersColumns, $this->positionColumns, $this->playersTeamsColumns);

        return $response;
    }

    /**
     * Filters
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
     * }
     * ],
     * "players": [
     * {
     * "id": 128,
     * "first_name": "baran",
     * "middle_name": "''",
     * "last_name": "erdogan"
     * }
     * ]
     * }
     * }
     *
     * @queryParam team_id optional string to get players of specific team
     */

    public function filters(Request $request)
    {
        try{
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $this->userModel->filters($request));
        }catch (\Exception $ex){
            return Helper::apiErrorResponse(false,"Something Went Wrong",new stdClass());
        }
    }

    /**
     *
     * Top records
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
     *
     * @queryParam player_id required string
     * @queryParam from required string date Y-m-d
     * @queryParam to required string date Y-m-d
     */

    public function topRecords(Request $request)
    {
        Validator::make($request->all(), [
            'player_id' => 'required|exists:users,id',
            'from' => 'date|date_format:Y-m-d',
            'to' => 'date|date_format:Y-m-d',
        ])
            ->validate();

        $response = MatchStat::topRecords($request);

        return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
    }

    /**
     * Session metrics
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
     * }
     * ]
     * }
     * }
     * }
     *
     * @queryParam player_id required player id is required
     * @queryParam from optional string date Y-m-d
     * @queryParam to optional string date Y-m-d
     */

    public function sessionMetrics(Request $request)
    {
        $from = isset($request->from) ? $request->from : '1970-01-01';

        $to = isset($request->to) ? $request->to : Carbon::today()->addDay();

        $response = MatchStat::getSessionMetrics($request);

        return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
    }

    /**
     *
     * Average speed
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "labels": [
     * "2012-01-30",
     * "2020-07-09"
     * ],
     * "data_1": [
     * 0,
     * 50,
     * 31.571428571428573
     * ],
     * "data_2": [
     * 88,
     * 0,
     * 0,
     * 0
     * ]
     * }
     * }
     *
     * @queryParam player_id required player id is required
     * @queryParam from string date Y-m-d send only when duration is null
     * @queryParam to string date Y-m-d send only when duration is null
     * @queryParam duration string day|month|3-months|6-months|year|all (select when to and from params are null)
     * @queryParam filter array optional [height,weight,age_group,position,player2]
     */

    public function averageSpeed(Request $request)
    {
        Validator::make($request->all(), [
            'player_id' => 'required|exists:users,id',
            'from' => 'date|date_format:Y-m-d',
            'to' => 'date|date_format:Y-m-d',
        ])
            ->validate();

        $response = (new User())->getGraph('avg_speed', $request);

        return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
    }

    /**
     *
     * Speed zone
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
     * 0
     * ],
     * "data_2": [
     * 54.4,
     * 53.25,
     * 38
     * ]
     * }
     * }
     *
     * @queryParam player_id required player id is required
     * @queryParam from string date Y-m-d send only when duration is null
     * @queryParam to string date Y-m-d send only when duration is null
     * @queryParam duration string day|month|3-months|6-months|year|all (select when to and from params are null)
     * @queryParam  filter array optional [height,weight,age_group,position,player2]
     */

    public function speedZone(Request $request)
    {
        Validator::make($request->all(), [
            'player_id' => 'required|exists:users,id',
            'from' => 'date|date_format:Y-m-d',
            'to' => 'date|date_format:Y-m-d',
        ])
            ->validate();

        $response = (new User())->getGraph('speed', $request);

        return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
    }

    /**
     *
     * Heart rate
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "labels": [
     * "2021-01-29"
     * ],
     * "data_1": [
     * 0
     * ],
     * "data_2": [
     * 0
     * ]
     * }
     * }
     *
     * @queryParam player_id required player id is required
     * @queryParam from string date Y-m-d send only when duration is null
     * @queryParam to string date Y-m-d send only when duration is null
     */

    public function heartRate(Request $request)
    {
        Validator::make($request->all(), [
            'player_id' => 'required|exists:users,id',
            'from' => 'date|date_format:Y-m-d',
            'to' => 'date|date_format:Y-m-d',
        ])
            ->validate();

        $response = (new User())->getGraph('heart_rate', $request);

        if (count($response)) {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }

        return Helper::apiSuccessResponse(false, 'Records not found!', $response);
    }

    /**
     * Exercises details
     *
     * Getting player exercises  data in which we will be getting  done exercises details.
     *
     * @queryParam  player_id required player id is required
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

    public function exercisesDetails(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:users,id',
        ]);

        $player_id = $request->player_id;

        $exerciseCallback = function ($q4) use ($player_id, $request) {
            $q4->select('exercises.id', 'exercises.title', 'completion_time', 'thumbnail', 'video_file', 'player_exercise.created_at', 'level_id')
                ->distinct('player_exercises.exercise_id');

            if ($request->view_from == "player_database") {
                $q4->whereNull('assignment_id');
            }

            $q4->orderBy('created_at', 'desc');
        };

        $get_player_data = $this->userModel->getUserExerciseData($exerciseCallback,true)
            ->find($player_id);

        if (count($get_player_data->exercises) > 0) {

            $conversation_ids = [(int)$player_id, auth()->user()->id];

            \Session::put('posts', []);

            $exercises = $get_player_data->exercises->map(function ($ex) use ($player_id, $conversation_ids) {
                return Helper::getExerciseObject($ex,$player_id,$conversation_ids,true);
            })->reject(function ($aa) {
                return $aa == null;
            });

            $exercises_responses = $this->loadExercises($exercises);

        } else {
            $exercises_responses = 0;
        }

        unset($get_player_data->exercises);

        if ($get_player_data) {
            $response['player_details'] = $get_player_data;
            $response['player_exercises'] = $exercises_responses;
            //$response['stats'] = $stats;

            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }
        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

    }
}