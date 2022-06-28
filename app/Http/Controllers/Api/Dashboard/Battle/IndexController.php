<?php

namespace App\Http\Controllers\Api\Dashboard\Battle;

use App\Battle;
use App\BattleInvite;
use App\BattleRound;
use App\GameMatchMaking;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Team;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


/**
 * @authenticated
 * @group Dashboard/Battle
 *
 * APIs to manage comments
 *
 */
class IndexController extends Controller
{

    /**
     * get Battles
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": [
    {
    "id": 24,
    "user_id": 40,
    "exercise_id": null,
    "title": "New Battle",
    "date": "2021-04-06",
    "time": "20:09",
    "rounds": 1,
    "type": "battle_royale",
    "created_at": "2021-04-06 20:09:26",
    "updated_at": "2021-04-06 20:09:26",
    "channel_id": null,
    "status": null,
    "description": "Lorem ipsum",
    "team_id": 5,
    "position": "left",
    "line": "straight",
    "players_count": 67,
    "team": {
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
    "deleted_at": null
    }
    }
    ]
    }
     *
     * @return JsonResponse
     */

    public function getBattles(){
        $battles = Battle::with(['team'])->withCount('battle_invites as players_count')->where('user_id', Auth::user()->id)->latest()->get();
        return Helper::apiSuccessResponse(true, 'Success', $battles ?? []);
    }



    /**
     * create Battle
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result":{}
    }
     * @bodyParam title string required
     * @bodyParam type string required
     * @bodyParam date date
     * @bodyParam time h:i
     * @bodyParam team_id integer required
     * @bodyParam line string required
     * @bodyParam position string required
     * @bodyParam players array optional
     * @bodyParam exercises array required [  [round:1,exercise_id:1,target_no:1],[round:2,exercise_id:2,target_no:2]  ]
     *
     * @return JsonResponse
     */


    public function createBattle(Request  $request){
        /*
         * 1- Create Battle
         * 2- Create Rounds For Battle
         * 3- Create Battle Channel
         * 4- Invite/Assign Battle to players
         *
         * */
        //create a battle

        DB::beginTransaction();
        try{
            $battle = new Battle();
            $battle->title = $request->title;
            $battle->description = $request->description;
            $battle->team_id = $request->team_id;
            $battle->user_id = auth()->user()->id;
            $battle->position = $request->position;
            $battle->line = $request->line;
            $battle->type = $request->type;
            $battle->status = "pending";
            $date = date('Y-m-d');
            $time = date('H:i');
            if ($request->date) {
                $date = date('Y-m-d', strtotime($request->date));
            }
            if ($request->time) {
                $time = date('H:i', strtotime($request->time));
            }
            $battle->date = $date;
            $battle->time = $time;
            $battle->save();

            /*
             * Format For exercise
             *
             *
            $exercises = [
                [ 'round'=>1,'exercise_id'=>2,'target_no'=>2],
                [ 'round'=>2,'exercise_id'=>2,'target_no'=>2],
                [ 'round'=>3,'exercise_id'=>2,'target_no'=>2],
                [ 'round'=>4,'exercise_id'=>2,'target_no'=>2],
            ];
            **/

            foreach ($request->exercises as $exercise){
                $battle_round = new BattleRound();
                $battle_round->battle_id = $battle->id;
                $battle_round->exercise_id= $exercise['exercise_id'];
                $battle_round->round= $exercise['round'];
                $battle_round->target_no= $exercise['target_no'];
                $battle_round->save();
            }

            $battle_players =[];
            //get players for battle
            if(isset($request->team_id)){
                $team = Team::with('players')->find($request->team_id);
                if($team) $battle_players = $team->players;
            }

            if(isset($request->players) && is_array($request->players) && count($request->players)){
                $battle_players = User::whereIn('id',$request->players)->get();
            }

            //create game channel
            $channel_id = "game_".time();
            $game_player_match = new GameMatchMaking();
            $game_player_match->id = $channel_id;
            $game_player_match->game_match_id = $channel_id;
            $game_player_match->game_type = $request->type;
            $game_player_match->battle_id = $battle->id;
            $game_player_match->max_players = $battle_players->count();
            $game_player_match->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return Helper::apiErrorResponse(false,$e->getMessage().' line no '.$e->getLine(), new \stdClass());
        }

        //send invitations
        foreach ($battle_players as $battle_player){
            $battle_invite = new BattleInvite();
            $battle_invite->invited_by = auth()->user()->id;
            $battle_invite->user_id = $battle_player->id;
            $battle_invite->status = "assigned";
            $battle_invite->battle_id = $battle->id;
            $battle_invite->save();
//            $this->sendPushNotifications($battle_player,$battle);
//            broadcast(new BattleInvitation($battle_invite));
        }
        return Helper::apiSuccessResponse(true,'success',new \stdClass());
    }

    public function sendPushNotifications($user,$battle)
    {
        $msg['en'] = 'You have an invitation for jogo battle by ' . Auth::user()->first_name;
        $msg['nl'] = 'Je hebt een uitnodiging voor jogo battle door ' . Auth::user()->first_name;
        Helper::processNotification(Auth::user()->id,$user->id,'battle/invite',$battle->id,'BattleInvite',$msg,$user->badge_count,$user->user_devices);
    }




    /**
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
