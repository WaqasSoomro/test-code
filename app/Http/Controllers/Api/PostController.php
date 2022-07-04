<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\AddEditRequest;
use App\Http\Requests\Post\DeleteRequest;
use App\Http\Requests\Post\ShowRequest;
use App\Like;
use App\PlayerExercise;
use App\Post;
use App\User;
use App\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * @authenticated
 * @group User Posts
 *
 * APIs for posts
 */
class PostController extends Controller
{
    private function postQuery()
    {
        $post = Post::with([
            'author' => function ($query)
            {
                $query->select('id', 'first_name', 'last_name', 'profile_picture');
            },
            'comments' => function ($query)
            {
                $query->select('id', 'comment', 'created_at', 'contact_id', 'post_id');
            },
            'comments.contact' => function ($query)
            {
                $query->select('id', 'first_name', 'last_name', 'profile_picture');
            },
            'likes' => function ($query)
            {
                $query->select('id', 'contact_id', 'post_id');
            },
            'likes.contact' => function ($query)
            {
                $query->select('id', 'first_name', 'last_name', 'profile_picture');
            }
        ]);

        return $post;
    }

    /**
     * fetch all
     *
     * @response {
     *   "Response": true,
     *   "StatusCode": 200,
     *   "Message": "Records found successfully!",
     *   "Result": [
     *   {
     *   "id": 15,
     *   "author_id": 2,
     *   "post_title": "bnn post",
     *   "post_desc": "ust for bnn test",
     *   "post_attachment": "media/posts/YJZcrQn04aR27BqqgKb5cStq0KnYioXsULBheyXY.jpeg",
     *   "post_share_id": null,
     *   "created_at": "2020-06-19 15:59:41",
     *   "updated_at": "2020-06-23 15:46:25",
     *   "comments": [],
     *   "likes": []
     *   }
     * ]
     * }
     *
     * @response 404 {
     *   "Response": false,
     *   "StatusCode": 404,
     *   "Message": "Records not found",
     *   "Result": []
     * }
     *
     */
    public function index()
    {
        $posts = $this->postQuery();

        $posts = $posts->where('author_id', Auth::user()->id)
            ->where('post_type', 'post')
            ->whereIn('created_at', function($q){
                $q->select(DB::raw('MAX(p.created_at)'))->from('posts as p')
                    ->groupBy('exercise_id','level_id')
                    ->where('p.author_id', Auth::user()->id);
            })
            ->latest()
            ->get();

        if (count($posts) > 0) {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $posts);
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', []);
    }

    /**
     * fetch by id
     *
     * @response {
     *   "Response": true,
     *   "StatusCode": 200,
     *   "Message": "Record found successfully!",
     *   "Result":
     *   {
     *      "id": 15,
     *      "author_id": 2,
     *      "post_title": "bnn post",
     *      "post_desc": "ust for bnn test",
     *      "post_attachment": "media/posts/YJZcrQn04aR27BqqgKb5cStq0KnYioXsULBheyXY.jpeg",
     *      "post_share_id": null,
     *      "created_at": "2020-06-19 15:59:41",
     *      "updated_at": "2020-06-23 15:46:25",
     *      "comments": [],
     *      "likes": []
     *   }
     *
     * }
     *
     * @response 404{
     *   "Response": false,
     *   "StatusCode": 404,
     *   "Message": "Record not found",
     *   "Result": {}
     *  }
     *
     */
    public function show(ShowRequest $request)
    {
        $post = Post::with(['comments.contact', 'likes.contact'])
            ->where('author_id', Auth::user()->id)
            ->where('id', $request->post_id)
            ->first();

        if ($post) {
            return Helper::apiSuccessResponse(true, 'Record found successfully!', $post);
        }

        return Helper::apiNotFoundResponse(false, 'Record not found', new stdClass());
    }

    /**
     * add-edit
     *
     * @response {
     *       "Response": true,
     *       "StatusCode": 200,
     *       "Message": "Record has been saved",
     *       "Result": {
     *       "id": 15,
     *       "author_id": 2,
     *       "post_title": "bnn post",
     *       "post_desc": "ust for bnn test",
     *       "post_attachment": "media/posts/YJZcrQn04aR27BqqgKb5cStq0KnYioXsULBheyXY.jpeg",
     *       "post_share_id": null,
     *       "created_at": "2020-06-19 15:59:41",
     *       "updated_at": "2020-06-23 15:46:25"
     *       }
     *   }
     *
     */
    public function addEdit(AddEditRequest $request)
    {
        $post = Post::where('author_id', Auth::user()->id)->where('id', $request->post_id)->first();

        if (!$post) {
            $post = new Post();
        }

        $request->request->add(['author_id' => Auth::user()->id]);
        $response = $post->store($request);

        return Helper::apiSuccessResponse(true, 'Record has been saved', $response);
    }

