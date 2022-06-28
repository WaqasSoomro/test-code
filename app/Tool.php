<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tool extends Model
{
    use SoftDeletes;
    
    public $timestamps = true;
    public $locale;
    private $defaultLocale = 'en';

    protected $appends = [
        'file_name'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->locale = App::getLocale();
    }

    public function getToolNameAttribute($value){
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function getFIleNameAttribute()
    {
        $fileName = json_decode($this->getAttributes()['tool_name']);
        $fileName = strtolower(str_replace("/", "_", $fileName->en));

        return $fileName;
    }


    /**
     * The exercises that belong to the tool.
     */
    public function exercises()
    {
        return $this->belongsToMany('App\Exercise', 'exercise_tools', 'tool_id', 'exercise_id');
    }
}
