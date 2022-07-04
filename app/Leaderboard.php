<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    public $timestamps = true;
    protected $fillable = ['user_id','total_score','league_id','position'];


    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
