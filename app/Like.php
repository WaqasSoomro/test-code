<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    public $timestamps = true;

    /**
     * Get the post that owns the like.
     */
    public function post()
    {
        return $this->belongsTo('App\Post');
    }

    public function contact()
    {
        return $this->belongsTo(User::class, 'contact_id');
    }

    public function store($request)
    {
        $this->post_id = $request->post_id;
        $this->contact_id = $request->contact_id;
        $this->save();

        return $this;
    }

}
