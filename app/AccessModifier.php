<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class AccessModifier extends Model
{
    protected $table = 'access_modifiers';
    public $timestamps = true;
    public $media ='media/achievements';

    public $locale;
    public $defaultLocale;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->locale = App::getLocale();
        $this->defaultLocale = 'en';
    }

    public function getDisplayNameAttribute($value){

        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function getDescriptionAttribute($value){
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    /**
     * The users that belong to the achievement.
     */
    public function users()
    {
        return $this->belongsToMany('App\User','user_privacy_settings','access_modifier_id','user_id');
    }

}
