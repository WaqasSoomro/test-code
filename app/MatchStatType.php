<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class MatchStatType extends Model
{
    protected $table = 'matches_stats_types';

    protected $fillable = [
        'name',
        'value_min',
        'value_max',
        'description','image'
    ];
    
    public $timestamps = true;

    protected $appends = [
        'max_stat_value',
        'disabled'
    ];

    public $locale;

    private $defaultLocale = 'en';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->locale = App::getLocale();
    }

    public function getDisplayNameAttribute($value)
    {
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function getDescriptionAttribute($value)
    {
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function getMaxStatValueAttribute()
    {
        if(auth()->user())
        {
            if ($val == $this->matches_stats()->where('player_id', auth()->user()->id)->max('stat_value'))
            {
                return $val;
            }
            else
            {
                return $this->matches_stats()->max('stat_value');
            }
        }
        else
        {
            return $this->matches_stats()->max('stat_value');
        }
    }

    public function getDisabledAttribute()
    {
        if (auth()->user())
        {
            if ($this->matches_stats()->where('player_id', auth()->user()->id)->first())
            {
                return 0;
            }
            else
            {
                return 1;
            }
        }
        else
        {
            return 1;
        }
    }

    public function matches_stats()
    {
        return $this->hasMany('App\MatchStat','stat_type_id');
    }
}