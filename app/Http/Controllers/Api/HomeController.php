<?php

namespace App\Http\Controllers\Api;

use App\AccessModifier;
use App\Contact;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Post;
use App\Status;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use stdClass;


/**
 * @group Home
 *
 * APIs for home feeds
 */
class HomeController extends Controller
{
    /**
     * feeds
     *
     * fetch home feeds
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "data": [
     * {
     * "id": 109,
     * "author_id": 1,
     * "exercise_id": 207,
     * "level_id": 1,
     * "post_title": " Low power shot (L)",
     * "post_desc": "test1",
     * "thumbnail": "media/player_exercises/NmDe8rtDeK1aC7je3eIafMTb3HP16Zkvzlss2zds.jpeg",
     * "post_attachment": "media/player_exercises/8YTaMMIJT5qQpyApihskfQ8D3K4eOJhpl7FF01Bx.mp4",
     * "status_id": 7,
     * "created_at": "2020-11-03T16:48:17.000000Z",
     * "updated_at": "2020-11-03T16:48:40.000000Z",
     * "deleted_at": null,
     * "author": {
     * "id": 1,
     * "first_name": "muhammad.",
     * "last_name": "shahzaib",
     * "profile_picture": "media/users/5f996dc5898911603890629.jpeg"
     * },
     * "comments": 0,
     * "likes": 0,
     * "user_likes_count": 0,
     * "i_liked": false,
     * "user_privacy_settings": 1
     * },
     * {
     * "id": 83,
     * "author_id": 11,
     * "exercise_id": 89,
     * "level_id": 1,
     * "post_title": "Out & in (L/R)",
     * "post_desc": "look at me now",
     * "thumbnail": "media/player_exercises/k1Se5Mna0pHeCcyxzBfYelIkm3mjVnzf8xjvfjYf.jpeg",
     * "post_attachment": "media/player_exercises/ZqMaSJE3MkysQ2qEQjspkhOJIalnr7VZVZmvIt2O.mp4",
     * "status_id": 7,
     * "created_at": "2020-10-31T16:07:59.000000Z",
     * "updated_at": "2020-10-31T16:08:19.000000Z",
     * "deleted_at": null,
     * "author": {
     * "id": 11,
     * "first_name": "Saad",
     * "last_name": "Saleem",
     * "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg"
     * },
     * "comments": 0,
     * "likes": 0,
     * "user_likes_count": 0,
     * "i_liked": false
     * },
     * {
     * "id": 50,
     * "author_id": 2,
     * "exercise_id": 213,
     * "level_id": 1,
     * "post_title": "Singe leg deadlifts",
     * "post_desc": "testing iOS thumbnail",
     * "thumbnail": "media/player_exercises/5DyqIWsGYKdR5NxdHL49nxqsD5mlWKLmXaDptBRm.jpeg",
     * "post_attachment": "media/player_exercises/HZV9SpjuKa8IAVGoQHi3TAI22WSgZhs4KXiyCU0X.mp4",
     * "status_id": 7,
     * "created_at": "2020-10-29T12:41:17.000000Z",
     * "updated_at": "2020-10-29T12:41:27.000000Z",
     * "deleted_at": null,
     * "author": {
     * "id": 2,
     * "first_name": "Fatima",
     * "last_name": "Sultana",
     * "profile_picture": "media/users/5f8d8640225f41603110464.jpeg"
     * },
     * "comments": 0,
     * "likes": 14,
     * "user_likes_count": 0,
     * "i_liked": false
     * },
     * {
     * "id": 22,
     * "author_id": 11,
     * "exercise_id": 3,
     * "level_id": 1,
     * "post_title": "10 Cones dribble (L)",
     * "post_desc": "ok",
     * "thumbnail": "media/player_exercises/JKgXd3ZX1MXSbaFwOnURPO1SQARJJC29T4u14GNO.jpeg",
     * "post_attachment": "media/player_exercises/QhLYrCC75iOlulIYnL31abjkUNz1nRgH4CmTRBhZ.mp4",
     * "status_id": 7,
     * "created_at": "2020-10-28T20:36:41.000000Z",
     * "updated_at": "2020-10-28T20:36:47.000000Z",
     * "deleted_at": null,
     * "author": {
     * "id": 11,
     * "first_name": "Saad",
     * "last_name": "Saleem",
     * "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg"
     * },
     * "comments": 0,
     * "likes": 29,
     * "user_likes_count": 0,
     * "i_liked": false
     * },
     * {
     * "id": 16,
     * "author_id": 2,
     * "exercise_id": 68,
     * "level_id": 1,
     * "post_title": "Laces push-pull (L)",
     * "post_desc": "release apk test",
     * "thumbnail": "media/player_exercises/HAsFokAR8ZHGKxgE4am9CsBq7YgOb4zJdNkTxuIp.jpeg",
     * "post_attachment": "media/player_exercises/NFNoDWGKBrfDP9CZkfrDufvAqvBRP5t6ZXLcOfGc.mp4",
     * "status_id": 7,
     * "created_at": "2020-10-28T19:28:34.000000Z",
     * "updated_at": "2020-10-28T19:28:49.000000Z",
     * "deleted_at": null,
     * "author": {
     * "id": 2,
     * "first_name": "Fatima",
     * "last_name": "Sultana",
     * "profile_picture": "media/users/5f8d8640225f41603110464.jpeg"
     * },
     * "comments": 2,
     * "likes": 22,
     * "user_likes_count": 0,
     * "i_liked": false
     * }
     * ],
     * "meta": {
     * "current_page": 1,
     * "first_page_url": "http://localhost/jogo/api/v1/app/home/feeds?page=1",
     * "from": 1,
     * "last_page": 2,
     * "last_page_url": "http://localhost/jogo/api/v1/app/home/feeds?page=2",
     * "next_page_url": "http://localhost/jogo/api/v1/app/home/feeds?page=2",
     * "per_page": 5,
     * "prev_page_url": null,
     * "total": 8
     * }
     * }
     * }
     */

