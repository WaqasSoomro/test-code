<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class ExerciseTip extends Model
{
    protected $fillable = ['exercise_id','description','media','media_type'];
    protected $table = 'exercise_tips';
    public $timestamps = true;
    public $media = 'media/exercise_tips';

    public $locale;

    private $defaultLocale = 'en';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->locale = App::getLocale();
    }

    public function getDescriptionAttribute($value){

        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    /**
     * Get the Exercise that owns the ExerciseTip.
     */
    public function exercise()
    {
        return $this->belongsTo('App\Exercise');
    }

}
