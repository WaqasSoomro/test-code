<?php

namespace App\Http\Controllers\Api\TrainerApp\Club\Teams;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Clubs\Teams\Players\IndexRequest;
use App\Position;
use App\Team;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @group TrainerApp / Players
 *
 * API for trainerapp players
 */
class PlayerController extends Controller
{
    //
    private $userModel, $playersColumns, $limit, $offset, $sortingColumn, $sortingType, $status;
    private $positionModel, $positionsColumns;

    public function __construct(Request $request)
    {
        $this->userModel = User::class;

        $this->positionModel = Position::class;

        $this->positionsColumns = (new $this->positionModel)->generalColumns();

        $this->playersColumns = (new $this->userModel)->playersGeneralColumns();

        $this->limit = $request->limit ?? 10;

        $this->offset = $request->offset ?? 0;

        $this->sortingColumn = 'created_at';

        $this->sortingType = 'asc';

        $this->status = [1];
    }
    /**
    Team players listing

    @response{
    "Response": true,
    "StatusCode": 200,
    "Message": "Records found successfully",
    "Result": [
    {
    "id": 1,
    "name": "Shahzaib Imran",
    "image": "media/users/5fa27263a93271604481635.jpeg",
    "positions": [
    {
    "id": 1,
    "name": "Left Back"
    }
    ]
    }
    ]
    }

    @response 500{
    "Response": false,
    "StatusCode": 500,
    "Message": "Something wen't wrong",
    "Result": []
    }

    @response 404{
    "Response": false,
    "StatusCode": 404,
    "Message": "no records found",
    "Result": []
    }

    @queryParam teamId[] required integer. Example: 5
    @queryParam positionsId[] required integer. Example: 4
    @queryParam positionsId[] required integer. Example: 10
    @queryParam limit optional integer default 10. Example: 10
    @queryParam offset optional integer default 0. Example: 0
     */

    protected function listingByPositions(Request $request)
    {
        Validator::make($request->all(), [
            'teamId' => 'required|array|exists:teams,id',
            'positionsId' => 'required|array'
        ])->validate();

        $response = (new $this->userModel)->getPlayers($request, $this->playersColumns, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType, $this->status);

        return $response;
    }

    /**
    Team Positions Listing
    @response
    {
    "Response": true,
    "StatusCode": 200,
    "Message": "success",
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
    "id": 3,
    "name": "Goal Keeper"
    },
    {
    "id": 4,
    "name": "Center Back"
    },
    {
    "id": 5,
    "name": "Center Midfield"
    },
    {
    "id": 6,
    "name": "Left Midfield"
    },
    {
    "id": 7,
    "name": "Left Wing"
    },
    {
    "id": 8,
    "name": "Right midfield"
    },
    {
    "id": 9,
    "name": "Right wing"
    },
    {
    "id": 10,
    "name": "Striker "
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
    "Message": "no records found",
    "Result": []
    }
     **/

    public function teamPositionListing(Request $request)
    {
        $response = (new $this->positionModel)->viewPositions($request, $this->positionsColumns, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType, $this->status);

        return $response;
    }

