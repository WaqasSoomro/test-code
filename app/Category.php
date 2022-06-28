<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Category extends Model
{
    protected $table = 'categories';

    public $timestamps = true;
    
    public $locale;

    private $defaultLocale;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->locale = App::getLocale();
        $this->defaultLocale = 'en';
    }

    public function getNameAttribute($value)
    {
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function getDescriptionAttribute($value)
    {
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    /**
        The exercises that belong to the category.
    */
    
    public function exercises()
    {
        return $this->belongsToMany('App\Exercise','category_exercise','category_id','exercise_id');
    }

    /**
        The Category Has Many Exercise Types Through Exercise Categories
    */

    public function types()
    {
        return $this->hasManyThrough("App\ExerciseType","App\ExerciseCategory","category_id","exercise_id","id","id");
    }
}