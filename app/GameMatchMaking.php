<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameMatchMaking extends Model
{
    //

    protected $table = 'game_players_matching';
    public $incrementing =false;
    public function players(){
        return $this->belongsToMany(User::class,'game_players_matched','game_match_id','player_id');
    }


    public function battle(){
        return $this->belongsTo(Battle::class,'battle_id');
    }



    public function battle_invites(){
//        return $this->belongsTo(BattleInvite::class,'batt','game_match_id','player_id');
    }



}