    /**
     * Get Trainer Team And Positions
     *
     * @response
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "Teams and Positions found",
    "Result": {
    "team_trainers": [
    {
    "id": 5,
    "team_name": "consequatur",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": "tempore",
    "age_group": null,
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": null,
    "updated_at": "2021-01-11 13:37:22",
    "deleted_at": null,
    "players_count": 67
    },
    {
    "id": 6,
    "team_name": "Test",
    "image": "",
    "gender": "mixed",
    "team_type": "indoor",
    "description": null,
    "age_group": "23",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2020-12-14 15:02:44",
    "updated_at": "2021-01-11 16:06:20",
    "deleted_at": null,
    "players_count": 9
    },
    {
    "id": 7,
    "team_name": "Argentina",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "U16",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2020-12-14 15:03:34",
    "updated_at": "2020-12-17 10:36:51",
    "deleted_at": null,
    "players_count": 5
    },
    {
    "id": 9,
    "team_name": "Team1",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "19",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2020-12-17 17:45:52",
    "updated_at": "2020-12-17 17:45:52",
    "deleted_at": null,
    "players_count": 2
    },
    {
    "id": 10,
    "team_name": "Team1",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "19",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2020-12-17 17:46:09",
    "updated_at": "2020-12-17 17:46:09",
    "deleted_at": null,
    "players_count": 2
    },
    {
    "id": 11,
    "team_name": "Team2",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "22",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2020-12-17 17:46:09",
    "updated_at": "2020-12-17 17:46:09",
    "deleted_at": null,
    "players_count": 3
    },
    {
    "id": 12,
    "team_name": "Check",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "19",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2020-12-17 17:47:06",
    "updated_at": "2020-12-17 17:47:06",
    "deleted_at": null,
    "players_count": 9
    },
    {
    "id": 16,
    "team_name": "teamname",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "22",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2020-12-21 15:03:41",
    "updated_at": "2020-12-21 15:03:41",
    "deleted_at": null,
    "players_count": 8
    },
    {
    "id": 20,
    "team_name": "Mixed team",
    "image": "",
    "gender": "mixed",
    "team_type": "outdoor",
    "description": null,
    "age_group": "10",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2021-01-08 13:12:02",
    "updated_at": "2021-01-08 13:12:02",
    "deleted_at": null,
    "players_count": 7
    },
    {
    "id": 21,
    "team_name": "Indoor team",
    "image": "",
    "gender": "mixed",
    "team_type": "indoor",
    "description": null,
    "age_group": "10",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2021-01-08 13:14:21",
    "updated_at": "2021-01-08 13:14:21",
    "deleted_at": null,
    "players_count": 4
    },
    {
    "id": 22,
    "team_name": "Outdoor team",
    "image": "",
    "gender": "mixed",
    "team_type": "outdoor",
    "description": null,
    "age_group": "10",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2021-01-08 13:15:06",
    "updated_at": "2021-01-08 13:15:06",
    "deleted_at": null,
    "players_count": 0
    },
    {
    "id": 23,
    "team_name": "11",
    "image": "",
    "gender": "man",
    "team_type": "indoor",
    "description": null,
    "age_group": "11",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2021-01-08 13:29:56",
    "updated_at": "2021-01-08 13:29:56",
    "deleted_at": null,
    "players_count": 0
    },
    {
    "id": 25,
    "team_name": "a",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "11",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2021-01-11 13:51:09",
    "updated_at": "2021-01-11 13:51:09",
    "deleted_at": null,
    "players_count": 0
    },
    {
    "id": 31,
    "team_name": "T1",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "11",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2021-01-12 14:07:25",
    "updated_at": "2021-01-12 14:07:25",
    "deleted_at": null,
    "players_count": 0
    },
    {
    "id": 33,
    "team_name": "T2",
    "image": "",
    "gender": "man",
    "team_type": "indoor",
    "description": null,
    "age_group": "10",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2021-01-14 10:34:37",
    "updated_at": "2021-01-14 10:34:37",
    "deleted_at": null,
    "players_count": 0
    },
    {
    "id": 34,
    "team_name": "T2",
    "image": "",
    "gender": "man",
    "team_type": "indoor",
    "description": null,
    "age_group": "10",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2021-01-14 13:00:32",
    "updated_at": "2021-01-14 13:00:32",
    "deleted_at": null,
    "players_count": 0
    },
    {
    "id": 51,
    "team_name": "yyyy",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "11",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2021-03-04 17:48:30",
    "updated_at": "2021-03-04 17:48:30",
    "deleted_at": null,
    "players_count": 0
    },
    {
    "id": 52,
    "team_name": "yyyyp",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "11",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2021-03-04 17:49:48",
    "updated_at": "2021-03-04 17:49:48",
    "deleted_at": null,
    "players_count": 0
    },
    {
    "id": 55,
    "team_name": "Team",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "11",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2021-03-16 09:01:01",
    "updated_at": "2021-03-16 09:01:01",
    "deleted_at": null,
    "players_count": 0
    }
    ],
    "positions": [
    {
    "id": 1,
    "name": "Left Back"
    },
    {
    "id": 2,
    "name": "Right Back"
    },
    {
    "id": 3,
    "name": "Goal Keeper"
    },
    {
    "id": 4,
    "name": "Center Back"
    },
    {
    "id": 5,
    "name": "Center Midfield"
    },
    {
    "id": 6,
    "name": "Left Midfield"
    },
    {
    "id": 7,
    "name": "Left Wing"
    },
    {
    "id": 8,
    "name": "Right midfield"
    },
    {
    "id": 9,
    "name": "Right wing"
    },
    {
    "id": 10,
    "name": "Striker "
    }
    ]
    }
    }
     */

    public function get_team_and_positions(Request $request){
        $data = [];
        $club = DB::table('club_trainers')->where('trainer_user_id', auth()->user()->id)->first();
        if(!$club){
            return Helper::apiErrorResponse(false, 'Club not found',new \stdClass());
        }
        $club_id = $club->club_id ?? 0;
        $teams = Team::whereHas('clubs',function($q) use ($club_id){
            return $q->where('club_id',$club_id);
        })->withCount('players')->get();

        if (!count($teams) > 0)
        {
            return Helper::apiSuccessResponse(false, 'Teams Not found', []);
        }

        $positions = (new $this->positionModel)->viewPositions($request, $this->positionsColumns, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType, $this->status);

        if (!count($positions->original["Result"]) > 0)
        {
            return Helper::apiSuccessResponse(false, 'Positions Not found', []);
        }
        $data["team_trainers"] = $teams;
        $data["positions"] = $positions->original["Result"];

        return Helper::apiSuccessResponse(true, 'Teams and Positions found', $data);
    }
}
