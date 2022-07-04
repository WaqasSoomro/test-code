<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{

	protected $fillable = [
		'user_id','contact_user_id','created_at','updated_at',
    ];


    public $timestamps = true;


    public static $rules = [
        'user_id' => 'required|exists:users,id,deleted_at,NULL',
        'contact_user_id' => 'required|exists:users,id,deleted_at,NULL',

    ];



    /**
     * Get the user that owns the contact.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }



    public function store($request)
    {
        $this->user_id = $request->user_id;
        $this->contact_user_id = $request->contact_user_id;
        $this->status_id = $request->status_id;
        $this->save();
        return $this;
    }

}
