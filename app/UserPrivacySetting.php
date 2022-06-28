<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPrivacySetting extends Model
{
    protected $fillable = ['user_id', 'access_modifier_id'];
    protected $table = 'user_privacy_settings';
    public $timestamps = true;

    /**
     * Get the post that owns the comment.
     */

    /*public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }*/


    public static $update_user_privacy_seting_rules = [
        'user_id' => 'required|exists:users,id',
        'user_privacy_setting_id' => 'required|exists:user_privacy_settings,id',
        'access_modifier_id' => 'required|exists:access_modifiers,id'
    ];


    public function store($request)
    {
        $this->user_id = $request->user_id;
        $this->access_modifier_id = $request->access_modifier_id;
        $this->save();
        return $this;
    }

    public function store_update($request)
    {
        $this->user_id = $request->user_id;
        $this->access_modifier_id = $request->access_modifier_id;
        $this->save();
        return $this;
    }

}
