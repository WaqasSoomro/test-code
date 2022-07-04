<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;

class EventRepetition extends Model
{
    use SoftDeletes;

    protected $locale;

    protected $appends = [
        "engTitle"
    ];

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

    public function getEngTitleAttribute()
    {
        $title = json_decode($this->getAttributes()['title']);
        $title = strtolower($title->en);

        return $title;
    }
}