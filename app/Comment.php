<?php

namespace App;

use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $appends = ['posted_at'];
    public function getPostedAtAttribute()
    {
        if($this->created_at){
            return Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->diffForHumans(null, true) . ' ago';
        }

        return null;
    }

    /**
     * Get the post that owns the comment.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function parent()
    {
        return $this->belongsTo(Self::class, 'parent_id', 'id');
    }

    public function replies()
    {
        return $this->hasMany(Self::class, 'parent_id', 'id');
    }

    public function contact()
    {
        return $this->belongsTo(User::class, 'contact_id');
    }

    public function store($request)
    {
        $this->post_id = $request->post_id;
        $this->contact_id = $request->contact_id;
        $this->comment = $request->comment;
        $this->parent_id = $request->parent_id ?? NULL;
        $this->save();

        $post = Post::find($request->post_id);

        if($request->new_comment){
            $data['from_user_id'] = $request->contact_id;
            $data['to_user_id'] = $post->author_id;
            $data['model_type'] = 'posts/comment';
            $data['model_type_id'] = $post->id;
            $data['click_action'] = 'VideoAndComments';
            if(isset($request->parent_id))
            {
                $data['message']['en'] = auth()->user()->first_name.' '.auth()->user()->last_name.' has replied to your comment ';
                $data['message']['nl'] = auth()->user()->first_name.' '.auth()->user()->last_name.' heeft gereageerd op uw opmerking ';   
            }
            else
            {
                $data['message']['en'] = auth()->user()->first_name.' '.auth()->user()->last_name.' has commented on your post ';
                $data['message']['nl'] = auth()->user()->first_name.' '.auth()->user()->last_name.' heeft gereageerd op je bericht ';
            }
            $data['message'] = json_encode($data['message']);
            $data['badge_count'] = $request->badge_count + 1;

            $devices = $post->author->user_devices;
            $tokens = [];
            $device_type = [];


            if($request->contact_id != $post->author_id){
                $data['badge_count'] = $post->author->badge_count + 1;
                foreach ($devices as $device) {
                    Helper::sendNotification($data, $device->onesignal_token,$device->device_type);
                }
                User::where('id', $post->author_id)->update([
                    'badge_count' => $data['badge_count']
                ]);
            }
        }

        return $this;
    }

    public function remove($comment_id,$contact_id){
        $comment = Comment::where('id', $comment_id)->where('contact_id', $contact_id)->first();

        if ($comment) {
            $comment->delete();
            return ['status' => true,'msg' => 'Record has been deleted'];
        }

        return ['status' => false,'msg' => 'Record not found'];
    }

    public function addEdit($request,$user){
        $comment = $this::where('id', $request->comment_id)
            ->where('contact_id', $user->id)
            ->first();

        $new_comment = false;
        if (!$comment) {
            $new_comment = true;
        }

        $data = new \stdClass();
        $data->post_id = $request->post_id;
        $data->contact_id = $user->id;
        $data->comment = $request->comment;
        $data->new_comment = $new_comment;
        $data->parent_id = $request->parent_id ?? NULL;
        $data->first_name = $user->first_name;
        $data->last_name = $user->last_name;
        $data->badge_count = $user->badge_count + 1;

        $comment = $this->store($data);

        return ['comment' => $comment, 'new_comment' => $new_comment];
    }
}