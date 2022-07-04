<?php

namespace App\Http\Controllers\Api\Dashboard\Setting;

use App\Assignment;
use App\Club;
use App\Country;
use App\Coupon;
use App\Exercise;
use App\ExerciseScore;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Match;
use App\PlayerExercise;
use App\Team;
use App\User;
use App\ZohoLead;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * @group Dashboard / Settings
 * APIs for dashboard settings
 */
class ClubController extends Controller
{
    //


    /**
     * Update Club
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Club updated",
    "Result": {}
    }
     *
     * @bodyParam club_name string required max 191 chars
     * @bodyParam website string required max 191 chars
     * @bodyParam club_type string required max 191 chars required
     * @bodyParam foundation_date  date required   Y-m-d required
     * @bodyParam registration_date  date required Y-m-d required
     * @bodyParam registration_no  string required max 191  chars required
     * @bodyParam country_id  integer required  required
     * @bodyParam city  string required  required
     * @bodyParam street_address string required  required
     * @bodyParam address string required max 191  chars
     * @bodyParam image string base64
     * @return JsonResponse
     */

    public function saveClub(Request $request){
        $this->validate($request,[
            'club_name'=>'required',
//            'website'=>'required',
//            'club_type'=>'required',
            'foundation_date'=>'nullable|date|date_format:Y-m-d',
            'registration_date'=>'nullable|date|date_format:Y-m-d',
//            'registration_no'=>'required',
//            'address'=>'required',
        ]);
        $club = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->first();
        $club_id = $club->club_id ?? 0;
        $club = Club::find($club_id);
        $newClub = false;
        if(!$club){
//            if(!$request->hasFile('image')){
//                return Helper::apiErrorResponse(false, 'Club Image/Logo is required',new \stdClass());
//            }
            $newClub = true;
            $club = new Club();
        }

        $save_club = $club->updateClub($request);
        if($save_club instanceof  Club){
            $save_club->trainers()->syncWithoutDetaching([auth()->user()->id]);
            if($newClub){
                /*$response = Helper::createOrganization($save_club);
                Helper::leadResponse($response,'club',$club->id);
                $contactResponse = Helper::createContact(Auth::user(),$request->club_name);
                Helper::leadResponse($contactResponse,'trainer',Auth::user()->id);*/
            }else{
                /*$response = Helper::updateOrganization($save_club,$club->id);
                Helper::leadResponse($response,'club',$club->id,false);*/
            }
            return Helper::apiSuccessResponse(true, 'Club Updated', new \stdClass());
        }
        return Helper::apiErrorResponse(false, 'Failed To Save Club',new \stdClass());

    }



    /**
     * Get Club Detail
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Club found",
    "Result":  {
    "id": 2,
    "title": "JOGO CLUB",
    "website": "jogo@jogo.com",
    "type": "field",
    "foundation_date": "2002-09-09",
    "registration_date": "2006-02-02",
    "registration_no": "AE434VB",
    "address": "USA",
    "description": null,
    "image": "media/users/5fb5848990cbf1605731465.png",
    "email": null,
    "created_at": null,
    "updated_at": "2020-11-18 20:31:05",
    "deleted_at": null
    }

    }
     *
     * @return JsonResponse
     */

    public function getClub(Request $request){
        $club = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->first();
        if(!$club){
            return Helper::apiErrorResponse(false, 'Club not found',new \stdClass());
        }
        $club_id = $club->club_id ?? 0;
        $club = Club::find($club_id);
        if($club){
            $countries = Country::select('id','name','iso','phone_code')->orderBy('name','ASC')->get();
            $club->countries = $countries;
            return Helper::apiSuccessResponse(true, 'Club Found', $club);
        }
        return Helper::apiErrorResponse(false, 'Club not found',new \stdClass());

    }



