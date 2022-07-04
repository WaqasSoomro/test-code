<?php

namespace App\Http\Controllers\Api\V3Dashboard;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3Dashboard\FilteredPlayerResource;
use App\PlayerExercise;
use App\Position;
use App\User;
use App\Line;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @group Dashboard V4 / Assignment
 * APIs for V4 dashboard assignment
 */
class SkillAssignment extends Controller
{
    private $lineModel, $lineColumns, $limit, $sockets, $sortingColumn, $sortingType, $status;

    public function __construct(Request $request)
    {
        $this->lineModel = new Line();

        $this->lineColumns = [
            'id',
            'name'
        ];

        $this->limit = $request->limit ?? 10;

        $this->offset = $request->offset ?? 0;

        $this->sortingColumn = 'created_at';

        $this->sortingType = 'desc';
    }

    /**
        Get Lines

        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found successfully",
            "Result": [
                {
                    "id": 1,
                    "name": "Left Back"
                }
            ]
        }

        @response 500
        {
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": []
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "No records found",
            "Result": []
        }
    **/

    public function get_lines(Request $request)
    {
        $response = $this->lineModel->viewLines($request, $this->lineColumns, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType);

        return $response;
    }
    /**
     * Get Team Player Positions
     *
     * @response
    {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": [
    {
    "id": 1,
    "name": "Left Back"
    },
    {
    "id": 2,
    "name": "Right Back"
    },
    {
    "id": 4,
    "name": "Center Back"
    }
    ]
    }
     *
     * @queryParam lines required array Pass Line Id to get associated positions
     */
    public function get_positions(Request $request)
    {
        $request->validate([
            'lines' => 'required|array',
            'lines.*' => 'numeric|exists:lines,id,status,active',
        ]);

        $positions = Position::select('id', 'name')->whereHas('line',function ($line) use
        ($request)
            {
           $line->whereIn("id",$request->lines);
        })->get();

        if (count($positions) == 0)
        {
            return Helper::apiNotFoundResponse(
                false,
                "No Position Found",
                []
            );
        }

        return Helper::apiSuccessResponse(true, 'Success', $positions);
    }

    /**
     * Filter Exercises
     *
     * @response
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "Successfully Filtered Players",
    "Result": [
    {
    "id": 2,
    "first_name": "Fatima",
    "last_name": "Sultana",
    "profile_picture": "media/users/60a3d1946b6701621348756.jpeg",
    "positions": [
        {
            "id": 3,
            "name": "Goal Keeper",
            "lines": 2,
            "line": {
                "id": 2,
                "name": "GoalKeepers"
            }
        }
    ],
    "team_name": "",
    "total_exercises": 37,
    "completed_exercises": 25,
    "total_comments": 0
    }
    ]
    }
     * @queryParam exercise_ids[] required integer
     * @queryParam assignment_id required integer
     */
    public function filter_exercises(Request $request)
    {
        $request->validate([
            "exercise_ids"=>"required|array",
//            "repetition"=>"required|integer",
            "assignment_id"=>"required|integer",
//            "club_id" => "required|integer"
        ]);

        if (in_array(null,$request->exercise_ids))
        {
            return  Helper::apiInvalidParamResponse(false,"Invalid Parameters", [
                "exercise_ids" => ["The exercise ids field is required."]
            ]);
        }

//        $club_id = DB::table("club_trainers")->select("club_id")->where("trainer_user_id",Auth::user()->id)->where("club_id",$request->club_id)->first();
//
//        if (!$club_id){
//            return Helper::apiNotFoundResponse(false,"Club Not Found", new \stdClass());
//        }

        $filterd_user = User::role("player")
            // WHERE USER HAS ASSIGNMENTS AND THAT ASSIGNMENT HAS EXERCISES
            ->whereHas([
                "player_assignments",function($player_assignment) use($request){
                    $player_assignment->whereHas("exercises" ,function($exercise) use($request)
                    {
                        $exercise->whereIn("exercise_id",$request->exercise_ids);
                    });
                    $player_assignment->where("assignment_id",$request->assignment_id);
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
            // WITH TEAMS OF THE PLAYER
            ->with("teams")
            // COUNT COMPLETED EXERCISES OF THAT USER
            ->withCount(["exercises as completed_exercises"=>function($c_exercise) use($request){
                $c_exercise->select(DB::raw("count(distinct(exercise_id))"))->where("status_id",3);
            }])
            // COUNT TOTAL EXERCISES IN THAT ASSIGNMENT
            ->withCount(["exercises as total_exercises"=>function($c_exercise) use($request){
            $c_exercise->select(DB::raw("count(distinct(exercise_id))"));
        }])
            // COUNT TOTAL NUMBER OF COMMENTS ON THAT USER WHERE ASSIGNMENT ID MATCHES THE REQUEST ONE
            ->withCount(["comments as total_comments" => function ($q) use($request)
        {
            $q->where('assignment_id', $request->assignment_id ?? 0);
        }])
            ->get();

        $result = FilteredPlayerResource::collection($filterd_user)->toArray($request);

        if (count($result) == 0)
        {
            return Helper::apiNotFoundResponse(false,"No Records Found", new \stdClass());
        }


        return Helper::apiSuccessResponse(true,"Successfully Filtered Players",$result);
    }
}