    private function postsQueryFeeds()
    {
        $status = Helper::getStatus('shared');
        $posts = Helper::postQuery();
        $posts = $posts->where('status_id', $status->id);
        return $posts;
    }

    public function feeds()
    {
        $followings_ids = Contact::where('user_id', Auth::user()->id)->pluck('contact_user_id')->toArray();
        $access_modifier = AccessModifier::whereIn('name', ['follower', 'public'])->pluck('id')->toArray();

        $posts = $this->postsQueryFeeds()->whereIn('author_id', $followings_ids)
        ->whereHas('author', function ($q) use ($access_modifier) {
            $q->whereHas('user_privacy_settings', function ($q2) use ($access_modifier) {
                $q2->whereIn('access_modifiers.id', $access_modifier);
            });
        })
        ->where('author_id', '!=', auth()->id())
        ->latest()
        ->get();


        $my_posts = $this->postsQueryFeeds()->latest()
        ->where('author_id', Auth::user()->id)
        ->get();


        if (count($posts) > 0 || count($my_posts) > 0) {
            $postss = $posts->merge($my_posts);

            $posts = $postss->map(function ($ex) {
                return Helper::getPostObject($ex);
            });

            $new_posts = $posts->sortByDesc(function ($element) {
                return $element->created_at;
            });
//            $new_posts = array();
//            foreach ($sorted as $value) {
//                $new_posts[] = $value;
//            }
            $new_posts = collect($new_posts);
            $new_posts = Helper::paginate($new_posts, 5);
            $meta = $new_posts->toArray();

            $response = [
                'data' => $new_posts->values()->all(),
                'meta' => [
                    'current_page' => $meta['current_page'],
                    'first_page_url' => $meta['first_page_url'],
                    'from' => $meta['from'],
                    'last_page' => $meta['last_page'],
                    'last_page_url' => $meta['last_page_url'],
                    'next_page_url' => $meta['next_page_url'],
                    'per_page' => $meta['per_page'],
                    'prev_page_url' => $meta['prev_page_url'],
                    'total' => $meta['total']
                ]
            ];

            return Helper::apiSuccessResponse(true, __('messages.feeds.found'), $response);
        }

        return Helper::apiNotFoundResponse(false, __('messages.feeds.not_found'), []);
    }

