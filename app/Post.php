<?php

namespace App;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Null_;

class Post extends Model
{
    use SoftDeletes;
    protected $fillable = ['author_id', 'player_exercise_id','exercise_id', 'level_id', 'post_title', 'post_desc','thumbnail', 'post_attachment', 'status_id'];
    public $timestamps = true;
    public $media = 'media/posts';

    /**
     * Get the user that owns the post.
     */
    public function author()
    {
        return $this->belongsTo('App\User', 'author_id');
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
        return $this->hasMany('App\Comment')->where('parent_id', Null)->with('replies');
    }


    /**
     * Post has many shares
     */

    public function shares()
    {
        return $this->hasMany('App\Comment');
    }

    public function getCheckPosts($ex,$player_id,$conversation_ids,$extraInfo){
        $check_post = $this::Select('id', 'level_id', 'exercise_id', 'post_title', 'created_at')
            ->where('exercise_id', $ex->id)->where('level_id', $ex->level_id)
            ->where('author_id', $player_id);

            if(!$extraInfo)
            {
                return $check_post->with("comments")->first();
            }

            $check_post->with(['comments.replies' => function ($q1) {
                $q1->orderBy('id', 'desc')->with('contact:id,first_name,last_name,profile_picture');
            }])
            ->with(['comments' => function ($q) use ($conversation_ids) {
                $q->whereIn('contact_id', $conversation_ids)->latest();
            }])
            ->with([
                'comments.contact' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'profile_picture');
                }
            ]);

        if (count(\Session::get('posts')) > 0) {
            $check_post->whereNotIn('id', \Session::get('posts'));
        }

        $check_post = $check_post->first();

        if ($check_post) {
            $posts = $check_post->id;

            \Session::put('posts.' . $ex->id . ' - ' . $check_post->id, $posts);
        }
        return $check_post;
    }

    public function store($request)
    {
        $this->author_id = $request->author_id;
        $this->post_title = $request->post_title;
        $this->post_desc = $request->post_desc;
        $this->post_type = isset($request->post_type)?$request->post_type:"post";
        if (Storage::exists($this->post_attachment)) {
            Storage::delete($this->post_attachment);
        }

        if (Storage::exists($this->thumbnail)) {
            Storage::delete($this->thumbnail);
        }
        if ($request->post_attachment != "") {
            $this->post_attachment = Helper::uploadBase64File($request->post_attachment, $this->media);
        }

        if ($request->post_thumbnail != "") {
            $this->thumbnail = Helper::uploadBase64File($request->post_thumbnail, $this->media);
        }

        $this->save();

        return $this;
    }

    public function store_duplicate($request)
    {
        $this->author_id = $request->author_id;
        $this->post_title = $request->post_title;
        $this->post_desc = $request->post_desc;
        $this->post_type = isset($request->post_type) ? $request->post_type : 'post';

        if (Storage::exists($this->post_attachment) && $request->hasFile('post_attachment'))
        {
            Storage::delete($this->post_attachment);
        }

        if (Storage::exists($this->thumbnail) && $request->post_thumbnail != "")
        {
            Storage::delete($this->thumbnail);
        }

        if ($request->hasFile('post_attachment'))
        {
            $this->post_attachment = Storage::putFile($this->media, $request->post_attachment);
        }

        if (isset($request->status_id))
        {
            $this->status_id = $request->status_id;
        }

        if ($request->hasFile('post_thumbnail'))
        {
            $this->thumbnail = Storage::putFile($this->media, $request->post_thumbnail);
        }

        $this->save();

        return $this;
    }

    public function createPost($pl_ex,$player_id){
        $exercise = Exercise::find($pl_ex->exercise_id);

        $status = Status::where('name', 'not-shared')->first();

        Post::create([
            'player_exercise_id' => $pl_ex->id,
            'author_id' => $player_id,
            'exercise_id' => $exercise->id,
            'level_id' => $pl_ex->level_id,
            'post_title' => $exercise->title,
            'status_id' => $status->id
        ]);
    }
}