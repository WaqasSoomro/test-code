<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class BattleResult extends Model
{
    protected $table = 'battle_results';
    public $timestamps = true;

    /**
     * The users that belong to the achievement.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id','id');
    }

    public function round()
    {
        return $this->belongsTo('App\BattleRound', 'round_id');
    }

    public function battle()
    {
        return $this->belongsTo('App\Battle', 'battle_id');
    }

}
