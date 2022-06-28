<?php

namespace App\Http\Controllers\Api\BattleAI;
use App\Battle;
use App\BattleInvite;
use App\BattleRound;
use App\Events\BattleInvitation;
use App\Events\BattleInvitationResponded;
use App\Exercise;
use App\GameMatchMaking;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


/**
 * @authenticated
 * @group Battle
 *
 * APIs to manage battle
 */

class IndexController extends Controller
{




    /**
     *  Battle / Invite Friends
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": {
    "game_channel_id": "game_1617117472",
    "host_id": 2,
    "max_players": 4
    }
    }
     *
     *
     * @bodyParam  exercise_id optional
     * @bodyParam  type required (quick_match,battle_royale,best_of_three,best_of_five,best_of_seven)
     * @bodyParam  user_ids[] array required
     *
     * @return JsonResponse
     */
    public function inviteFriends(Request  $request){
        $this->validate($request,[
            'type' => 'required|in:quick_match,battle_royale,best_of_three,best_of_five,best_of_seven',
            'user_ids.*' => 'required',
        ]);

        if($request->type!=='quick_match' && !$request->exercise_id ){
            return Helper::apiErrorResponse(false,'select exercise_id when type not equal to quick match',new \stdClass());
        }

        $users = $request->user_ids;
        if ($request->type == 'best_of_three') {
            $rounds = 3;
        } elseif ($request->type == 'best_of_five') {
            $rounds = 5;
        } elseif ($request->type == 'best_of_seven') {
            $rounds = 7;
        } else {
            $rounds = 1;
        }
        if($request->type==='quick_match'){
            $exercises = Exercise::where('badge', 'ai')->pluck('id', 'id')->toArray();
            $exercise_id = array_rand($exercises, $rounds);
        }else{
            $exercise_id = $request->exercise_id;
        }
        //create game channel
        $game_id = "game_".time();

        //create battle
        $battle = new Battle();
        $battle->user_id = Auth::user()->id;
        $battle->exercise_id = $exercise_id;
        $battle->type = $request->type;
        $battle->date = date('Y-m-d');
        $battle->time = date('H:i');
        $battle->rounds = $rounds;
        $battle->title = $request->title;
        $battle->channel_id = $game_id;
        $battle->save();



        //create game channel

        $game_player_match = new GameMatchMaking();
        $game_player_match->id = $game_id;
        $game_player_match->game_match_id = $game_id;
        $game_player_match->host_id =auth()->user()->id;
        $game_player_match->game_type = $request->type;
        $game_player_match->battle_id = $battle->id;
        $game_player_match->max_players = count($users);
        $game_player_match->save();



        //create battle rounds

        for($i=1; $i<=$rounds;$i++){
            $rounds_exercises = new BattleRound();
            $rounds_exercises->battle_id = $battle->id;
            $rounds_exercises->exercise_id = $exercise_id;
            $rounds_exercises->round = $i;
            $rounds_exercises->save();
        }

        //invite users
        $users = User::whereIn('id',$request->user_ids)->get();
        foreach ($users as $user){
           if($user->id!=auth()->user()->id){
               $battle_invite = new BattleInvite();
               $battle_invite->invited_by = auth()->user()->id;
               $battle_invite->user_id = $user->id;
               $battle_invite->status = "pending";
               $battle_invite->battle_id = $battle->id;
               $battle_invite->save();
               $this->sendPushNotifications($user,$battle);
               broadcast(new BattleInvitation($battle_invite));
           }
        }
        return Helper::apiSuccessResponse(true,'success',['game_channel_id'=>$game_id,'host_id'=>auth()->user()->id,'max_players'=>$game_player_match->max_players]);
    }





    /**
     *  Battle / Respond Request
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": {
    "game_channel_id": "game_1617117472",
    "host_id": 2,
    "max_players": 4
    }
    }
     *
     *
     * @bodyParam  request_id optional
     * @bodyParam  status required (accepted,declined)
     *
     * @return JsonResponse
     */
    public function respondRequest(Request $request)
    {
        $this->validate($request,[
            'request_id' => 'required|exists:user_battles,id',
            'status' => 'required|in:accepted,declined'
        ]);
        $invitation = BattleInvite::find($request->request_id);
        if(!$invitation){
            return  Helper::apiErrorResponse(false,'invalid id',new \stdClass());
        }
        $invitation->status = $request->status;
        $invitation->save();
        broadcast(new BattleInvitationResponded($invitation));
        $data = [];
        $data['from_user_id'] = $invitation->user_id;
        $data['to_user_id'] = $invitation->invited_by;
        $data['model_type'] = 'battle/invite-response';
        $data['model_type_id'] = $invitation->id;
        $data['click_action'] = 'BattleInviteResponse';
        if($invitation->status == 'accepted') {
            $data['message']['en'] = $invitation->user->first_name . ' has accepted your challenge.';
            $data['message']['nl'] = $invitation->user->first_name . ' heeft je uitdaging aangenomen.';
        } else {
            $data['message']['en'] = $invitation->user->first_name . ' has declined to your challenge.';
            $data['message']['nl'] = $invitation->user->first_name . ' heeft uw uitdaging afgewezen.';
        }
        $data['message'] = json_encode($data['message']);
        $data['badge_count'] = $invitation->host->badge_count + 1;
        foreach ($invitation->host->user_devices as $device) {
            Helper::sendNotification($data, $device->device_token);
        }
        if($invitation->status=="accepted"){
            return Helper::apiSuccessResponse(true, 'Success',  ['game_channel_id'=>$invitation->battle->id,'host_id'=>$invitation->invited_by]);
        }
        return Helper::apiSuccessResponse(true, 'Success',['game_channel_id'=>null,'host_id'=>null]);
    }


    public function sendPushNotifications($user,$battle){
        $msg['en'] = 'You have an invitation for jogo battle by ' . Auth::user()->first_name;
        $msg['nl'] = 'Je hebt een uitnodiging voor jogo battle door ' . Auth::user()->first_name;
        Helper::processNotification(Auth::user()->id,$user->id,'battle/invite',$battle->id,'BattleInvite',$msg,$user->badge_count,$user->user_devices);
    }





    /**
     *  Battle / get Friends
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": [
    {
    "id": 13,
    "first_name": "Muhammad",
    "middle_name": "''",
    "last_name": "Huzaifa",
    "profile_picture": "media/users/5f20d450524291595987024.jpeg",
    "last_seen": null,
    "online_status": 0,
    "pivot": {
    "contact_user_id": 1,
    "user_id": 13,
    "status_id": 2
    }
    }
     * ]

     * }
     *
     * @return JsonResponse
     */
    public function getFriends(Request $request)
    {
        $user = User::with([
            'followers' => function ($q1) {
                $q1->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.profile_picture', 'last_seen', 'online_status');
            }])->find(Auth::user()->id);

        return Helper::apiSuccessResponse(true, 'Success', $user->followers ?? []);
    }
    //
}
