<?php

namespace App\Http\Controllers\Api\TrainerApp\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\{ChatGroup, PlayerTeamRequest, Team, User};
use App\Helpers\Helper;
use App\Http\Controllers\Controller;

/**
 * @group Trainer Settings
 */
class PlayerRequestController extends Controller
{
    private $playerTeamRequestModel;

    public function __construct()
    {
        $this->playerTeamRequestModel = new PlayerTeamRequest();
    }
    
    /**
     * Get Team Requests
     * 
     * @response
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "Team requests found",
    "Result": [
    {
    "id": 206,
    "player_id": 2,
    "player_name": "Fatima Sultana",
    "profile_picture": "media/users/60a3d1946b6701621348756.jpeg",
    "positions": [],
    "team": "Test",
    "applied_team": "Test"
    }
    ]
    }
     * 
     * @queryParam team_id required integer
     */
    public function getTeamRequests(Request $request)
    {
        $response = $this->playerTeamRequestModel->teamRequests($request);

        return $response;
    }

    /**
     * AcceptTeamRequest
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Request Accepted",
    "Result": {}
    }
     *
     * @bodyParam request_id integer required
     * @return JsonResponse
     */
    public function acceptTeamRequests(Request $request){

        $request->validate([
            "request_id"=>"required|integer"
        ]);

        return $this->playerTeamRequestModel->acceptTeamRequests($request);
    }

    /**
     * RejectTeamRequest
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Request Rejected",
    "Result": {}
    }
     *
     * @bodyParam request_id integer required  required
     * @return JsonResponse
     */
    public function rejectTeamRequests(Request $request){
        $request->validate([
            "request_id"=>"required|integer"
        ]);

        return $this->playerTeamRequestModel->rejectTeamRequests($request);
    }