    /**
     * Single Feed
     *
     * Getting single feed brief details with comments and likes
     *
     * @queryParam  post_id required player id is required
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Records found successfully!",
     *     "Result": {
     *         "id": 3,
     *         "author_id": 1,
     *         "exercise_id": 2,
     *         "level_id": 2,
     *         "post_title": "my first try",
     *         "post_desc": "my first try",
     *         "thumbnail": null,
     *         "post_attachment": null,
     *         "status_id": 7,
     *         "created_at": "2020-07-20T19:33:30.000000Z",
     *         "updated_at": "2020-07-20T19:33:30.000000Z",
     *         "deleted_at": null,
     *         "author": {
     *             "id": 1,
     *             "first_name": "muhammad",
     *             "last_name": "shahzaib",
     *             "profile_picture": "media/users/5f216ac10a9fc1596025537.jpeg"
     *         },
     *         "comments": [
     *             {
     *                 "id": 50,
     *                 "comment": "looks cool",
     *                 "created_at": "2020-07-24T22:56:11.000000Z",
     *                 "contact_id": 18,
     *                 "post_id": 3,
     *                 "posted_at": "3 weeks ago",
     *                 "contact": {
     *                     "id": 18,
     *                     "first_name": "Saad Alternate",
     *                     "last_name": "Saleem",
     *                     "profile_picture": null
     *                 },
     *                 "user_role": "player"
     *             },
     *             {
     *                 "id": 70,
     *                 "comment": "ok",
     *                 "created_at": "2020-07-28T17:59:28.000000Z",
     *                 "contact_id": 20,
     *                 "post_id": 3,
     *                 "posted_at": "3 weeks ago",
     *                 "contact": {
     *                     "id": 20,
     *                     "first_name": "falak",
     *                     "last_name": "Saad",
     *                     "profile_picture": "media/users/5f20223ea98da1595941438.jpeg"
     *                 },
     *                 "user_role": "player"
     *             },
     *             {
     *                 "id": 71,
     *                 "comment": "ok",
     *                 "created_at": "2020-07-28T17:59:28.000000Z",
     *                 "contact_id": 20,
     *                 "post_id": 3,
     *                 "posted_at": "3 weeks ago",
     *                 "contact": {
     *                     "id": 20,
     *                     "first_name": "falak",
     *                     "last_name": "Saad",
     *                     "profile_picture": "media/users/5f20223ea98da1595941438.jpeg"
     *                 },
     *                 "user_role": "player"
     *             }
     *         ],
     *         "likes": [
     *             {
     *                 "id": 1,
     *                 "contact_id": 1,
     *                 "post_id": 3,
     *                 "contact": {
     *                     "id": 1,
     *                     "first_name": "muhammad",
     *                     "last_name": "shahzaib",
     *                     "profile_picture": "media/users/5f216ac10a9fc1596025537.jpeg"
     *                 }
     *             },
     *             {
     *                 "id": 6,
     *                 "contact_id": 2,
     *                 "post_id": 3,
     *                 "contact": {
     *                     "id": 2,
     *                     "first_name": "Fatima",
     *                     "last_name": "Sultana",
     *                     "profile_picture": "media/users/5f1959393731c1595496761.jpeg"
     *                 }
     *             },
     *             {
     *                 "id": 28,
     *                 "contact_id": 3,
     *                 "post_id": 3,
     *                 "contact": {
     *                     "id": 3,
     *                     "first_name": "Hasnain",
     *                     "last_name": "Ali",
     *                     "profile_picture": "media/users/5f1c1dfef10731595678206.png"
     *                 }
     *             },
     *             {
     *                 "id": 42,
     *                 "contact_id": 20,
     *                 "post_id": 3,
     *                 "contact": {
     *                     "id": 20,
     *                     "first_name": "falak",
     *                     "last_name": "Saad",
     *                     "profile_picture": "media/users/5f20223ea98da1595941438.jpeg"
     *                 }
     *             }
     *         ],
     *         "i_liked": true
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "post_id": [
     *             "The post id field is required."
     *         ]
     *     }
     * }
     *
     * @response 422 {
     *     "Response": false,
     *     "StatusCode": 422,
     *     "Message": "Invalid Parameters",
     *     "Result": {
     *         "player_id": [
     *             "The selected post id is invalid."
     *         ]
     *     }
     * }
     *
     * @response 404 {
     *     "Response": false,
     *     "StatusCode": 404,
     *     "Message": "Records not found",
     *     "Result": {}
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     */


