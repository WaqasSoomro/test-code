<?php

namespace App\Http\Controllers\Api\BattleAI;

use App\Battle;
use App\BattleRound;
use App\Events\GameIsReady;
use App\Events\PlayerIsReady;
use App\Exercise;
use App\GameMatchMaking;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @authenticated
 * @group Battle
 *
 * APIs to manage battle
 */
class LobbyController extends Controller
{
    //


    /**
     *  Get Battle Channel /Lobby
     * Ref to this url for details https://docs.google.com/document/d/1a1zWYqd9rN6s5FWBkRwwnyXL4ZYGbTTt1QFR6NdN3NI/edit?usp=sharing
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": {
    "game_channel_id": "game_1616822658"
    }
    }
     *
     *
     * @queryParam   game_type required IN (quick_match,battle_royale,best_of_three,best_of_five,best_of_seven)
     * @queryParam   max_players required integer,
     * @queryParam   exercise_id optional,
     *
     * @return JsonResponse
     */

    public function getBattleChannel(Request $request){
        $this->validate($request,
            [
                'game_type'=>'required|in:quick_match,battle_royale,best_of_three,best_of_five,best_of_seven',
                'max_players'=>'required'
            ]
        );

        $game_player_match = GameMatchMaking::whereDoesntHave('battle.battle_invites')->where('max_players','>','avb_players')->where('battle_status','pending');
        if($request->exercise_id){
            $game_player_match = $game_player_match->where('exercise_id',$request->exercise_id);
        }
        if($request->game_type){
            $game_player_match = $game_player_match->where('game_type',$request->game_type);
        }
        $game_player_match= $game_player_match->first();
        //add game matching logic here eg match players by rank , or by other attributes
        $game_id = "game_".time();
        if($game_player_match && $game_player_match->id){
            $game_id = $game_player_match->id;
        }
        if(!$game_player_match){
            $game_player_match = new GameMatchMaking();
            $game_player_match->id = $game_id;
            $game_player_match->game_match_id = $game_id;
            $game_player_match->host_id =auth()->user()->id;
            $game_player_match->game_type = $request->game_type;
            $game_player_match->exercise_id = $request->exercise_id?$request->exercise_id:Exercise::where('badge', 'ai')->inRandomOrder()->first()->id;
            $game_player_match->battle_status = "pending";
            $game_player_match->max_players = isset($request->max_players)?$request->max_players:2;
            $game_player_match->save();
        }
        $data = ['game_channel_id'=>$game_id,'host_id'=>$game_player_match->host_id,'max_players'=>$game_player_match->max_players];
        return Helper::apiSuccessResponse(true, 'Success', $data);
    }



    /**
     *  Get Ready
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": {
    }
    }
     *
     *
     * @queryParam   game_channel_id required
     *
     * @return JsonResponse
     */
    public function getReady(Request $request){
        $this->validate($request,[
            'game_channel_id'=>'required'
        ]);
        $get_channel =   GameMatchMaking::find($request->game_channel_id);
        if($get_channel){
            $player = DB::table('game_players_matched')->where('player_id',auth()->user()->id)->first();
            if(!$player){
                return Helper::apiErrorResponse(false, 'invalid game channel',new \stdClass());
            }
            DB::table('game_players_matched')->where('player_id','=',auth()->user()->id)
                ->where('game_match_id','=',$request->game_channel_id)
                ->update([
                    'is_ready'=>1
                ]);
            $someone_is_unready = DB::table('game_players_matched')->where('is_ready',0)
                ->where('game_match_id',$request->game_channel_id)->first();
            if(!$someone_is_unready){
                if(!$get_channel->battle_id){
                    $battle = $this->createBattle($get_channel->game_type);
                    $get_channel->battle_id = $battle->id;
                    $get_channel->save();
                    broadcast(new GameIsReady($battle , $request->game_channel_id));
                }
            }
            broadcast(new PlayerIsReady(auth()->user(),$get_channel));
            return Helper::apiSuccessResponse(true, 'Success',new \stdClass());
        }
        return Helper::apiErrorResponse(false, 'invalid game channel',new \stdClass());
    }

    /**
     *  Force Ready
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": {
    }
    }
     *
     *
     * @queryParam   game_channel_id required
     *
     * @return JsonResponse
     */
    public function forceReady(Request $request){
        $this->validate($request,[
            'game_channel_id'=>'required'
        ]);
        $get_channel =   GameMatchMaking::find($request->game_channel_id);
        if($get_channel && $get_channel->host_id==auth()->user()->id){
            DB::table('game_players_matched')->where('game_match_id','=',$request->game_channel_id)->update([
                'is_ready'=>1
            ]);

            if(!$get_channel->battle_id){
                $battle = $this->createBattle($get_channel);
                $get_channel->battle_id = $battle->id;
                $get_channel->save();
            }else{
                $battle = Battle::find($get_channel->battle_id);
                if($battle){
                    broadcast(new GameIsReady($battle , $request->game_channel_id));
                }
            }
            return Helper::apiSuccessResponse(true, 'Success',new \stdClass());
        }
        return Helper::apiErrorResponse(false, 'invalid game channel',new \stdClass());
    }



    public function createBattle($game_channel){

        $rounds = 1;
        if ($game_channel->game_type == 'best_of_three') {
            $rounds = 3;
        } elseif ($game_channel->game_type == 'best_of_five') {
            $rounds = 5;
        } elseif ($game_channel->game_type == 'best_of_seven') {
            $rounds = 7;
        }
        $battle = new Battle();
        $battle->type = $game_channel->game_type;
        $battle->date = date('Y-m-d');
        $battle->time = date('H:i');
        $battle->rounds = $rounds;
        $battle->title = '';
        $battle->save();


        //create battle rounds

        for($i=1; $i<=$rounds;$i++){
            $rounds_exercises = new BattleRound();
            $rounds_exercises->battle_id = $battle->id;
            $rounds_exercises->exercise_id = $game_channel->exercise_id;
            $rounds_exercises->round = $i;
            $rounds_exercises->save();
        }
        return $battle;
    }
}