    /**
     * Get Club Data
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Club Found",
    "Result": {
    "club": {
    "id": 2,
    "title": "JOGO",
    "description": null,
    "image": "media/clubs/8UlN8oNpPvjmnAYLhMmidcKurazvouRvmaFk0BU7.png",
    "website": "jogo@jogo.com",
    "type": "Football Academy",
    "foundation_date": "2002-09-09",
    "registration_date": "2006-02-02",
    "registration_no": "AE434VB",
    "address": "ABC Street",
    "email": null,
    "country_id": 164,
    "city": "Karachi",
    "street_address": null,
    "created_at": null,
    "updated_at": "2021-01-26 08:29:50",
    "deleted_at": null
    },
    "teams": [
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
    "players_count": 5
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
    "players_count": 2
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
    "players_count": 1
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
    "players_count": 1
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
    "players_count": 0
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
    "players_count": 6
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
    "players_count": 3
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
    "players_count": 0
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
    "players_count": 2
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
    "players_count": 66
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
    }
    ],
    "trainers": [
    {
    "id": 40,
    "first_name": "Umer",
    "last_name": "Shaikh",
    "middle_name": null,
    "profile_picture": "media/users/OF4YBPrj3vkx7QtIztDKOAZod45t1sSsXQfc1cPE.jpeg"
    },
    {
    "id": 133,
    "first_name": "Umer",
    "last_name": "Shaikh",
    "middle_name": null,
    "profile_picture": null
    },
    {
    "id": 144,
    "first_name": "shahzaib",
    "last_name": "trainer",
    "middle_name": null,
    "profile_picture": null
    },
    {
    "id": 146,
    "first_name": null,
    "last_name": null,
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 147,
    "first_name": "Hasnain",
    "last_name": "Ali",
    "middle_name": null,
    "profile_picture": null
    },
    {
    "id": 148,
    "first_name": "mj",
    "last_name": null,
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 149,
    "first_name": "Hasnain",
    "last_name": "Ali",
    "middle_name": null,
    "profile_picture": null
    },
    {
    "id": 151,
    "first_name": "khurram",
    "last_name": "munir",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 187,
    "first_name": "khurram",
    "last_name": "munir",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 188,
    "first_name": "First",
    "last_name": "Last",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 192,
    "first_name": "h",
    "last_name": "w",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 193,
    "first_name": "h",
    "last_name": "w",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 201,
    "first_name": "a",
    "last_name": "b",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 202,
    "first_name": "a",
    "last_name": "b",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 210,
    "first_name": "Test",
    "last_name": "Trainer",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 211,
    "first_name": "Trainer",
    "last_name": "Name",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 212,
    "first_name": "Trainer",
    "last_name": "Testing",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 213,
    "first_name": "a",
    "last_name": "v",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 214,
    "first_name": "First",
    "last_name": "Last",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 215,
    "first_name": "ABC",
    "last_name": "DEF",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 253,
    "first_name": "First",
    "last_name": "afcom",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 254,
    "first_name": "Sec",
    "last_name": "abv",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 255,
    "first_name": "First",
    "last_name": "afcom",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 256,
    "first_name": "Sec",
    "last_name": "abv",
    "middle_name": "''",
    "profile_picture": null
    },
    {
    "id": 332,
    "first_name": "t",
    "last_name": "ra",
    "middle_name": "''",
    "profile_picture": null
    }
    ],
    "players": [
    {
    "id": 128,
    "player_name": "baran erdogan",
    "profile_picture": "media/users/5f99cb1c40f871603914524.jpeg",
    "age": null,
    "gender": null,
    "position": [
    "Striker "
    ],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 131,
    "player_name": "testing ",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 132,
    "player_name": " ",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 134,
    "player_name": " ",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 135,
    "player_name": " ",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 136,
    "player_name": "erik eijgenstein",
    "profile_picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
    "age": null,
    "gender": null,
    "position": [
    "Striker "
    ],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 137,
    "player_name": " ",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 138,
    "player_name": "Baran Erdogan",
    "profile_picture": "media/users/5f9fc5c306fbf1604306371.jpeg",
    "age": null,
    "gender": null,
    "position": [
    "Goal Keeper"
    ],
    "points": 150,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 140,
    "player_name": "Christiano Ronaldo",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [
    "Left Midfield"
    ],
    "points": 50,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 141,
    "player_name": "Fahad Paapi",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [
    "Center Back"
    ],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 142,
    "player_name": "Fami sultana",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 143,
    "player_name": "Bram Vijgen",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [
    "Goal Keeper"
    ],
    "points": 0,
    "suggestions": 0,
    "score": 100,
    "matches": []
    },
    {
    "id": 150,
    "player_name": " ",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 152,
    "player_name": "alvaro montero",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": [
    {
    "id": 3892,
    "user_id": 152,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2021-01-28 12:51:57",
    "end_ts": "2021-01-28 12:52:30",
    "total_ts": "00:00:28",
    "name": null,
    "creator": null,
    "stadium_name": null,
    "location": null,
    "geo_lon": null,
    "rotation": null,
    "team1": null,
    "team2": null,
    "geo_lat": null,
    "match_type": null,
    "player_image": null,
    "current_period": null,
    "finished": "",
    "team1_score": null,
    "team2_score": null,
    "created_at": "2021-01-28 12:51:57",
    "updated_at": "2021-01-28 12:52:30"
    },
    {
    "id": 3893,
    "user_id": 152,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2021-01-28 12:52:39",
    "end_ts": "2021-01-28 12:52:43",
    "total_ts": "00:00:01",
    "name": null,
    "creator": null,
    "stadium_name": null,
    "location": null,
    "geo_lon": null,
    "rotation": null,
    "team1": null,
    "team2": null,
    "geo_lat": null,
    "match_type": null,
    "player_image": null,
    "current_period": null,
    "finished": "",
    "team1_score": null,
    "team2_score": null,
    "created_at": "2021-01-28 12:52:39",
    "updated_at": "2021-01-28 12:52:43"
    },
    {
    "id": 3895,
    "user_id": 152,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2021-01-28 13:06:28",
    "end_ts": "2021-01-28 13:07:32",
    "total_ts": "00:00:56",
    "name": null,
    "creator": null,
    "stadium_name": null,
    "location": null,
    "geo_lon": null,
    "rotation": null,
    "team1": null,
    "team2": null,
    "geo_lat": null,
    "match_type": null,
    "player_image": null,
    "current_period": null,
    "finished": "",
    "team1_score": null,
    "team2_score": null,
    "created_at": "2021-01-28 13:06:28",
    "updated_at": "2021-01-28 13:07:32"
    },
    {
    "id": 3897,
    "user_id": 152,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2021-01-28 13:07:35",
    "end_ts": "2021-01-28 13:12:03",
    "total_ts": "00:02:23",
    "name": null,
    "creator": null,
    "stadium_name": null,
    "location": null,
    "geo_lon": null,
    "rotation": null,
    "team1": null,
    "team2": null,
    "geo_lat": null,
    "match_type": null,
    "player_image": null,
    "current_period": null,
    "finished": "",
    "team1_score": null,
    "team2_score": null,
    "created_at": "2021-01-28 13:07:35",
    "updated_at": "2021-01-28 13:12:03"
    },
    {
    "id": 3903,
    "user_id": 152,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2021-01-28 13:16:11",
    "end_ts": "2021-01-28 13:16:31",
    "total_ts": "00:00:17",
    "name": null,
    "creator": null,
    "stadium_name": null,
    "location": null,
    "geo_lon": null,
    "rotation": null,
    "team1": null,
    "team2": null,
    "geo_lat": null,
    "match_type": null,
    "player_image": null,
    "current_period": null,
    "finished": "",
    "team1_score": null,
    "team2_score": null,
    "created_at": "2021-01-28 13:16:11",
    "updated_at": "2021-01-28 13:16:31"
    },
    {
    "id": 3904,
    "user_id": 152,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2021-01-28 13:17:09",
    "end_ts": "2021-01-28 13:23:52",
    "total_ts": "00:00:00",
    "name": null,
    "creator": null,
    "stadium_name": null,
    "location": null,
    "geo_lon": null,
    "rotation": null,
    "team1": null,
    "team2": null,
    "geo_lat": null,
    "match_type": null,
    "player_image": null,
    "current_period": null,
    "finished": "",
    "team1_score": null,
    "team2_score": null,
    "created_at": "2021-01-28 13:17:09",
    "updated_at": "2021-01-28 13:23:52"
    }
    ]
    },
    {
    "id": 153,
    "player_name": "exercitationem harum",
    "profile_picture": "media/users/5ff44f23dbe3b1609846563.jpeg",
    "age": null,
    "gender": null,
    "position": [
    "Left Back"
    ],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": [
    {
    "id": 1732,
    "user_id": 153,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2019-10-14 13:12:32",
    "end_ts": "2019-10-14 13:57:01",
    "total_ts": null,
    "name": "FRIENDLY NOON MATCH",
    "creator": 2,
    "stadium_name": "CHAPIN",
    "location": "JEREZ,CADIZ,ES",
    "geo_lon": -6.120534,
    "rotation": null,
    "team1": "TEAM A",
    "team2": "TEAM B",
    "geo_lat": 36.689382,
    "match_type": "match",
    "player_image": "media/matches/yoBEkATZI5rCs0sPo0XZi0j2YCzYn91BqxLkWzqp.png",
    "current_period": 1,
    "finished": "1",
    "team1_score": 0,
    "team2_score": 0,
    "created_at": null,
    "updated_at": "2021-01-22 11:05:30"
    }
    ]
    },
    {
    "id": 154,
    "player_name": "Michael Jackson",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 155,
    "player_name": "M J",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 156,
    "player_name": "Hasnain Ali",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 157,
    "player_name": "Hasnain Ali",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 159,
    "player_name": "first name last name",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 160,
    "player_name": "player2 abc",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 161,
    "player_name": "Player Name",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 162,
    "player_name": "ui abc",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 163,
    "player_name": "abv def",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 164,
    "player_name": "argentina player",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 166,
    "player_name": "First Name Last Name",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 168,
    "player_name": "Diago Maradona",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 167,
    "player_name": "Lionel Messi",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 173,
    "player_name": "Cecilia Ritz",
    "profile_picture": "media/users/5fe098e0f0de91608554720.jpeg",
    "age": null,
    "gender": null,
    "position": [
    "Left Midfield"
    ],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 174,
    "player_name": "Bo-Jane Ladru",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 175,
    "player_name": "Judith de Bruin",
    "profile_picture": "media/users/5fe0ac60a992b1608559712.jpeg",
    "age": null,
    "gender": null,
    "position": [
    "Left Wing"
    ],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 176,
    "player_name": "Hasnain Ali",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 177,
    "player_name": "Umer Sheikh",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 178,
    "player_name": "Tariq Siddiqui",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 180,
    "player_name": "Kick Dwinger",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 181,
    "player_name": "Lars van Halteren",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [
    "Goal Keeper"
    ],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 182,
    "player_name": "Faizan Pervaiz",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 191,
    "player_name": "a b",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 194,
    "player_name": "claudia dwinger",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 196,
    "player_name": "Khurram Munir",
    "profile_picture": null,
    "age": null,
    "gender": "male",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 197,
    "player_name": "Sarmad Bhatti",
    "profile_picture": null,
    "age": null,
    "gender": "male",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 198,
    "player_name": "Khurram Munir",
    "profile_picture": null,
    "age": "26",
    "gender": "male",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 199,
    "player_name": "Sarmad Bhatti",
    "profile_picture": null,
    "age": "28",
    "gender": "male",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 200,
    "player_name": "Aaron Summers",
    "profile_picture": null,
    "age": "30",
    "gender": "man",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 203,
    "player_name": "1 Player ab",
    "profile_picture": null,
    "age": "6",
    "gender": "man",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 204,
    "player_name": "2 Player ab",
    "profile_picture": null,
    "age": "13",
    "gender": "mixed",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 206,
    "player_name": "fake acc",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 207,
    "player_name": "Jahanzeb Khan",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 217,
    "player_name": "pratikesh singh",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 219,
    "player_name": "First Last",
    "profile_picture": null,
    "age": "22",
    "gender": "man",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 221,
    "player_name": "Khurram Munir",
    "profile_picture": null,
    "age": "26",
    "gender": "man",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 222,
    "player_name": "Sarmad Bhatti",
    "profile_picture": null,
    "age": "28",
    "gender": "man",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 223,
    "player_name": "Khurram Munir",
    "profile_picture": null,
    "age": "26",
    "gender": "man",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 224,
    "player_name": "Sarmad Bhatti",
    "profile_picture": null,
    "age": "28",
    "gender": "man",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 225,
    "player_name": "Tests it tes",
    "profile_picture": null,
    "age": "43",
    "gender": "man",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 231,
    "player_name": "corrupti id",
    "profile_picture": null,
    "age": "15",
    "gender": "man",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    },
    {
    "id": 331,
    "player_name": "b a",
    "profile_picture": null,
    "age": "11",
    "gender": "man",
    "position": [],
    "points": 0,
    "suggestions": 0,
    "score": 0,
    "matches": []
    }
    ],
    "exercises": [
    {
    "id": 261,
    "title": "Test",
    "description": "Test",
    "image": null,
    "video": null,
    "leaderboard_direction": "asc",
    "badge": "non_ai",
    "android_exercise_type": "COUNTDOWN",
    "ios_exercise_type": "COUNTDOWN",
    "score": 0,
    "count_down_milliseconds": 3000,
    "use_questions": 0,
    "selected_camera_facing": "FRONT",
    "unit": null,
    "is_active": 1,
    "android_exercise_variation": 0,
    "ios_exercise_variation": 0,
    "question_count": 0,
    "answer_count": 0,
    "created_at": "2021-02-09 09:35:08",
    "updated_at": "2021-02-09 09:35:08",
    "deleted_at": null,
    "teams": [
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
    "pivot": {
    "exercise_id": 261,
    "team_id": 5
    }
    }
    ]
    },
    {
    "id": 262,
    "title": "name",
    "description": "aa",
    "image": null,
    "video": null,
    "leaderboard_direction": "asc",
    "badge": "non_ai",
    "android_exercise_type": "QUESTION",
    "ios_exercise_type": "QUESTION",
    "score": 0,
    "count_down_milliseconds": 3000,
    "use_questions": 0,
    "selected_camera_facing": "FRONT",
    "unit": null,
    "is_active": 1,
    "android_exercise_variation": 0,
    "ios_exercise_variation": 0,
    "question_count": 0,
    "answer_count": 0,
    "created_at": "2021-02-10 02:19:54",
    "updated_at": "2021-02-10 02:19:54",
    "deleted_at": null,
    "teams": [
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
    "pivot": {
    "exercise_id": 262,
    "team_id": 10
    }
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
    "pivot": {
    "exercise_id": 262,
    "team_id": 9
    }
    }
    ]
    },
    {
    "id": 263,
    "title": "Test",
    "description": "abc",
    "image": null,
    "video": null,
    "leaderboard_direction": "asc",
    "badge": "non_ai",
    "android_exercise_type": "QUESTION",
    "ios_exercise_type": "QUESTION",
    "score": 0,
    "count_down_milliseconds": 3000,
    "use_questions": 0,
    "selected_camera_facing": "FRONT",
    "unit": null,
    "is_active": 1,
    "android_exercise_variation": 0,
    "ios_exercise_variation": 0,
    "question_count": 0,
    "answer_count": 0,
    "created_at": "2021-02-10 02:24:34",
    "updated_at": "2021-02-10 02:24:34",
    "deleted_at": null,
    "teams": [
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
    "pivot": {
    "exercise_id": 263,
    "team_id": 10
    }
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
    "pivot": {
    "exercise_id": 263,
    "team_id": 11
    }
    }
    ]
    },
    {
    "id": 264,
    "title": "Name",
    "description": "ab",
    "image": null,
    "video": null,
    "leaderboard_direction": "asc",
    "badge": "non_ai",
    "android_exercise_type": "QUESTION",
    "ios_exercise_type": "QUESTION",
    "score": 0,
    "count_down_milliseconds": 3000,
    "use_questions": 0,
    "selected_camera_facing": "FRONT",
    "unit": null,
    "is_active": 1,
    "android_exercise_variation": 0,
    "ios_exercise_variation": 0,
    "question_count": 0,
    "answer_count": 0,
    "created_at": "2021-02-10 02:25:53",
    "updated_at": "2021-02-10 02:25:53",
    "deleted_at": null,
    "teams": [
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
    "pivot": {
    "exercise_id": 264,
    "team_id": 21
    }
    }
    ]
    },
    {
    "id": 265,
    "title": "Name 1",
    "description": "abc",
    "image": null,
    "video": null,
    "leaderboard_direction": "asc",
    "badge": "non_ai",
    "android_exercise_type": "QUESTION",
    "ios_exercise_type": "QUESTION",
    "score": 0,
    "count_down_milliseconds": 3000,
    "use_questions": 0,
    "selected_camera_facing": "FRONT",
    "unit": null,
    "is_active": 1,
    "android_exercise_variation": 0,
    "ios_exercise_variation": 0,
    "question_count": 0,
    "answer_count": 0,
    "created_at": "2021-02-10 02:28:14",
    "updated_at": "2021-02-10 02:28:14",
    "deleted_at": null,
    "teams": [
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
    "pivot": {
    "exercise_id": 265,
    "team_id": 7
    }
    }
    ]
    },
    {
    "id": 268,
    "title": "Test",
    "description": "Test",
    "image": null,
    "video": "media/exercises/videos/F89oJxKlXdSjU7SqJk6YFiVrmPlk40XvR7gJTwlV.mp4",
    "leaderboard_direction": "asc",
    "badge": "non_ai",
    "android_exercise_type": "QUESTION",
    "ios_exercise_type": "QUESTION",
    "score": 0,
    "count_down_milliseconds": 3000,
    "use_questions": 0,
    "selected_camera_facing": "FRONT",
    "unit": null,
    "is_active": 1,
    "android_exercise_variation": 0,
    "ios_exercise_variation": 0,
    "question_count": 0,
    "answer_count": 0,
    "created_at": "2021-02-15 16:09:14",
    "updated_at": "2021-02-15 16:09:14",
    "deleted_at": null,
    "teams": [
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
    "pivot": {
    "exercise_id": 268,
    "team_id": 5
    }
    }
    ]
    }
    ],
    "assignments": [
    {
    "id": 1,
    "trainer_user_id": 40,
    "difficulty_level": null,
    "title": "Mj Assignment",
    "description": "Do some moon walks",
    "image": "media/assignments/BdMaf2VywMPec0WxCt5SuUZVzAUoyx05FPA1uD1j.png",
    "deadline": "2020-10-29 00:00:00",
    "team_id": null,
    "created_at": "2020-10-28 10:44:50",
    "updated_at": "2020-10-28 10:44:50",
    "deleted_at": null
    },
    {
    "id": 15,
    "trainer_user_id": 40,
    "difficulty_level": null,
    "title": "Mj Assignment",
    "description": "Do some moon walks",
    "image": "media/assignments/BdMaf2VywMPec0WxCt5SuUZVzAUoyx05FPA1uD1j.png",
    "deadline": "2020-10-29 00:00:00",
    "team_id": null,
    "created_at": "2020-11-02 21:08:26",
    "updated_at": "2020-11-02 21:08:26",
    "deleted_at": null
    },
    {
    "id": 17,
    "trainer_user_id": 40,
    "difficulty_level": null,
    "title": "name",
    "description": "abc",
    "image": "",
    "deadline": "2020-11-17 00:00:00",
    "team_id": null,
    "created_at": "2020-11-02 21:27:12",
    "updated_at": "2020-11-02 21:27:12",
    "deleted_at": null
    },
    {
    "id": 19,
    "trainer_user_id": 40,
    "difficulty_level": null,
    "title": "Testing",
    "description": "Testing Description",
    "image": "media/assignments/PYqHtNjiE6unwziaF3WKTsKfWE4w2ibfAZAycTdi.png",
    "deadline": "2020-12-05 00:00:00",
    "team_id": null,
    "created_at": "2020-11-27 14:31:20",
    "updated_at": "2020-11-27 14:31:20",
    "deleted_at": null
    },
    {
    "id": 20,
    "trainer_user_id": 40,
    "difficulty_level": null,
    "title": "Work Hard",
    "description": "Do or die",
    "image": "media/assignments/d31lPIVZ2SI6ercMNbxFCkPZ5b0Yy8kTqLWisiUT.jpeg",
    "deadline": "2020-11-30 00:00:00",
    "team_id": null,
    "created_at": "2020-11-27 14:36:00",
    "updated_at": "2020-11-27 14:36:00",
    "deleted_at": null
    }
    ],
    "matches_data": [
    {
    "id": 3892,
    "user_id": 152,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2021-01-28 12:51:57",
    "end_ts": "2021-01-28 12:52:30",
    "total_ts": "00:00:28",
    "name": null,
    "creator": null,
    "stadium_name": null,
    "location": null,
    "geo_lon": null,
    "rotation": null,
    "team1": null,
    "team2": null,
    "geo_lat": null,
    "match_type": null,
    "player_image": null,
    "current_period": null,
    "finished": "",
    "team1_score": null,
    "team2_score": null,
    "created_at": "2021-01-28 12:51:57",
    "updated_at": "2021-01-28 12:52:30",
    "matches_stats": [
    {
    "id": 8697,
    "match_id": 3892,
    "stat_type_id": 1,
    "stat_value": 0.03,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 1,
    "name": "TOTAL_DISTANCE",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8698,
    "match_id": 3892,
    "stat_type_id": 15,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 15,
    "name": "NUMBER_STEPS",
    "max_stat_value": 2395,
    "disabled": 1
    }
    },
    {
    "id": 8699,
    "match_id": 3892,
    "stat_type_id": 4,
    "stat_value": 65.79,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 4,
    "name": "SPEED_WALKING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8700,
    "match_id": 3892,
    "stat_type_id": 17,
    "stat_value": 21.05,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 17,
    "name": "SPEED_RUNNING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8701,
    "match_id": 3892,
    "stat_type_id": 6,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 6,
    "name": "SPEED_SPRINTING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8702,
    "match_id": 3892,
    "stat_type_id": 7,
    "stat_value": 6.65,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 7,
    "name": "SPEED_MAX",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8703,
    "match_id": 3892,
    "stat_type_id": 2,
    "stat_value": 3.68,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 2,
    "name": "SPEED_AVG",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8704,
    "match_id": 3892,
    "stat_type_id": 11,
    "stat_value": 75,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 11,
    "name": "HR_MAX",
    "max_stat_value": 159,
    "disabled": 1
    }
    },
    {
    "id": 8705,
    "match_id": 3892,
    "stat_type_id": 3,
    "stat_value": 72.7895,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 3,
    "name": "HR_AVG",
    "max_stat_value": 109.75,
    "disabled": 1
    }
    },
    {
    "id": 8706,
    "match_id": 3892,
    "stat_type_id": 14,
    "stat_value": 1,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8707,
    "match_id": 3892,
    "stat_type_id": 14,
    "stat_value": 1,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    }
    ]
    },
    {
    "id": 3893,
    "user_id": 152,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2021-01-28 12:52:39",
    "end_ts": "2021-01-28 12:52:43",
    "total_ts": "00:00:01",
    "name": null,
    "creator": null,
    "stadium_name": null,
    "location": null,
    "geo_lon": null,
    "rotation": null,
    "team1": null,
    "team2": null,
    "geo_lat": null,
    "match_type": null,
    "player_image": null,
    "current_period": null,
    "finished": "",
    "team1_score": null,
    "team2_score": null,
    "created_at": "2021-01-28 12:52:39",
    "updated_at": "2021-01-28 12:52:43",
    "matches_stats": [
    {
    "id": 8708,
    "match_id": 3893,
    "stat_type_id": 1,
    "stat_value": 0.04,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 1,
    "name": "TOTAL_DISTANCE",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8709,
    "match_id": 3893,
    "stat_type_id": 15,
    "stat_value": 48,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 15,
    "name": "NUMBER_STEPS",
    "max_stat_value": 2395,
    "disabled": 1
    }
    },
    {
    "id": 8710,
    "match_id": 3893,
    "stat_type_id": 4,
    "stat_value": 100,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 4,
    "name": "SPEED_WALKING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8711,
    "match_id": 3893,
    "stat_type_id": 17,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 17,
    "name": "SPEED_RUNNING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8712,
    "match_id": 3893,
    "stat_type_id": 6,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 6,
    "name": "SPEED_SPRINTING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8713,
    "match_id": 3893,
    "stat_type_id": 7,
    "stat_value": 4,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 7,
    "name": "SPEED_MAX",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8714,
    "match_id": 3893,
    "stat_type_id": 2,
    "stat_value": 2.25,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 2,
    "name": "SPEED_AVG",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8715,
    "match_id": 3893,
    "stat_type_id": 11,
    "stat_value": 78,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 11,
    "name": "HR_MAX",
    "max_stat_value": 159,
    "disabled": 1
    }
    },
    {
    "id": 8716,
    "match_id": 3893,
    "stat_type_id": 3,
    "stat_value": 75,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 3,
    "name": "HR_AVG",
    "max_stat_value": 109.75,
    "disabled": 1
    }
    },
    {
    "id": 8717,
    "match_id": 3893,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8718,
    "match_id": 3893,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    }
    ]
    },
    {
    "id": 3895,
    "user_id": 152,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2021-01-28 13:06:28",
    "end_ts": "2021-01-28 13:07:32",
    "total_ts": "00:00:56",
    "name": null,
    "creator": null,
    "stadium_name": null,
    "location": null,
    "geo_lon": null,
    "rotation": null,
    "team1": null,
    "team2": null,
    "geo_lat": null,
    "match_type": null,
    "player_image": null,
    "current_period": null,
    "finished": "",
    "team1_score": null,
    "team2_score": null,
    "created_at": "2021-01-28 13:06:28",
    "updated_at": "2021-01-28 13:07:32",
    "matches_stats": [
    {
    "id": 8719,
    "match_id": 3895,
    "stat_type_id": 1,
    "stat_value": 0.05,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 1,
    "name": "TOTAL_DISTANCE",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8720,
    "match_id": 3895,
    "stat_type_id": 15,
    "stat_value": 929,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 15,
    "name": "NUMBER_STEPS",
    "max_stat_value": 2395,
    "disabled": 1
    }
    },
    {
    "id": 8721,
    "match_id": 3895,
    "stat_type_id": 4,
    "stat_value": 100,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 4,
    "name": "SPEED_WALKING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8722,
    "match_id": 3895,
    "stat_type_id": 17,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 17,
    "name": "SPEED_RUNNING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8723,
    "match_id": 3895,
    "stat_type_id": 6,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 6,
    "name": "SPEED_SPRINTING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8724,
    "match_id": 3895,
    "stat_type_id": 7,
    "stat_value": 3.8,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 7,
    "name": "SPEED_MAX",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8725,
    "match_id": 3895,
    "stat_type_id": 2,
    "stat_value": 2.44,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 2,
    "name": "SPEED_AVG",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8726,
    "match_id": 3895,
    "stat_type_id": 11,
    "stat_value": 136,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 11,
    "name": "HR_MAX",
    "max_stat_value": 159,
    "disabled": 1
    }
    },
    {
    "id": 8727,
    "match_id": 3895,
    "stat_type_id": 3,
    "stat_value": 85.7692,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 3,
    "name": "HR_AVG",
    "max_stat_value": 109.75,
    "disabled": 1
    }
    },
    {
    "id": 8728,
    "match_id": 3895,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8729,
    "match_id": 3895,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    }
    ]
    },
    {
    "id": 3897,
    "user_id": 152,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2021-01-28 13:07:35",
    "end_ts": "2021-01-28 13:12:03",
    "total_ts": "00:02:23",
    "name": null,
    "creator": null,
    "stadium_name": null,
    "location": null,
    "geo_lon": null,
    "rotation": null,
    "team1": null,
    "team2": null,
    "geo_lat": null,
    "match_type": null,
    "player_image": null,
    "current_period": null,
    "finished": "",
    "team1_score": null,
    "team2_score": null,
    "created_at": "2021-01-28 13:07:35",
    "updated_at": "2021-01-28 13:12:03",
    "matches_stats": [
    {
    "id": 8730,
    "match_id": 3897,
    "stat_type_id": 1,
    "stat_value": 0.15,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 1,
    "name": "TOTAL_DISTANCE",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8731,
    "match_id": 3897,
    "stat_type_id": 15,
    "stat_value": 1272,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 15,
    "name": "NUMBER_STEPS",
    "max_stat_value": 2395,
    "disabled": 1
    }
    },
    {
    "id": 8732,
    "match_id": 3897,
    "stat_type_id": 4,
    "stat_value": 100,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 4,
    "name": "SPEED_WALKING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8733,
    "match_id": 3897,
    "stat_type_id": 17,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 17,
    "name": "SPEED_RUNNING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8734,
    "match_id": 3897,
    "stat_type_id": 6,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 6,
    "name": "SPEED_SPRINTING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8735,
    "match_id": 3897,
    "stat_type_id": 7,
    "stat_value": 5.44,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 7,
    "name": "SPEED_MAX",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8736,
    "match_id": 3897,
    "stat_type_id": 2,
    "stat_value": 2.54,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 2,
    "name": "SPEED_AVG",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8737,
    "match_id": 3897,
    "stat_type_id": 11,
    "stat_value": 154,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 11,
    "name": "HR_MAX",
    "max_stat_value": 159,
    "disabled": 1
    }
    },
    {
    "id": 8738,
    "match_id": 3897,
    "stat_type_id": 3,
    "stat_value": 104.8462,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 3,
    "name": "HR_AVG",
    "max_stat_value": 109.75,
    "disabled": 1
    }
    },
    {
    "id": 8739,
    "match_id": 3897,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8740,
    "match_id": 3897,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    }
    ]
    },
    {
    "id": 3903,
    "user_id": 152,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2021-01-28 13:16:11",
    "end_ts": "2021-01-28 13:16:31",
    "total_ts": "00:00:17",
    "name": null,
    "creator": null,
    "stadium_name": null,
    "location": null,
    "geo_lon": null,
    "rotation": null,
    "team1": null,
    "team2": null,
    "geo_lat": null,
    "match_type": null,
    "player_image": null,
    "current_period": null,
    "finished": "",
    "team1_score": null,
    "team2_score": null,
    "created_at": "2021-01-28 13:16:11",
    "updated_at": "2021-01-28 13:16:31",
    "matches_stats": [
    {
    "id": 8741,
    "match_id": 3903,
    "stat_type_id": 1,
    "stat_value": 1.08,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 1,
    "name": "TOTAL_DISTANCE",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8742,
    "match_id": 3903,
    "stat_type_id": 15,
    "stat_value": 1498,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 15,
    "name": "NUMBER_STEPS",
    "max_stat_value": 2395,
    "disabled": 1
    }
    },
    {
    "id": 8743,
    "match_id": 3903,
    "stat_type_id": 4,
    "stat_value": 50,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 4,
    "name": "SPEED_WALKING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8744,
    "match_id": 3903,
    "stat_type_id": 17,
    "stat_value": 50,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 17,
    "name": "SPEED_RUNNING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8745,
    "match_id": 3903,
    "stat_type_id": 6,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 6,
    "name": "SPEED_SPRINTING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8746,
    "match_id": 3903,
    "stat_type_id": 7,
    "stat_value": 8.46,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 7,
    "name": "SPEED_MAX",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8747,
    "match_id": 3903,
    "stat_type_id": 2,
    "stat_value": 5.22,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 2,
    "name": "SPEED_AVG",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8748,
    "match_id": 3903,
    "stat_type_id": 11,
    "stat_value": 154,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 11,
    "name": "HR_MAX",
    "max_stat_value": 159,
    "disabled": 1
    }
    },
    {
    "id": 8749,
    "match_id": 3903,
    "stat_type_id": 3,
    "stat_value": 109.75,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 3,
    "name": "HR_AVG",
    "max_stat_value": 109.75,
    "disabled": 1
    }
    },
    {
    "id": 8750,
    "match_id": 3903,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8751,
    "match_id": 3903,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    }
    ]
    },
    {
    "id": 3904,
    "user_id": 152,
    "exercise_id": null,
    "level_id": null,
    "init_ts": "2021-01-28 13:17:09",
    "end_ts": "2021-01-28 13:23:52",
    "total_ts": "00:00:00",
    "name": null,
    "creator": null,
    "stadium_name": null,
    "location": null,
    "geo_lon": null,
    "rotation": null,
    "team1": null,
    "team2": null,
    "geo_lat": null,
    "match_type": null,
    "player_image": null,
    "current_period": null,
    "finished": "",
    "team1_score": null,
    "team2_score": null,
    "created_at": "2021-01-28 13:17:09",
    "updated_at": "2021-01-28 13:23:52",
    "matches_stats": [
    {
    "id": 8752,
    "match_id": 3904,
    "stat_type_id": 1,
    "stat_value": 1.33,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 1,
    "name": "TOTAL_DISTANCE",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8753,
    "match_id": 3904,
    "stat_type_id": 15,
    "stat_value": 1852,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 15,
    "name": "NUMBER_STEPS",
    "max_stat_value": 2395,
    "disabled": 1
    }
    },
    {
    "id": 8754,
    "match_id": 3904,
    "stat_type_id": 4,
    "stat_value": 73.78,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 4,
    "name": "SPEED_WALKING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8755,
    "match_id": 3904,
    "stat_type_id": 17,
    "stat_value": 15.73,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 17,
    "name": "SPEED_RUNNING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8756,
    "match_id": 3904,
    "stat_type_id": 6,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 6,
    "name": "SPEED_SPRINTING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8757,
    "match_id": 3904,
    "stat_type_id": 7,
    "stat_value": 7.91,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 7,
    "name": "SPEED_MAX",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8758,
    "match_id": 3904,
    "stat_type_id": 2,
    "stat_value": 4.2,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 2,
    "name": "SPEED_AVG",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8759,
    "match_id": 3904,
    "stat_type_id": 11,
    "stat_value": 156,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 11,
    "name": "HR_MAX",
    "max_stat_value": 159,
    "disabled": 1
    }
    },
    {
    "id": 8760,
    "match_id": 3904,
    "stat_type_id": 3,
    "stat_value": 89.0075,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 3,
    "name": "HR_AVG",
    "max_stat_value": 109.75,
    "disabled": 1
    }
    },
    {
    "id": 8761,
    "match_id": 3904,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8762,
    "match_id": 3904,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8763,
    "match_id": 3904,
    "stat_type_id": 1,
    "stat_value": 1.55,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 1,
    "name": "TOTAL_DISTANCE",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8764,
    "match_id": 3904,
    "stat_type_id": 15,
    "stat_value": 2157,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 15,
    "name": "NUMBER_STEPS",
    "max_stat_value": 2395,
    "disabled": 1
    }
    },
    {
    "id": 8765,
    "match_id": 3904,
    "stat_type_id": 4,
    "stat_value": 85.03,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 4,
    "name": "SPEED_WALKING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8766,
    "match_id": 3904,
    "stat_type_id": 17,
    "stat_value": 10.73,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 17,
    "name": "SPEED_RUNNING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8767,
    "match_id": 3904,
    "stat_type_id": 6,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 6,
    "name": "SPEED_SPRINTING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8768,
    "match_id": 3904,
    "stat_type_id": 7,
    "stat_value": 20.78,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 7,
    "name": "SPEED_MAX",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8769,
    "match_id": 3904,
    "stat_type_id": 2,
    "stat_value": 3.45,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 2,
    "name": "SPEED_AVG",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8770,
    "match_id": 3904,
    "stat_type_id": 11,
    "stat_value": 159,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 11,
    "name": "HR_MAX",
    "max_stat_value": 159,
    "disabled": 1
    }
    },
    {
    "id": 8771,
    "match_id": 3904,
    "stat_type_id": 3,
    "stat_value": 96.4929,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 3,
    "name": "HR_AVG",
    "max_stat_value": 109.75,
    "disabled": 1
    }
    },
    {
    "id": 8772,
    "match_id": 3904,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8773,
    "match_id": 3904,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8774,
    "match_id": 3904,
    "stat_type_id": 1,
    "stat_value": 1.55,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 1,
    "name": "TOTAL_DISTANCE",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8775,
    "match_id": 3904,
    "stat_type_id": 15,
    "stat_value": 2157,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 15,
    "name": "NUMBER_STEPS",
    "max_stat_value": 2395,
    "disabled": 1
    }
    },
    {
    "id": 8776,
    "match_id": 3904,
    "stat_type_id": 4,
    "stat_value": 85.04,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 4,
    "name": "SPEED_WALKING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8777,
    "match_id": 3904,
    "stat_type_id": 17,
    "stat_value": 10.66,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 17,
    "name": "SPEED_RUNNING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8778,
    "match_id": 3904,
    "stat_type_id": 6,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 6,
    "name": "SPEED_SPRINTING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8779,
    "match_id": 3904,
    "stat_type_id": 7,
    "stat_value": 20.78,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 7,
    "name": "SPEED_MAX",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8780,
    "match_id": 3904,
    "stat_type_id": 2,
    "stat_value": 3.45,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 2,
    "name": "SPEED_AVG",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8781,
    "match_id": 3904,
    "stat_type_id": 11,
    "stat_value": 159,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 11,
    "name": "HR_MAX",
    "max_stat_value": 159,
    "disabled": 1
    }
    },
    {
    "id": 8782,
    "match_id": 3904,
    "stat_type_id": 3,
    "stat_value": 97.1925,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 3,
    "name": "HR_AVG",
    "max_stat_value": 109.75,
    "disabled": 1
    }
    },
    {
    "id": 8783,
    "match_id": 3904,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8784,
    "match_id": 3904,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8785,
    "match_id": 3904,
    "stat_type_id": 1,
    "stat_value": 1.56,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 1,
    "name": "TOTAL_DISTANCE",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8786,
    "match_id": 3904,
    "stat_type_id": 15,
    "stat_value": 2166,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 15,
    "name": "NUMBER_STEPS",
    "max_stat_value": 2395,
    "disabled": 1
    }
    },
    {
    "id": 8787,
    "match_id": 3904,
    "stat_type_id": 4,
    "stat_value": 84.64,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 4,
    "name": "SPEED_WALKING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8788,
    "match_id": 3904,
    "stat_type_id": 17,
    "stat_value": 11.11,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 17,
    "name": "SPEED_RUNNING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8789,
    "match_id": 3904,
    "stat_type_id": 6,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 6,
    "name": "SPEED_SPRINTING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8790,
    "match_id": 3904,
    "stat_type_id": 7,
    "stat_value": 20.78,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 7,
    "name": "SPEED_MAX",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8791,
    "match_id": 3904,
    "stat_type_id": 2,
    "stat_value": 3.47,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 2,
    "name": "SPEED_AVG",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8792,
    "match_id": 3904,
    "stat_type_id": 11,
    "stat_value": 159,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 11,
    "name": "HR_MAX",
    "max_stat_value": 159,
    "disabled": 1
    }
    },
    {
    "id": 8793,
    "match_id": 3904,
    "stat_type_id": 3,
    "stat_value": 96.9931,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 3,
    "name": "HR_AVG",
    "max_stat_value": 109.75,
    "disabled": 1
    }
    },
    {
    "id": 8794,
    "match_id": 3904,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8795,
    "match_id": 3904,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8796,
    "match_id": 3904,
    "stat_type_id": 1,
    "stat_value": 1.72,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 1,
    "name": "TOTAL_DISTANCE",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8797,
    "match_id": 3904,
    "stat_type_id": 15,
    "stat_value": 2395,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 15,
    "name": "NUMBER_STEPS",
    "max_stat_value": 2395,
    "disabled": 1
    }
    },
    {
    "id": 8798,
    "match_id": 3904,
    "stat_type_id": 4,
    "stat_value": 83.63,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 4,
    "name": "SPEED_WALKING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8799,
    "match_id": 3904,
    "stat_type_id": 17,
    "stat_value": 9.61,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 17,
    "name": "SPEED_RUNNING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8800,
    "match_id": 3904,
    "stat_type_id": 6,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 6,
    "name": "SPEED_SPRINTING",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8801,
    "match_id": 3904,
    "stat_type_id": 7,
    "stat_value": 20.78,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 7,
    "name": "SPEED_MAX",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8802,
    "match_id": 3904,
    "stat_type_id": 2,
    "stat_value": 3.37,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 2,
    "name": "SPEED_AVG",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8803,
    "match_id": 3904,
    "stat_type_id": 11,
    "stat_value": 159,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 11,
    "name": "HR_MAX",
    "max_stat_value": 159,
    "disabled": 1
    }
    },
    {
    "id": 8804,
    "match_id": 3904,
    "stat_type_id": 3,
    "stat_value": 96.8707,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 3,
    "name": "HR_AVG",
    "max_stat_value": 109.75,
    "disabled": 1
    }
    },
    {
    "id": 8805,
    "match_id": 3904,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    },
    {
    "id": 8806,
    "match_id": 3904,
    "stat_type_id": 14,
    "stat_value": 0,
    "player_id": 152,
    "imei": "862549047758860",
    "created_at": null,
    "updated_at": null,
    "match_stat_types": {
    "id": 14,
    "name": "RECEIVED_IMPACTS",
    "max_stat_value": 100,
    "disabled": 1
    }
    }
    ]
    }
    ]
    }
    }
     *
     * @return JsonResponse
     */

