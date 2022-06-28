<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Translation extends Model
{

    public $timestamps = true;
    public $locale;
    private $defaultLocale = 'en';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->locale = App::getLocale();
    }

    public function getTranslationAttribute($value){
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }
}
