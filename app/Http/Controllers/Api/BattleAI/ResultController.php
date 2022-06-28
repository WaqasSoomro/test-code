<?php

namespace App\Http\Controllers\Api\BattleAI;

use App\Battle;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


/**
 * @authenticated
 * @group Battle
 *
 * APIs to manage battle
 */

class ResultController extends Controller
{
    //



    /**
     *  Battle / My Battle Results
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": {
    "id": 1,
    "user": {
    "id": 87,
    "nationality_id": 1,
    "first_name": "Khurram",
    "middle_name": "''",
    "last_name": "Munir",
    "surname": null,
    "email": "kmunir3822@gmail.com",
    "humanox_username": null,
    "humanox_user_id": null,
    "humanox_pin": null,
    "humanox_auth_token": null,
    "phone": "+923242428594",
    "gender": "man",
    "language": null,
    "address": null,
    "profile_picture": "media/users/5fbbc9d904f4c1606142425.jpeg",
    "date_of_birth": null,
    "age": "18",
    "badge_count": 0,
    "verification_code": "178904",
    "verified_at": "2021-01-27 09:03:38",
    "active": 0,
    "status_id": 1,
    "who_created": null,
    "created_at": "2020-11-23 13:18:55",
    "updated_at": "2021-02-02 13:12:10",
    "deleted_at": null
    },
    "exercise": {
    "id": 2,
    "title": "10 Cones dribble (R)",
    "description": "Weave/Zigzag through the cones using both feet, weave up and down the cones, make sure you keep the ball close to your feet",
    "image": "media/exercise/images/JOGO_D3.3.jpeg",
    "video": "media/exercise/DV2/JOGO_D3.3_v2.mp4",
    "leaderboard_direction": "asc",
    "badge": "non_ai",
    "android_exercise_type": "INFINITE",
    "ios_exercise_type": "INFINITE",
    "score": 100,
    "count_down_milliseconds": 1000,
    "use_questions": 0,
    "selected_camera_facing": "FRONT",
    "unit": null,
    "is_active": 1,
    "created_at": "-0001-11-30 00:00:00",
    "updated_at": "2020-10-08 14:56:18",
    "deleted_at": null
    },
    "title": "Test",
    "date": "2021-02-23",
    "time": "13:25",
    "rounds": 3,
    "type": "best_of_three",
    "created_at": "2021-02-22T02:37:32.000000Z"
    }
    }
     * @queryParam  battle_id required
     * @return JsonResponse
     */
    public function myBattleResults(Request $request)
    {

        $this->validate($request,[
            'battle_id'=>'required'
        ]);


//        $battle = Battle::with(['results'=>function($result) use($request){
//            $result->orderBy('position','asc')
//                ->with('user:id,first_name,last_name,profile_picture,online_status');
//        }])->find($request->battle_id);



        $battle = Battle::with(['rounds_exercises.exercise', 'results' => function($q) {
            $q->orderBy('position','asc')
                ->with('user:id,first_name,last_name,profile_picture,online_status');
        }])->find($request->battle_id);

        if($battle) {
            $bat = new \stdClass();
            $bat->id = $battle->id;
            $bat->user = $battle->user;
            $bat->rounds_exercises = $battle->rounds_exercises;
            $bat->title = $battle->title;
            $bat->date = $battle->date;
            $bat->time = $battle->time;
            $bat->rounds = $battle->rounds;
            $bat->type = $battle->type;
            $bat->created_at = $battle->created_at;
            if($battle->type=='quick_match'){
                $bat->result = $battle->results->where('user_id',auth()->user()->id)->values();
            }else{
                $bat->result = $battle->results;
            }
            return Helper::apiSuccessResponse(true, 'Success', $bat);
        }
        return Helper::apiErrorResponse(false, 'no battle found', new \stdClass());
    }




