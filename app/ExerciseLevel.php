<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExerciseLevel extends Model
{
    protected $table = 'exercise_levels';
    public $timestamps = true;


    /**
    * Get the exercise that owns the exercise level.
    */
    public function exercise()
    {
        return $this->belongsTo('App\Exercise', 'exercise_id');
    }

    /**
     * The users (players) that belong to the ExerciseLevel.
     */
    public function users()
    {
        return $this->belongsToMany('App\User','player_scores','level_id','user_id');
    }


}
