<?php

namespace App\Http\Controllers\Api;

use App\Battle;
use App\BattleResult;
use App\BattleRound;
use App\Contact;
use App\Events\GameIsReady;
use App\Events\PlayerIsReady;
use App\Exercise;
use App\GameMatchMaking;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\AddEditRequest;
use App\Http\Requests\Comment\DeleteRequest;
use App\User;
use App\UserBattle;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Http\Request;

/**
 * @authenticated
 * @group Battle
 *
 * APIs to manage battle
 */
class BattleController extends Controller
{


    /**
     *  Battle / My Upcoming Battles
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": [
    {
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
    ]
    }
     *
     * @return JsonResponse
     */
    public function myUpcomingBattles(Request $request)
    {
        $battles = UserBattle::with(['user', 'battle.rounds_exercises.exercise'])->where('user_id', Auth::user()->id)->whereHas('battle', function($q){
            $q->where(DB::raw("CONCAT(date, ' ', time)"), '>=', date('Y-m-d H:i'));
        })->where('status', '!=', 'declined')->orderBy('status', 'asc')->get();

        if(count($battles) > 0) {
            $battles = $battles->map(function ($battle) {
                $bat = new stdClass();
                $bat->id = $battle->battle->id;
                $bat->user = $battle->battle->user;
                $bat->rounds_exercises = $battle->battle->rounds_exercises;
                $bat->title = $battle->battle->title;
                $bat->date = $battle->battle->date;
                $bat->time = $battle->battle->time;
                $bat->rounds = $battle->battle->rounds;
                $bat->type = $battle->battle->type;
                $bat->created_at = $battle->battle->created_at;

                return $bat;
            });
        }

        return Helper::apiSuccessResponse(true, 'Success', $battles ?? []);
    }



    /**
     *  Battle / Create Match
     *
     * @response {
     *
     * }
     *
     *
     * @bodyParam  exercise_id[] optional (when select exercises make sure you select exercises for all rounds)
     * @bodyParam  type required (quick_match,battle_royale,best_of_three,best_of_five,best_of_seven)
     * @bodyParam  user_ids[] required
     * @bodyParam  date (optional)
     * @bodyParam  time (optional)
     * @bodyParam  title (optional)
     *
     * @return JsonResponse
     */
    public function createMatch(Request $request)
    {
        $validatedData = $request->validate([
            'exercise_id.*' => 'required|exists:exercises,id',
            'type' => 'required|in:quick_match,battle_royale,best_of_three,best_of_five,best_of_seven',
//            'user_ids' => 'required|array',
            'user_ids.*' => 'required|exists:users,id'
        ]);

        $data = Helper::getTimeDataRoundForMatch($request);
        $request = $data['request'];

        $response = Helper::createMatchNotification($request, $data['date'], $data['time'], $data['rounds']);

        return $response;
    }
}