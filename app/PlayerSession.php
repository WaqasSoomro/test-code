<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlayerSession extends Model
{
    //
    protected $table = 'player_session'; 
    protected $fillable=['session_id','player_id','status'];
    

}
