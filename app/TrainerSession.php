<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainerSession extends Model
{
    //
    protected $table = 'trainer_session'; 
    protected $fillable=['trainer_id','exercise_id','level_id','status'];

}
