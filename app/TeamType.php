<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;

class TeamType extends Model
{
    use SoftDeletes;

    public $timestamps = true;
    public $locale;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->locale = App::getLocale();
    }

    public function getNameAttribute($value){
        return json_decode($value)->{$this->locale} ?? $value;
    }
}