    public function single_feed(Request $request)
    {

        Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id'
        ])->validate();

        $access_modifier = AccessModifier::whereIn('name', ['follower', 'public'])->pluck('id')->toArray();
        $public_access_modifier = AccessModifier::where('name', 'public')->first();
        $posts = Post::with(['author:id,first_name,last_name,profile_picture'])->find($request->post_id);

        if (!$posts) {
            return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());
        }

        $author_id = $posts->author_id;
        $current_user_id = Auth::user()->id;

        $check_current_user = Contact::where('user_id', $current_user_id)->where('contact_user_id', $author_id)->first();

        $p = Post::where(function ($q) use ($public_access_modifier, $current_user_id) {
            $q->whereHas('author', function ($q) use ($public_access_modifier) {
                $q->whereHas('user_privacy_settings', function ($q2) use ($public_access_modifier) {
                    $q2->whereIn('access_modifiers.id', $public_access_modifier);
                });
            })->orWhere('author_id', $current_user_id);
        })->find($request->post_id);

        if (!$p) {
            if (!$check_current_user) {

                if ($author_id != $current_user_id) {
                    return Helper::apiNotFoundResponse(false, 'Records not found, You are not following the author.', new stdClass());
                }

            }
        }
        $postss = Post::with(['author:id,first_name,last_name,profile_picture'])
