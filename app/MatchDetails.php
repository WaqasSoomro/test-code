<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MatchDetails extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'event_ts',
        'geo_lon',
        'geo_lat',
        'event_type',
        'event_magnitude',
        'speed',
        'hr',
        'period',
        'steps',
        'temperature'
    ];


    public function player(){
        return $this->belongsTo(User::class,'user_id');
    }
}
