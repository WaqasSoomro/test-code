<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class UserBattle extends Model
{
    protected $table = 'user_battles';
    public $timestamps = true;

    /**
     * The users that belong to the achievement.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function battle()
    {
        return $this->belongsTo('App\Battle', 'battle_id');
    }

}
