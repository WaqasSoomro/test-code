<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    //

    public function exercises(){
        return $this->belongsToMany("App\Exercise","exercise_types","type_id","exercise_id");
    }
}
