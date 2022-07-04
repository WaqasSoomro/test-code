<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssignmentExercise extends Model
{
    protected $fillable = ['assignment_id','exercise_id', 'level_id', 'sort_order'];
    protected $table = 'assignment_exercises';
    public $timestamps = true;




}
