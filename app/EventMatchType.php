<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;

class EventMatchType extends Model
{
    use SoftDeletes;

    protected $locale;

    private $defaultLocale = 'en';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->locale = App::getLocale();
    }

    public function getTitleAttribute($value)
    {
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }
}