//            ->with(['comments.replies' => function ($q1) {
//                $q1->select('id', 'comment', 'created_at', 'contact_id', 'post_id');
//                $q1->with('contact:id,first_name,last_name,profile_picture');
//                $q1->with(['replies' => function($q2) {
//                    $q2->select('id', 'comment', 'created_at', 'contact_id', 'post_id');
//                    $q2->with('contact:id,first_name,last_name,profile_picture');
//                }]);
//            }])
            ->with(['comments.replies' => function ($q1) {
                $q1->orderBy('id', 'desc')->with('contact:id,first_name,last_name,profile_picture');
            }])
            ->with(['comments' => function ($q1) {
                $q1->orderBy('id', 'desc')->with(['contact:id,first_name,last_name,profile_picture']);
            }])
            ->with(['likes' => function ($q1) {
                $q1->select('id', 'contact_id', 'post_id');
                $q1->with('contact:id,first_name,last_name,profile_picture');
            }])
            ->withCount(['likes as user_likes_count' => function ($q) {
                $q->where('contact_id', Auth::user()->id);
            }])
            ->where(function ($q) use ($access_modifier, $current_user_id) {
                $q->whereHas('author', function ($q) use ($access_modifier) {
                    $q->whereHas('user_privacy_settings', function ($q2) use ($access_modifier) {
                        $q2->whereIn('access_modifiers.id', $access_modifier);
                    });
                })->orWhere('author_id', $current_user_id);
            })
            ->find($request->post_id);

        if (!$postss) {
            return Helper::apiNotFoundResponse(false, 'Records not found, Perhaps! Author has set it to private.', new stdClass());
        }


        $obj = new stdClass();
        $obj->id = $postss->id;
        $obj->author_id = $postss->author_id;
        $obj->exercise_id = $postss->exercise_id;
        $obj->level_id = $postss->level_id;
        $obj->post_title = $postss->post_title;
        $obj->post_desc = $postss->post_desc;
        $obj->thumbnail = $postss->thumbnail;
        $obj->post_attachment = $postss->post_attachment;
        $obj->status_id = $postss->status_id;
        $obj->created_at = $postss->created_at;
        $obj->updated_at = $postss->updated_at;
        $obj->deleted_at = $postss->deleted_at;
        $obj->author = $postss->author;
        $obj->user_likes_count = $postss->user_likes_count;

        if (count($postss->comments) > 0) {
            $comments_array = array();

            foreach ($postss->comments as $comment) {

                $comments_inner_array = array();
                $comments_inner_array['id'] = $comment->id;
                $comments_inner_array['comment'] = $comment->comment;
                $comments_inner_array['created_at'] = $comment->created_at;
                $comments_inner_array['contact_id'] = $comment->contact_id;
                $comments_inner_array['post_id'] = $comment->post_id;
                $comments_inner_array['posted_at'] = $comment->posted_at;
                $comments_inner_array['contact'] = $comment->contact;

                $user = User::find($comment->contact_id);
                $comments_inner_array['user_role'] = $user->roles->pluck('name')[0];

                if (count($comment->replies) > 0) {
                    $replies_array = array();

                    foreach ($comment->replies as $reply) {

                        $replies_inner_array = array();
                        $replies_inner_array['id'] = $reply->id;
                        $replies_inner_array['comment'] = $reply->comment;
                        $replies_inner_array['created_at'] = $reply->created_at;
                        $replies_inner_array['contact_id'] = $reply->contact_id;
                        $replies_inner_array['post_id'] = $reply->post_id;
                        $replies_inner_array['posted_at'] = $reply->posted_at;
                        $replies_inner_array['contact'] = $reply->contact;

                        $user = User::find($reply->contact_id);
                        $replies_inner_array['user_role'] = $user->roles->pluck('name')[0];

                        $replies_array[] = $replies_inner_array;

                    }

                    $comments_inner_array['replies'] = $replies_array;

                } else {
                    $comments_inner_array['replies'] = $comment->replies;
                }

                $comments_array[] = $comments_inner_array;

            }

            $obj->comments = $comments_array;

        } else {
            $obj->comments = $postss->comments;
        }
        $obj->likes = $postss->likes;
        if (count($postss->likes) > 0) {
            $check_contact_ids = array();
            foreach ($postss->likes as $like) {
                $check_contact_ids[] = $like->contact_id;
            }

            if (in_array(Auth::user()->id, $check_contact_ids)) {
                $obj->i_liked = true;
            } else {
                $obj->i_liked = false;
            }
        } else {
            $obj->i_liked = false;
        }


        $post = (array)$obj;

        if (count($post) > 0) {
            return Helper::response(true, 200, 'Records found successfully!', $post);
        }
        return Helper::response(false, 200, 'Records not found', []);
    }


    /**
     * Shinguard Request
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Request has been sent successfully",
     * "Result": {}
     * }
     *
     * @bodyParam name string required
     * @bodyParam email string optional
     * @bodyParam phone string required
     * @bodyParam club string optional
     * @bodyParam team string optional
     * @bodyParam age string required
     * @bodyParam country string required
     * @return JsonResponse
     */
    public function shinguardRequest(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'email',
            'phone' => 'required',
            'age' => 'required',
//            'country' => 'required'
        ]);

        try {
            Mail::send('emails.shinguard_request', ['data' => $request->all()], function ($m) use ($request) {
                $m->to('sales@jogo.ai')->subject('JOGO Shinguard Request');
            });
        } catch (Exception $e) {
            activity()->causedBy(\Auth::user())->performedOn(\Auth::user())->log($e->getMessage());
        }

        return Helper::apiSuccessResponse(true, "Request has been sent successfully", new stdClass());
    }
}
