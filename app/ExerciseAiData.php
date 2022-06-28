<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class ExerciseAiData extends Model
{
    protected $fillable = ['exercise_id','player_exercise_id','level_id','user_id','title','value'];
    protected $table = 'exercise_ai_data';
    public $timestamps = true;

    public $locale;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->locale = App::getLocale();
    }

    /**
     * Get the Exercise that owns the ExerciseTip.
     */
    public function exercise()
    {
        return $this->belongsTo('App\Exercise');
    }

}
