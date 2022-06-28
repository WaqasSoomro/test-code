<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{

 	protected $fillable = [
        'user_id','country',
    ];


    public $timestamps = false;

    /*
	* If you need to set all columns as fillable, do this in the model:
	**/

    /** protected $guarded = []; **/


    public function user()
    {
        return $this->belongsTo('App\User');
    }



}
