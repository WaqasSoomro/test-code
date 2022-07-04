<?php

namespace App;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Story extends Model
{
    use SoftDeletes;
    protected $fillable = ['author_id', 'thumbnail','media','media_type','status_id'];
    public $timestamps = true;
    public static $media = 'media/stories';
    public  static $media_thumbnails = 'media/stories/thumbnails';


    /**
     * Get the user that owns the post.
     */
    public function author()
    {
        return $this->belongsTo('App\User', 'author_id');
    }



    public function views(){
        return $this->belongsToMany(User::class,'story_views');
    }

    /**
     * Post has many likes
     */

    public function likes()
    {
        return $this->hasMany('App\Like');
    }


    /**
     * Post has many comments
     */

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }


    /**
     * Post has many shares
     */

    public function shares()
    {
        return $this->hasMany('App\Comment');
    }

    public function store($request)
    {
        $this->author_id = $request->author_id;
        //$this->thumbnail = $request->thumbnail;
        $this->media_type = $request->media_type;

        if (Storage::exists($this->media)) {
            Storage::delete($this->media);
        }
        if ($request->media != "") {
            $this->media = Helper::uploadBase64File($request->media, $this->media);
        }

        if (Storage::exists($this->media_thumbnails)) {
            Storage::delete($this->media_thumbnails);
        }
        if ($request->thumbnail != "") {
            $this->thumbnail = Helper::uploadBase64File($request->thumbnail, $this->media_thumbnails);
        }


        $this->save();

        return $this;
    }

    public function store_duplicate($request)
    {
        $this->author_id = $request->author_id;
        $this->post_title = $request->post_title;
        $this->post_desc = $request->post_desc;
        $this->post_attachment = $request->post_attachment;


        if (Storage::exists($this->post_attachment) && $request->hasFile('post_attachment')) {
            Storage::delete($this->post_attachment);
        }

        $path = "";
        if ($request->hasFile('post_attachment')) {
            $path = Storage::putFile($this->media, $request->post_attachment);
        }

        $this->post_attachment = $path;

        $this->save();

        return $this;
    }

}
