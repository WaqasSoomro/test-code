<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use SoftDeletes;
    
    public $timestamps = true;

    public $media ='media/skills';
    
    public $locale;
    public $defaultLocale;

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
    
    public function player_scores_users()
    {
        return $this->belongsToMany('App\User','player_scores','skill_id','user_id')
        ->withPivot('exercise_id', 'level_id', 'score')
        ->withTimestamps();
    }

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'exercise_skills', 'skill_id', 'exercise_id');
    }
}