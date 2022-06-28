<?php

namespace App\Http\Controllers\Api\BattleAI;

use App\BattleResult;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
/**
 * @authenticated
 * @group Battle
 *
 * APIs to manage battle
 */

class BattleController extends Controller
{
    //




    /**
     *  Battle / End Round
     *
     * @response {
     *
     * }
     *
     * @bodyParam  battle_id required
     * @bodyParam  round_id optional
     * @bodyParam  score required
     * @bodyParam  match_bonus required
     * @bodyParam  placement_bonus required
     * @bodyParam  position required
     * @bodyParam  play_time_mins required
     *
     * @return JsonResponse
     */
    public function endRound(Request $request)
    {
        $request->validate([
            'battle_id' => 'required|exists:battles,id',
//            'round_id' => 'required',
            'score' => 'required',
            'match_bonus' => 'required',
            'placement_bonus' => 'required',
            'position' => 'required',
            'play_time_mins' => 'required'
        ]);
        $battle = new BattleResult();
        $battle->user_id = Auth::user()->id;
        $battle->battle_id = $request->battle_id;
        $battle->round_id = $request->round_id;
        $battle->score = $request->score;
        $battle->match_bonus = $request->match_bonus;
        $battle->placement_bonus = $request->placement_bonus;
        $battle->position = $request->position;
        $battle->play_time_mins = $request->play_time_mins;
        $battle->save();
        return Helper::apiSuccessResponse(true, 'Success', $battle);
    }

}