    /**
     * Get Team Details
     * 
     * @response
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": {
        "id": 6,
        "team_name": "Test",
        "privacy": "open_to_invites",
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
        "requests_count": 1,
        "trainers": [
            {
                "id": 40,
                "nationality_id": 164,
                "first_name": "Umer",
                "middle_name": null,
                "last_name": "Shaikh",
                "surname": "Shaikh",
                "email": "umer@jogo.ai",
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": "+934342336633",
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": "media/users/OF4YBPrj3vkx7QtIztDKOAZod45t1sSsXQfc1cPE.jpeg",
                "date_of_birth": null,
                "age": null,
                "badge_count": 0,
                "verification_code": null,
                "verified_at": "2021-08-13 13:15:08",
                "active": 0,
                "status_id": 1,
                "who_created": 40,
                "last_seen": "2021-09-08 16:08:57",
                "online_status": "1",
                "created_at": "2020-07-30 21:26:43",
                "updated_at": "2021-09-08 16:08:57",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "trainer_user_id": 40,
                    "created_at": "2021-01-11 13:56:19"
                }
            },
            {
                "id": 212,
                "nationality_id": 152,
                "first_name": "Trainer",
                "middle_name": "''",
                "last_name": "Testing",
                "surname": "",
                "email": "testingtrainer@gmail.com",
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": null,
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": null,
                "badge_count": 44,
                "verification_code": null,
                "verified_at": "2021-01-11 16:32:17",
                "active": 0,
                "status_id": 2,
                "who_created": 40,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2021-01-11 16:32:17",
                "updated_at": "2021-06-17 08:59:23",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "trainer_user_id": 212,
                    "created_at": null
                }
            },
            {
                "id": 214,
                "nationality_id": 152,
                "first_name": "First",
                "middle_name": "''",
                "last_name": "Last",
                "surname": "",
                "email": "qwerty@a.com",
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": null,
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": null,
                "badge_count": 0,
                "verification_code": null,
                "verified_at": "2021-01-11 17:26:28",
                "active": 0,
                "status_id": 2,
                "who_created": 40,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2021-01-11 17:26:28",
                "updated_at": "2021-01-11 17:26:28",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "trainer_user_id": 214,
                    "created_at": "2021-01-11 17:26:28"
                }
            },
            {
                "id": 215,
                "nationality_id": 152,
                "first_name": "ABC",
                "middle_name": "''",
                "last_name": "DEF",
                "surname": "",
                "email": "1@a.com",
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": null,
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": null,
                "badge_count": 0,
                "verification_code": null,
                "verified_at": "2021-01-11 17:41:25",
                "active": 0,
                "status_id": 2,
                "who_created": 40,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2021-01-11 17:41:25",
                "updated_at": "2021-01-11 17:41:25",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "trainer_user_id": 215,
                    "created_at": "2021-01-11 17:41:25"
                }
            },
            {
                "id": 382,
                "nationality_id": 152,
                "first_name": "Saad",
                "middle_name": "''",
                "last_name": "Saleem",
                "surname": "",
                "email": "ssaad.sm@gmail.com",
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": null,
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": null,
                "badge_count": 0,
                "verification_code": null,
                "verified_at": "2021-03-16 12:49:04",
                "active": 0,
                "status_id": 2,
                "who_created": 40,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2021-03-16 12:49:04",
                "updated_at": "2021-03-16 12:49:04",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "trainer_user_id": 382,
                    "created_at": "2021-03-16 12:49:04"
                }
            },
            {
                "id": 383,
                "nationality_id": 152,
                "first_name": "Waleed",
                "middle_name": "''",
                "last_name": "waqar",
                "surname": "",
                "email": "ta.waleed1@gmail.com",
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": null,
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": null,
                "badge_count": 0,
                "verification_code": null,
                "verified_at": "2021-03-16 12:49:51",
                "active": 0,
                "status_id": 2,
                "who_created": 40,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2021-03-16 12:49:51",
                "updated_at": "2021-03-16 12:49:51",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "trainer_user_id": 383,
                    "created_at": "2021-03-16 12:49:51"
                }
            },
            {
                "id": 447,
                "nationality_id": 164,
                "first_name": "Shahzaib",
                "middle_name": null,
                "last_name": "Trainer",
                "surname": null,
                "email": "shahzaib.imran@jogo.ai",
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": "+923482302450",
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": null,
                "badge_count": 14,
                "verification_code": null,
                "verified_at": "2021-04-09 13:27:31",
                "active": 0,
                "status_id": 2,
                "who_created": null,
                "last_seen": "2021-06-21 06:49:47",
                "online_status": "1",
                "created_at": "2021-04-09 13:24:46",
                "updated_at": "2021-06-21 06:49:47",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "trainer_user_id": 447,
                    "created_at": null
                }
            }
        ],
        "players": [
            {
                "id": 155,
                "nationality_id": null,
                "first_name": "M",
                "middle_name": "''",
                "last_name": "J",
                "surname": null,
                "email": null,
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": "+123",
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": null,
                "badge_count": 55,
                "verification_code": null,
                "verified_at": "2020-12-14 15:05:57",
                "active": 0,
                "status_id": 1,
                "who_created": null,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2020-12-14 15:05:57",
                "updated_at": "2021-06-18 14:05:18",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "user_id": 155
                }
            },
            {
                "id": 210,
                "nationality_id": 152,
                "first_name": "Test",
                "middle_name": "''",
                "last_name": "Trainer",
                "surname": "",
                "email": "trainerr@gmail.com",
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": null,
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": null,
                "badge_count": 45,
                "verification_code": null,
                "verified_at": "2021-01-11 16:11:52",
                "active": 0,
                "status_id": 2,
                "who_created": 40,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2021-01-11 16:11:52",
                "updated_at": "2021-06-17 08:59:23",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "user_id": 210
                }
            },
            {
                "id": 211,
                "nationality_id": 152,
                "first_name": "Trainer",
                "middle_name": "''",
                "last_name": "Name",
                "surname": "",
                "email": "amav@a.com",
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": null,
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": null,
                "badge_count": 44,
                "verification_code": null,
                "verified_at": "2021-01-11 16:31:03",
                "active": 0,
                "status_id": 2,
                "who_created": 40,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2021-01-11 16:31:03",
                "updated_at": "2021-06-17 08:59:23",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "user_id": 211
                }
            },
            {
                "id": 212,
                "nationality_id": 152,
                "first_name": "Trainer",
                "middle_name": "''",
                "last_name": "Testing",
                "surname": "",
                "email": "testingtrainer@gmail.com",
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": null,
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": null,
                "badge_count": 44,
                "verification_code": null,
                "verified_at": "2021-01-11 16:32:17",
                "active": 0,
                "status_id": 2,
                "who_created": 40,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2021-01-11 16:32:17",
                "updated_at": "2021-06-17 08:59:23",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "user_id": 212
                }
            },
            {
                "id": 213,
                "nationality_id": 152,
                "first_name": "a",
                "middle_name": "''",
                "last_name": "v",
                "surname": "",
                "email": "fw@a.com",
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": null,
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": null,
                "badge_count": 44,
                "verification_code": null,
                "verified_at": "2021-01-11 16:49:58",
                "active": 0,
                "status_id": 2,
                "who_created": 40,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2021-01-11 16:49:58",
                "updated_at": "2021-06-17 08:59:23",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "user_id": 213
                }
            },
            {
                "id": 390,
                "nationality_id": null,
                "first_name": "Umer",
                "middle_name": "''",
                "last_name": "Shaikh",
                "surname": null,
                "email": null,
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": "+923489097792",
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": "media/users/605dd0b272be71616761010.jpeg",
                "date_of_birth": "1997-04-23",
                "age": null,
                "badge_count": 21,
                "verification_code": "646745",
                "verified_at": "2021-06-09 11:48:18",
                "active": 0,
                "status_id": 1,
                "who_created": null,
                "last_seen": "2021-06-09 12:15:57",
                "online_status": "1",
                "created_at": "2021-03-26 12:15:16",
                "updated_at": "2021-06-23 08:44:27",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "user_id": 390
                }
            },
            {
                "id": 429,
                "nationality_id": null,
                "first_name": "Umer",
                "middle_name": "''",
                "last_name": "Shaikh",
                "surname": null,
                "email": null,
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": "+1233",
                "gender": "man",
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": "10",
                "badge_count": 6,
                "verification_code": null,
                "verified_at": "2021-04-08 19:33:22",
                "active": 0,
                "status_id": 1,
                "who_created": null,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2021-04-08 19:33:22",
                "updated_at": "2021-05-20 12:18:00",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "user_id": 429
                }
            },
            {
                "id": 433,
                "nationality_id": null,
                "first_name": "d",
                "middle_name": "''",
                "last_name": "fd",
                "surname": null,
                "email": null,
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": "+31454",
                "gender": "man",
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": "12",
                "badge_count": 5,
                "verification_code": null,
                "verified_at": "2021-04-08 20:24:48",
                "active": 0,
                "status_id": 1,
                "who_created": null,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2021-04-08 20:24:48",
                "updated_at": "2021-05-20 12:18:00",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "user_id": 433
                }
            },
            {
                "id": 437,
                "nationality_id": null,
                "first_name": "Playrer",
                "middle_name": "''",
                "last_name": "2",
                "surname": null,
                "email": null,
                "new_temp_email": null,
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "country_code_id": 152,
                "phone": "+663",
                "gender": "man",
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": "10",
                "badge_count": 5,
                "verification_code": null,
                "verified_at": "2021-04-08 20:43:08",
                "active": 0,
                "status_id": 1,
                "who_created": null,
                "last_seen": null,
                "online_status": "0",
                "created_at": "2021-04-08 20:43:08",
                "updated_at": "2021-05-20 12:18:00",
                "deleted_at": null,
                "country_code": null,
                "pivot": {
                    "team_id": 6,
                    "user_id": 437
                }
            }
        ]
    }
}
     * 
     * @queryParam team_id required integer
     */
    public function getTeamDetails(Request $request){
        $request->validate([
            "team_id"=>"required|integer"
        ]);

        $team = Team::with(["trainers","players"])->withCount("requests")->find($request->team_id);

        if (!$team) {
            return Helper::apiNotFoundResponse(false,"Team Not Found",$team);
        }

        return Helper::apiSuccessResponse(true,"Success",$team);

    }
}