<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    protected $fillable = ['user_id','session_time','match_id'];
}
