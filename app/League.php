<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class League extends Model
{
    public $timestamps = true;
    public $media ='media/leagues';

    public $locale;

    private $defaultLocale = 'en';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->locale = App::getLocale();
    }

    public function getLeagueNameAttribute($value){

        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function getDescriptionAttribute($value){
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    /**
    * The users that belong to the league.
    */
    public function users()
    {
        return $this->belongsToMany('App\User','leaderboards','league_id','user_id')->withPivot('total_score','position');
    }

}