    public function getClubData(Request $request){
        $pass = $request->pass;

        if($pass != 'D09jOP6!mN&uyi') {
            return Helper::apiErrorResponse(false, 'Invalid Password',new \stdClass());
        }

        $data = [];
        $clubs = Club::where('id', $request->club_id)->get();
        if(count($clubs) <= 0){
            $clubs = Club::get();
//            return Helper::apiErrorResponse(false, 'Club not found',new \stdClass());
        }
        foreach ($clubs as $club) {
            $club_id = $club->id ?? 0;
            $club = Club::find($club_id);
            $user_ids = [];
            if ($club) {
                $teams = Team::whereHas('clubs', function ($q) use ($club) {
                    return $q->where('club_id', $club->id);
                })->withCount('players')->get();
                $trainers = User::select('id', 'first_name', 'last_name', 'middle_name', 'profile_picture')->whereHas('clubs_trainers', function ($q) use ($club) {
                    return $q->where('club_id', $club->id);
                })->get();
                $players = User::role('player')
                    ->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.profile_picture', 'users.age', 'users.gender')
                    ->whereHas('clubs_players', function ($q) use ($club) {
                        $q->where('club_id', $club->id);
                    })
                    ->with([
                        'player' => function ($q1) {
                            $q1->select('id', 'players.user_id', 'players.position_id');
                        }
                    ])
                    ->with([
                        'leaderboards' => function ($q3) {
                            $q3->select('leaderboards.id', 'leaderboards.user_id', 'leaderboards.total_score');
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
                    ->orderBy('created_at')
                    ->get();

                if (count($players)) {
                    $user_ids = $players->pluck('id');
                    $players = $players->map(function ($item) {
                        $obj = new \stdClass();
                        $obj->id = $item->id;
                        $obj->player_name = $item->first_name . ' ' . $item->last_name;
                        $obj->profile_picture = $item->profile_picture;
                        $obj->age = $item->age;
                        $obj->gender = $item->gender;
                        $obj->position = $item->player->positions ?? [];
                        $obj->points = $item->leaderboards->total_score ?? 0;
                        $obj->suggestions = 0;
                        $scores = PlayerExercise::where('user_id', $item->id)->get();
                        $total_score = 0;
                        foreach ($scores as $q) {
                            $row = ExerciseScore::where('exercise_id', $q->exercise_id)->where('level_id', $q->level_id)->first();
                            if ($row) {
                                $total_score += $row->score;
                            }
                        }

                        $obj->score = $total_score;
                        //                    $obj->score = $total_score;
                        $obj->matches = Match::where('user_id', $item->id)->get();
                        return $obj;
                    });
                }
                $team_ids = $teams->pluck('id');
                $exercises = Exercise::whereHas('teams', function ($q) use ($team_ids) {
                    $q->whereIn('team_id', $team_ids);
                })->with('teams')->get();
                $trainer_ids = $trainers->pluck('id');
                $assignments = Assignment::whereIn('trainer_user_id', $trainer_ids)->get();
                $matches_data = Match::has('matches_stats')->with([
                    'matches_stats' => function ($q) {
                        $q->with('match_stat_types:matches_stats_types.id,matches_stats_types.name');
                    }
                ])->whereIn('user_id', $user_ids)->get();
                //            return  response()->json($matches_data);
                $data[] = ['club' => $club, 'teams' => $teams, 'trainers' => $trainers, 'players' => $players, 'exercises' => $exercises, 'assignments' => $assignments, 'matches_data' => $matches_data];
            }
        }

//        $this->generateCsv($data);
        return Helper::apiSuccessResponse(true, 'Club Found', $data);
        return Helper::apiErrorResponse(false, 'Club not found',new \stdClass());

    }

    public function generateCsv($data){
        if(!File::exists(public_path().'/exports')){
            File::makeDirectory(public_path().'/exports');
        }
        // open the file "demosaved.csv" for writing
        $file = fopen(public_path().'/exports/demosaved.csv', 'w');
        foreach ($data as $datum)
        {
            $club = $this->getClubsDataForCsv($datum['club']);
            $teams = $this->getTeamsDataForCsv($datum['teams']);
            $trainers = $this->getTrainersDataForCsv($datum['trainers']);
            $players = $this->getPlayersDataForCsv($datum['players']);
            $exercises = $this->getExercisesDataForCsv($datum['exercises']);
            $assignments = $this->getAssignmentsDataForCsv($datum['assignments']);
            $matches = $this->getMatchesDataForCsv($datum['matches_data']);

            $this->setClubDataInCsv($file,$club);
            $this->setTeamsDataInCsv($file,$teams);
            $this->setTrainersDataInCsv($file,$trainers);
            $this->setPlayersDataInCsv($file,$players);
            $this->setExercisesDataInCsv($file,$exercises);
            $this->setAssignmentDataInCsv($file,$assignments);
            $this->setMatchesDataInCsv($file,$matches);
        }
        fclose($file);
    }

    public function getClubsDataForCsv($club){
        $clubRecord = [
            'id' => $club->id,
            'title' => $club->title,
            'description' => $club->description,
            'image' => $club->image,
            'website' => $club->website,
            'type' => $club->type,
            'foundation_date' => $club->foundation_date,
            'registration_date' => $club->registration_date,
            'registration_no' => $club->registration_no,
            'address' => $club->address,
            'email' => $club->email,
            'country_id' => $club->country_id,
            'city' => $club->city,
            'street_address' => $club->street_address,
            'zip_code' => $club->zip_code,
        ];

        return ['record' => $clubRecord,'columns' => array_keys($clubRecord)];
    }

    public function getTeamsDataForCsv($teams){
        $teamsData = [];
        foreach ($teams as $team){
            $teamsData[] = [
                'id' => $team->id,
                'team_name' => $team->team_name,
                'image' => $team->image,
                'gender' => $team->gender,
                'team_type' => $team->team_type,
                'description' => $team->description,
                //'age_group' => $team->age_group,
                'min_age_group' => $team->min_age_group,
                'max_age_group' => $team->max_age_group,
                'players_count' => $team->players_count,
            ];
        }

        $columns = count($teams) > 0 ? array_keys($teamsData[0]): [];
        return ['record' => $teamsData, 'columns' => $columns];
    }

    public function getTrainersDataForCsv($trainers){
        $trainersData = [];
        foreach ($trainers as $trainer){
            $trainersData[] = [
                'id' => $trainer->id,
                'first_name' => $trainer->first_name,
                'last_name' => $trainer->last_name,
                'middle_name' => $trainer->middle_name,
                'profile_picture' => $trainer->profile_picture,
            ];
        }

        $columns = count($trainers) > 0 ? array_keys($trainersData[0]): [];
        return ['record' => $trainersData, 'columns' => $columns];
    }

    public function getPlayersDataForCsv($players){
        $playersData = [];
        foreach ($players as $player){
            $playersData[] = [
                'id' => $player->id,
                'player_name' => $player->player_name,
                'profile_picture' => $player->profile_picture,
                'age' => $player->age,
                'gender' => $player->gender,
                'points' => $player->points,
                'suggestions' => $player->suggestions,
                'score' => $player->score,
                'position' => $this->getPlayerPositionDataForCsv($player->positions()->pluck('name')->toArray()),
                'matches' => $this->getPlayersMatchDataForCsv($player->matches)
            ];
        }

        $columns = count($players) > 0 ? array_keys($playersData[0]): [];
        return ['record' => $playersData, 'columns' => $columns];
    }

    public function getPlayerPositionDataForCsv($position){
        $playerPosition = '[';
        foreach ($position as $post){
            $playerPosition .= $post.", ";
        }

        if($playerPosition == '['){
            $playerPosition .= ']';
        }else{
            $playerPosition = substr($playerPosition,0,strlen($playerPosition) - 2).']';
        }

        return $playerPosition;
    }

    public function getPlayersMatchDataForCsv($matches){
        $playerMatchesData = [];
        foreach ($matches as $match){
            $playerMatchesData[] = [
                'match_id' => $match->id,
                'user_id' => $match->user_id,
                'exercise_id' => $match->exercise_id,
                'level_id' => $match->level_id,
                'init_ts' => $match->init_ts,
                'end_ts' => $match->end_ts,
                'total_ts' => $match->total_ts,
                'name' => $match->name,
                'creator' => $match->creator,
                'stadium_name' => $match->stadium_name,
                'location' => $match->location,
                'geo_lon' => $match->geo_lon,
                'geo_lat' => $match->geo_lat,
                'rotation' => $match->rotation,
                'team1' => $match->team1,
                'team2' => $match->team2,
                'match_type' => $match->match_type,
                'player_image' => $match->player_image,
                'current_period' => $match->current_period,
                'finished' => $match->finished,
                'team1_score' => $match->team1_score,
                'team2_score' => $match->team2_score,
            ];
        }

        return $playerMatchesData;
    }

    public function getExercisesDataForCsv($exercises){
        $exercisesData = [];
        foreach ($exercises as $exercise){
            $exercisesData[] = [
                'id' => $exercise->id,
                'title' => $exercise->title,
                'description' => $exercise->description,
                'image' => $exercise->image,
                'video' => $exercise->video,
                'leaderboard_direction' => $exercise->leaderboard_direction,
                'badge' => $exercise->badge,
                'android_exercise_type' => $exercise->android_exercise_type,
                'ios_exercise_type' => $exercise->ios_exercise_type,
                'score' => $exercise->score,
                'count_down_milliseconds' => $exercise->count_down_milliseconds,
                'use_questions' => $exercise->use_questions,
                'selected_camera_facing' => $exercise->selected_camera_facing,
                'unit' => $exercise->unit,
                'is_active' => $exercise->is_active,
                'android_exercise_variation' => $exercise->android_exercise_variation,
                'ios_exercise_variation' => $exercise->ios_exercise_variation,
                'question_count' => $exercise->question_count,
                'answer_count' => $exercise->answer_count,
                'teams' => $this->getExerciseTeamsDataForCsv($exercise->teams),
            ];
        }

        $columns = count($exercises) > 0 ? array_keys($exercisesData[0]): [];
        return ['record' => $exercisesData, 'columns' => $columns];
    }

    public function getExerciseTeamsDataForCsv($teams){
        $exerciseTeams = [];
        foreach ($teams as $team){
            $exerciseTeams[] = [
                'team_id' => $team->id,
                'team_name' => $team->team_name,
                'team_image' => $team->image,
                'gender' => $team->gender,
                'team_type' => $team->team_type,
                'team_description' => $team->description,
                //'age_group' => $team->age_group,
                'min_age_group' => $team->min_age_group,
                'max_age_group' => $team->max_age_group
            ];
        }
        return $exerciseTeams;
    }

    public function getAssignmentsDataForCsv($assignments){
        $assignmentsData = [];
        foreach ($assignments as $assignment){
            $assignmentsData[] = [
                'id' => $assignment->id,
                'trainer_user_id' => $assignment->trainer_user_id,
                'difficulty_level' => $assignment->difficulty_level,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'image' => $assignment->image,
                'deadline' => $assignment->deadline,
            ];
        }

        $columns = count($assignments) > 0 ? array_keys($assignmentsData[0]): [];
        return ['record' => $assignmentsData, 'columns' => $columns];
    }

    public function getMatchesDataForCsv($matches){
        $matchesData = [];
        foreach ($matches as $match){
            $matchesData[] = [
                'id' => $match->id,
                'user_id' => $match->user_id,
                'exercise_id' => $match->exercise_id,
                'level_id' => $match->level_id,
                'init_ts' => $match->init_ts,
                'end_ts' => $match->end_ts,
                'total_ts' => $match->total_ts,
                'name' => $match->name,
                'creator' => $match->creator,
                'stadium_name' => $match->stadium_name,
                'location' => $match->location,
                'geo_lon' => $match->geo_lon,
                'geo_lat' => $match->geo_lat,
                'rotation' => $match->rotation,
                'team1' => $match->team1,
                'team2' => $match->team2,
                'match_type' => $match->match_type,
                'player_image' => $match->player_image,
                'current_period' => $match->current_period,
                'finished' => $match->finished,
                'team1_score' => $match->team1_score,
                'team2_score' => $match->team2_score,
                'stats' => $this->getMatchStatsDataForCsv($match->matches_stats)
            ];
        }

        $columns = count($matches) > 0 ? array_keys($matchesData[0]): [];
        return ['record' => $matchesData, 'columns' => $columns];
    }

    public function getMatchStatsDataForCsv($matchStats){
        $matchStatsData = [];
        foreach ($matchStats as $stats){
            $matchStatsData[] = [
                'stat_id' => $stats->id,
                'match_id' => $stats->match_id,
                'stat_type_id' => $stats->stat_type_id,
                'stat_value' => $stats->stat_value,
                'player_id' => $stats->player_id,
                'imei' => $stats->imei,
                'match_stat_type_id' => $stats->match_stat_types['id'],
                'match_stat_type_name' => $stats->match_stat_types['name'],
                'match_stat_type_max_value' => $stats->match_stat_types['max_stat_value'],
                'match_stat_type_max_disabled' => $stats->match_stat_types['disabled'],
            ];
        }

        return $matchStatsData;
    }

    public function setClubDataInCsv($file,$club){
        //Set club data
        fputcsv($file, ['Club Detail']);
        fputcsv($file, $club['columns']);
        fputcsv($file, $club['record']);
        fputcsv($file, []);
    }

    public function setTeamsDataInCsv($file,$teams){
        //Set teams data
        fputcsv($file, ['Teams']);
        if(count($teams['columns']) > 0){
            fputcsv($file, $teams['columns']);
            foreach ($teams['record'] as $team){
                fputcsv($file, $team);
            }
        }
        fputcsv($file, []);
    }

    public function setTrainersDataInCsv($file,$trainers){
        //Set trainers data
        fputcsv($file, ['Trainers']);
        if(count($trainers['columns']) > 0){
            fputcsv($file, $trainers['columns']);
            foreach ($trainers['record'] as $trainer){
                fputcsv($file, $trainer);
            }
        }
        fputcsv($file, []);
    }

    public function setPlayersDataInCsv($file,$players){
        //Set players data
        fputcsv($file, ['Players']);
        if(count($players['columns']) > 0){
            unset($players['columns'][9]);
            $columns = array_merge($players['columns'],[
                'match_id','user_id','exercise_id','level_id','init_ts','end_ts','total_ts','name','creator','stadium_name','location',
                'geo_lon','geo_lat','rotation','team1','team2','match_type','player_image','current_period','finished','team1_score',
                'team2_score']);
            fputcsv($file, $columns);
            foreach ($players['record'] as $player){
                if(count($player['matches']) > 0){
                    foreach ($player['matches'] as $match){
                        $playerRecord = $player;
                        unset($playerRecord['matches']);
                        $playerRecord = array_merge($playerRecord,$match);
                        fputcsv($file, $playerRecord);
                    }
                }else{
                    unset($player['matches']);
                    fputcsv($file, $player);
                }
            }
        }
        fputcsv($file, []);
    }

    public function setExercisesDataInCsv($file,$exercises){
        //Set Exercises data
        fputcsv($file, ['Exercises']);
        if(count($exercises['columns']) > 0){
            unset($exercises['columns'][17]);
            //$columns = array_merge($exercises['columns'],['team_id','team_name','team_image','gender','team_type','team_description','age_group']);
            $columns = array_merge($exercises['columns'],['team_id','team_name','team_image','gender','team_type','team_description', 'min_age_group', 'max_age_group']);
            fputcsv($file, $columns);
            foreach ($exercises['record'] as $exercise){
                if(count($exercise['teams']) > 0){
                    foreach ($exercise['teams'] as $team){
                        $teamRecord = $exercise;
                        unset($teamRecord['teams']);
                        $teamRecord = array_merge($teamRecord,$team);
                        fputcsv($file, $teamRecord);
                    }
                }else{
                    unset($exercise['teams']);
                    fputcsv($file, $exercise);
                }
            }
        }
        fputcsv($file, []);
    }

    public function setAssignmentDataInCsv($file,$assignments){
        //Set assignments data
        fputcsv($file, ['Assignments']);
        if(count($assignments['columns']) > 0){
            fputcsv($file, $assignments['columns']);
            foreach ($assignments['record'] as $assignment){
                fputcsv($file, $assignment);
            }
        }
        fputcsv($file, []);
    }

    public function setMatchesDataInCsv($file,$matches){
        //Set Matches data
        fputcsv($file, ['Matches']);
        if(count($matches['columns']) > 0){
            unset($matches['columns'][22]);
            $columns = array_merge($matches['columns'],['stat_id','match_id','stat_type_id','stat_value','player_id','imei','match_stat_type_id','match_stat_type_name','match_stat_type_max_value','match_stat_type_disabled']);
            fputcsv($file, $columns);
            foreach ($matches['record'] as $match){
                if(count($match['stats']) > 0){
                    foreach ($match['stats'] as $stat){
                        $matchRecord = $match;
                        unset($matchRecord['stats']);
                        $matchRecord = array_merge($matchRecord,$stat);
                        fputcsv($file, $matchRecord);
                    }
                }else{
                    unset($match['stats']);
                    fputcsv($file, $match);
                }
            }
        }
        fputcsv($file, []);
    }
}