    /**
     * delete
     *
     * @response {
     *   "Response": true,
     *   "StatusCode": 200,
     *   "Message": "Record has been deleted",
     *   "Result": {}
     * }
     *
     * @response 404 {
     *   "Response": false,
     *   "StatusCode": 404,
     *   "Message": "Record not found",
     *   "Result": {}
     *  }
     */
    public function delete(DeleteRequest $request)
    {
        $post = Post::where('author_id', Auth::user()->id)->where('id', $request->post_id)->first();

        if ($post) {
            $post->delete();
            PlayerExercise::where('id', $post->player_exercise_id)->delete();
            UserNotification::where('model_type_id', $post->id)->delete();
            return Helper::apiSuccessResponse(true, 'Record has been deleted', new stdClass());
        }

        return Helper::apiNotFoundResponse(false, 'Record not found', new stdClass());
    }






    /**
     * SaveVideo
     * @bodyParam post_attachment string required
     * @bodyParam post_thumbnail string required
     * @bodyParam post_title string 
     * @response {
     *       "Response": true,
     *       "StatusCode": 200,
     *       "Message": "Record has been saved",
     *       "Result": {
     *       "id": 15,
     *       "author_id": 2,
     *       "post_title": "bnn post",
     *       "post_desc": "ust for bnn test",
     *       "post_attachment": "media/posts/YJZcrQn04aR27BqqgKb5cStq0KnYioXsULBheyXY.jpeg",
     *       "post_thumbnail": "media/posts/YJZcrQn04aR27BqqgKb5cStq0KnYioXsULBheyXY.jpeg",
     *       "post_share_id": null,
     *       "created_at": "2020-06-19 15:59:41",
     *       "updated_at": "2020-06-23 15:46:25"
     *       }
     *   }
     *
     */

    public function saveVideo(Request $request){
        $this->validate($request,[
//            'post_attachment'=>'required|mimes:mp4,mov,ogg,qt,avi|max:40000',
            'post_attachment'=>'required|mimes:mp4,mov,ogg,qt,avi',
//            'post_thumbnail'=>'required|mimes:jpg,jpeg,png|max:2000',
            'post_thumbnail'=>'required|mimes:jpg,jpeg,png',
            'post_title'=>'nullable|string'
        ]);

        
        $post = Post::where('author_id', Auth::user()->id)->where('id', $request->post_id)->where('post_type','social_club')->first();
        if (!$post) {
            $post = new Post();
        }
        $request->request->add(['author_id' => Auth::user()->id]);
        $request->request->add(['post_type' => 'social_club']);
        $request->request->add(['status_id' => 7]);
        $response = $post->store_duplicate($request);
        
        $ex = $this->postQuery();

        $ex = $ex->withCount(['likes as user_likes_count' => function ($q) {
                $q->where('contact_id', Auth::user()->id);
            }])->find($response->id);

        return Helper::apiSuccessResponse(true, 'Record has been saved', Helper::getPostObject($ex));
    }


    public function getVideos(){

    }




    /**
     * Get post juggles
     * @bodyParam post_id string required
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Records found successfully!",
    "Result": [
    {
    "id": 2,
    "first_name": "Fatima",
    "middle_name": null,
    "last_name": "Sultana",
    "profile_picture": "media/users/5f8d8640225f41603110464.jpeg",
    "juggles_count": 11
    },
    {
    "id": 3,
    "first_name": "Hasnain",
    "middle_name": null,
    "last_name": "Ali",
    "profile_picture": "media/users/5f7b29180ec451601906968.jpeg",
    "juggles_count": 1
    },
    {
    "id": 4,
    "first_name": "Tariq",
    "middle_name": null,
    "last_name": "Sidd",
    "profile_picture": "media/users/5f7b294249cca1601907010.jpeg",
    "juggles_count": 1
    }
    ]
    }
     *
     * @response 404 {
     *   "Response": false,
     *   "StatusCode": 404,
     *   "Message": "Records not found",
     *   "Result": []
     * }
     *
     */
    public function getJuggles(Request $request){
        $this->validate($request,[
            'post_id'=>'required'
        ]);

        $juggles = Like::selectRaw('users.id, users.first_name, users.middle_name, users.last_name , users.profile_picture, COUNT(likes.id) as juggles_count')
            ->join('users', 'users.id', '=', 'likes.contact_id')
            ->where('likes.post_id',$request->post_id)
            ->groupBy(\DB::raw('likes.post_id, users.id'))->get();
        if (count($juggles) > 0) {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $juggles);
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', []);

    }
}
