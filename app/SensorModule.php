<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SensorModule extends Model
{
    //
    protected $table = 'sensor_module'; 
    protected $fillable=['version_number','file'];
    public static $media = 'sensor_module/';
}
