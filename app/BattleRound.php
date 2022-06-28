<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class BattleRound extends Model
{
    protected $table = 'battle_rounds';
    public $timestamps = true;

    /**
     * The users that belong to the achievement.
     */
    public function exercise()
    {
        return $this->belongsTo('App\Exercise', 'exercise_id');
    }

    public function battle()
    {
        return $this->belongsTo('App\Battle', 'battle_id');
    }

}