    /**
     *  Battle / Get Battle Result
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": {
    "id": 1,
    "title": "Battle One",
    "date": null,
    "time": null,
    "rounds": 1,
    "type": "quick_match",
    "created_at": "2021-03-04T02:03:34.000000Z",
    "result": [
    {
    "id": 3,
    "battle_id": 1,
    "user_id": 3,
    "round": 2,
    "score": 23,
    "match_bonus": 2,
    "placement_bonus": 21,
    "position": 1,
    "play_time_mins": 321,
    "created_at": "2021-03-05 01:13:28",
    "updated_at": "2021-03-05 01:37:49",
    "user": {
    "id": 3,
    "first_name": "Hasnain",
    "last_name": "Ali",
    "profile_picture": "media/users/5f7b29180ec451601906968.jpeg",
    "online_status": 0
    }
    },
    {
    "id": 2,
    "battle_id": 1,
    "user_id": 3,
    "round": 1,
    "score": 12,
    "match_bonus": 23,
    "placement_bonus": 23,
    "position": 2,
    "play_time_mins": 23,
    "created_at": "2021-03-05 00:45:44",
    "updated_at": "2021-03-05 01:37:50",
    "user": {
    "id": 3,
    "first_name": "Hasnain",
    "last_name": "Ali",
    "profile_picture": "media/users/5f7b29180ec451601906968.jpeg",
    "online_status": 0
    }
    },
    {
    "id": 1,
    "battle_id": 1,
    "user_id": 1,
    "round": 1,
    "score": 13,
    "match_bonus": 23,
    "placement_bonus": 2,
    "position": 3,
    "play_time_mins": 23,
    "created_at": "2021-03-05 00:28:19",
    "updated_at": "2021-03-05 01:37:54",
    "user": {
    "id": 1,
    "first_name": "Muhammed",
    "last_name": "shahzaib",
    "profile_picture": "media/users/5fa27263a93271604481635.jpeg",
    "online_status": 1
    }
    }
    ]
    }
    }
     * @queryParam  battle_id required
     * @return JsonResponse
     */
    public function getBattleResults(Request $request)
    {
        $this->validate($request,[
            'battle_id'=>'required'
        ]);
        $battle = Battle::with(['results'=>function($result) use($request){
            $result->orderBy('position','asc')
                ->with('user:id,first_name,last_name,profile_picture,online_status');
        }])->find($request->battle_id);
        if($battle){
            $bat = new \stdClass();
            $bat->id = $battle->id;
//            $bat->user = $battle->user;
//            $bat->rounds_exercises = $battle->rounds_exercises;
            $bat->title = $battle->title;
            $bat->date = $battle->date;
            $bat->time = $battle->time;
            $bat->rounds = $battle->rounds;
            $bat->type = $battle->type;
            $bat->created_at = $battle->created_at;
            $bat->result = $battle->results;
            return Helper::apiSuccessResponse(true, 'Success', $bat);
        }
        return Helper::apiErrorResponse(false, 'not found', new \stdClass());
    }




    /**
        Battle / Get Battle Round Result
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Success",
            "Result": {
                "id": 1,
                "user_id": 3,
                "exercise_id": 2,
                "title": "ABC",
                "date": "2021-03-01",
                "time": "00:00",
                "rounds": 3,
                "type": "best_of_three",
                "created_at": "2021-02-24 20:32:51",
                "updated_at": "2021-03-03 21:00:34",
                "channel_id": null,
                "status": null,
                "team_id": null,
                "results": [
                    {
                        "id": 1,
                        "battle_id": 1,
                        "user_id": 2,
                        "round": 1,
                        "score": 30,
                        "match_bonus": 1,
                        "placement_bonus": 1,
                        "position": 1,
                        "play_time_mins": 12,
                        "created_at": "2021-03-03 21:09:27",
                        "updated_at": "2021-03-31 22:27:15",
                        "user": {
                            "id": 2,
                            "first_name": "Fatima",
                            "last_name": "Sultana",
                            "profile_picture": "media/users/606c4314623c11617707796.jpeg",
                            "online_status": "1"
                        }
                    }
                ]
            }
        }
        
        @queryParam battle_id required
        @queryParam round required
    */
    
    public function getBattleRoundResults(Request $request)
    {
        $response = (new Battle())->roundResults($request);

        return $response;
    }
}