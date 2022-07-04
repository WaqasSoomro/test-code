<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BattleInvite extends Model
{

    protected $table='battle_invites';


    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function host(){
        return $this->belongsTo(User::class,'invited_by');
    }

    public function battle(){
        return $this->belongsTo(Battle::class,'battle_id');
    }


    //
}
