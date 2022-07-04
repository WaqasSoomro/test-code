<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Like;
use App\Post;
use App\User;
use App\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use stdClass;

/**
 * @group Like
 *
 * APIs to manage likes
 */
class LikeController extends Controller
{
    /**
     * like/dislike a post
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Record has been saved",
     * "Result": {
     * "id": 5,
     * "post_id": "2",
     * "contact_id": 2,
     * "status": 1,
     * "created_at": "2020-06-19 16:34:55",
     * "updated_at": "2020-06-23 17:26:20"
     * }
     * }
     *
     * @bodyParam post_id required
     * @bodyParam count required
     *
     */
    public function addEdit(Request $request)
    {
        Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
            'count' => 'required|numeric|gt:0'
        ])->validate();

        $likes = Like::where('post_id', $request->post_id)->where('contact_id', Auth::user()->id)->get();

        if((count($likes) + $request->count) - 1 > 10){
            return Helper::apiNotFoundResponse(false, 'You cannot like this post more than 11', new stdClass());
        }

        $post = Post::find($request->post_id);
        if(empty($post))
        {
            return Helper::apiNotFoundResponse(false, 'Requested Post is not associated to Current User', new stdClass());
        }
        $data['from_user_id'] = Auth::user()->id;
        $data['to_user_id'] = $post->author_id;
        $data['model_type'] = 'posts/like';
        $data['model_type_id'] = $post->id;
        $data['click_action'] = 'VideoAndComments';
        $data['message']['en'] = Auth::user()->first_name . ' ' . Auth::user()->last_name . ' likes your post';
        $data['message']['nl'] = Auth::user()->first_name . ' ' . Auth::user()->last_name . ' vindt jouw bericht leuk';
        $data['message'] = json_encode($data['message']);


        $request->request->add(['contact_id' => Auth::user()->id]);
        $response = '';
        for($i = 0; $i < $request->count; $i++){
            $like = new Like();
            $response = $like->store($request);
        }

        $response->contact;

        $devices = $post->author->user_devices;

        if (Auth::user()->id != $post->author_id) {
            $data['badge_count'] = $post->author->badge_count + 1;
            foreach ($devices as $device) {
                Helper::sendNotification($data, $device->onesignal_token,$device->device_type);
            }
            User::where('id', $post->author_id)->update([
                'badge_count' => $data['badge_count']
            ]);
        }

        return Helper::apiSuccessResponse(true, 'Record has been saved', $response);
    }
}
