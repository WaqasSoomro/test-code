<?php
namespace App\Services\Websockets\Channels;
use App\Battle;
use App\Events\GameIsReady;
use App\GameMatchMaking;
use App\UserBattle;
use BeyondCode\LaravelWebSockets\WebSockets\Channels\PresenceChannel as BasePresenceChannel;
use Illuminate\Support\Facades\DB;
use stdClass;
use Ratchet\ConnectionInterface;

class PresenceChannel extends BasePresenceChannel
{
    protected $users = [];
    protected $sockets = [];
    public function subscribe(ConnectionInterface $connection, stdClass $payload)
    {
        parent::subscribe($connection, $payload);
        if (isset($this->subscribedConnections[$connection->socketId])) {
            $userId = $this->sockets[$connection->socketId];
            $extract_channel_name = explode(".",$this->channelName);
            $channel = $extract_channel_name[0];
            if($channel  === 'presence-battle_ai'){
                $game_id = $extract_channel_name[1];
                $game = GameMatchMaking::find($game_id);
                if($game){
                    $avb_players = $game->avb_players;
                    $get_player = DB::table('game_players_matched')->where('player_id',$userId)->where('game_match_id',$game_id)->first();
                    if(!$get_player){
                        DB::table('game_players_matched')->insert([
                            'game_match_id'=>$game_id,
                            'player_id'=>$userId
                        ]);
                        $avb_players = $avb_players+1;
                        $game->avb_players = $avb_players;
                    }
                    //check if all are ready
//                    $get_player = DB::table('game_players_matched')
//                        ->where('is_ready',1)
//                        ->where('game_match_id',$game_id)->get();
//
//                    if($get_player->count() >= $max_players){
//                        $battle =  $this->createMatch($game->game_type);
//                        $game->battle_id = $battle->id;
//                        broadcast(new GameIsReady($battle , $game_id));
//                    }
                    $game->save();
                }
            }
        }
    }

    public function unsubscribe(ConnectionInterface $connection)
    {
        if (isset($this->subscribedConnections[$connection->socketId])) {
            $userId = $this->sockets[$connection->socketId];
            $extract_channel_name = explode(".",$this->channelName);
            $channel = $extract_channel_name[0];
            if($channel  == 'presence-battle_ai'){
                $game_id = $extract_channel_name[1];
                $this->leftGameLobby($userId , $game_id);
            }
        }
        parent::unsubscribe($connection);

    }
    public function getUsers(): array
    {
        parent::getUsers();
        return $this->users;
    }

    public function leftGameLobby($user_id , $game_match_id){
        $game = GameMatchMaking::find($game_match_id);
        if($game){
            if($game->avb_players > 0){
                $game->decrement('avb_players',1);
            }
            DB::table('game_players_matched')->where('game_match_id',$game_match_id)->where('player_id',$user_id)->delete();
        }
    }

    public function createMatch($battle_type){
        $rounds = 1;
        if ($battle_type == 'best_of_three') {
            $rounds = 3;
        } elseif ($battle_type == 'best_of_five') {
            $rounds = 5;
        } elseif ($battle_type == 'best_of_seven') {
            $rounds = 7;
        }
        $battle = new Battle();
        $battle->type = $battle_type;
        $battle->date = date('Y-m-d');
        $battle->time = date('H:i');
        $battle->rounds = $rounds;
        $battle->title = '';
        $battle->save();
        return $battle;
    }
}
