<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    public $timestamps = true;
    public $incrementing = false;
    protected $fillable = ['id','init_ts', 'exercise_id', 'level_id', 'user_id'];

    public function matches_stats()
    {
        return $this->hasMany('App\MatchStat','match_id');
    }



    public function player(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}
