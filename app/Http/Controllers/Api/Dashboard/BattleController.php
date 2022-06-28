<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Battle;
use App\BattleResult;
use App\BattleRound;
use App\Contact;
use App\Exercise;
use App\GameMatchMaking;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\AddEditRequest;
use App\Http\Requests\Comment\DeleteRequest;
use App\Team;
use App\User;
use App\UserBattle;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Http\Request;

/**
 * @authenticated
 * @group Dashboard/Battle
 *
 * APIs to manage comments
 *
 */
class BattleController extends Controller
{
    /**
     * get Battles
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": [
     * {
     * "id": 25,
     * "user_id": 88,
     * "team_id": null,
     * "exercise_id": null,
     * "title": null,
     * "date": "2021-02-25",
     * "time": "14:30",
     * "rounds": 3,
     * "type": "best_of_three",
     * "created_at": "2021-02-25 14:30:20",
     * "updated_at": "2021-02-25 14:30:20",
     * "team": null,
     * "rounds_exercises": [
     * {
     * "id": 40,
     * "battle_id": 25,
     * "exercise_id": 89,
     * "round": 1,
     * "created_at": "2021-02-25 14:30:20",
     * "updated_at": "2021-02-25 14:30:20"
     * },
     * {
     * "id": 41,
     * "battle_id": 25,
     * "exercise_id": 215,
     * "round": 2,
     * "created_at": "2021-02-25 14:30:20",
     * "updated_at": "2021-02-25 14:30:20"
     * },
     * {
     * "id": 42,
     * "battle_id": 25,
     * "exercise_id": 277,
     * "round": 3,
     * "created_at": "2021-02-25 14:30:20",
     * "updated_at": "2021-02-25 14:30:20"
     * }
     * ]
     * },
     * {
     * "id": 1,
     * "user_id": 88,
     * "team_id": 5,
     * "exercise_id": 2,
     * "title": "Test",
     * "date": "2021-02-21",
     * "time": "21:47",
     * "rounds": 3,
     * "type": "best_of_three",
     * "created_at": "2021-02-22 02:37:32",
     * "updated_at": "2021-02-25 19:06:23",
     * "team": {
     * "id": 5,
     * "team_name": "JOGO",
     * "age_group": "16",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "image": "https://jogobucket.s3.eu-west-2.amazonaws.com/media/users/ic_launcher_APP.png",
     * "description": "Together we revolutionise the world of football and help youth players reach their full potential\r\n",
     * "team_type": "field",
     * "gender": "man",
     * "created_at": null,
     * "updated_at": null,
     * "deleted_at": null,
     * "players_count": 80
     * },
     * "rounds_exercises": []
     * }
     * ]
     * }
     *
     * @return JsonResponse
     */
    public function getBattles(Request $request)
    {
        $battles = Battle::with(['team' => function ($q) {
            $q->withCount('players');
        }, 'rounds_exercises'])->where('user_id', Auth::user()->id)->latest()->get();

        return Helper::apiSuccessResponse(true, 'Success', $battles ?? []);
    }

    /**
     *  Create Match
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
            'exercise_id' => 'required|array',
            'exercise_id.*' => 'required|exists:exercises,id',
            'type' => 'required|in:quick_match,battle_royale,best_of_three,best_of_five,best_of_seven',
//            'user_ids' => 'required|array',
            'team_id' => 'exists:teams,id',
            'user_ids.*' => 'required|exists:users,id'
        ]);

        $data = Helper::getTimeDataRoundForMatch($request);
        $request = $data['request'];

        if ($request->team_id) {
            $team = Team::with(['players'])->find($request->team_id);

            $request->user_ids = $team->players->pluck('id')->toArray();
        }


        $response = Helper::createMatchNotification($request, $data['date'], $data['time'], $data['rounds']);

        return $response;
    }
}