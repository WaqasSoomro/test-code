<?php

namespace App\Http\Controllers\Api;

use App\AccessModifier;
use App\Contact;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\AddEditRequest;
use App\Http\Requests\Post\DeleteRequest;
use App\Http\Requests\Post\ShowRequest;
use App\PlayerExercise;
use App\Post;
use App\Status;
use App\Story;
use App\User;
use App\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use stdClass;

/**
 * @authenticated
 * @group User Posts
 *
 * APIs for posts
 */
class StoryController extends Controller
{
    /**
     * Fecth all stories
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "stories.feeds.found",
     *     "Result": {
     *         "data": [
     *             {
     *                 "id": 2,
     *                 "author_id": 106,
     *                 "thumbnail": "media/stories/thumbnails/5fc55827e03e91606768679.jpeg",
     *                 "media": "media/stories/5fc55827e3d141606768679.jpeg",
     *                 "media_type": "jpeg",
     *                 "is_viewed": 0,
     *                 "created_at": "2020-11-30T20:37:59.000000Z",
     *                 "updated_at": "2020-11-30T20:37:59.000000Z",
     *                 "deleted_at": null
     *             },
     *             {
     *                 "id": 1,
     *                 "author_id": 106,
     *                 "thumbnail": "media/stories/thumbnails/5fc557fc735b61606768636.jpeg",
     *                 "media": "media/stories/5fc557fc771c51606768636.jpeg",
     *                 "media_type": "jpeg",
     *                  "is_viewed: 1,
     *                 "created_at": "2020-11-30T20:34:38.000000Z",
     *                 "updated_at": "2020-11-30T21:07:00.000000Z",
     *                 "deleted_at": null
     *             }
     *         ],
     *         "meta": {
     *             "current_page": 1,
     *             "first_page_url": "http://localhost/JOGO-PHP/api/v1/app/stories?page=1",
     *             "from": 1,
     *             "last_page": 1,
     *             "last_page_url": "http://localhost/JOGO-PHP/api/v1/app/stories?page=1",
     *             "next_page_url": null,
     *             "per_page": 5,
     *             "prev_page_url": null,
     *             "total": 2
     *         }
     *     }
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

    private function storyQyeryIndex()
    {
        $story = Story::with(['author:id,first_name,last_name,profile_picture','views']);

        return $story;
    }

    public function index()
    {
        $followingsIds = Contact::where('user_id', auth()->user()->id)
        ->pluck('contact_user_id')
        ->toArray();

        $accessModifier = AccessModifier::whereIn('name', ['follower', 'public'])
        ->pluck('id')
        ->toArray();

        $stories = $this->storyQyeryIndex()->whereIn('author_id', $followingsIds)
        ->whereHas('author.user_privacy_settings', function ($query) use($accessModifier)
        {
            $query->whereIn('accessModifiers.id', $accessModifier);
        })
        ->where('author_id', '!=', auth()->user()->id())
        ->orderBy('created_at', 'desc')
        ->get();

        $my_stories = $this->storyQyeryIndex()->latest()
            ->where('author_id', Auth::user()->id)
            ->get();


        if (count($stories) > 0 || count($my_stories) > 0) {
            $storiess = $stories->merge($my_stories);

            $stories = $storiess->map(function ($ex) {

                $obj = new stdClass();
                $obj->id = $ex->id;
                $obj->author_id = $ex->author_id;
                $obj->thumbnail = $ex->thumbnail;
                $obj->media = $ex->media;
                $obj->media_type = $ex->media_type;
                $obj->created_at = $ex->created_at;
                $obj->updated_at = $ex->updated_at;
                $obj->deleted_at = $ex->deleted_at;
                $obj->is_viewed = $ex->views()->find(auth()->user()->id)?true:false;
                return $obj;
            });

            $new_stories = $stories->sortByDesc(function ($element) {
                return $element->created_at;
            });

            $new_stories = collect($new_stories);
            $new_stories = Helper::paginate($new_stories, 5);
            $meta = $new_stories->toArray();

            $response = [
                'data' => $new_stories->values()->all(),
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

            return Helper::apiSuccessResponse(true, __('stories.feeds.found'), $response);
        }

        return Helper::apiNotFoundResponse(false, __('stories.feeds.not_found'), []);
    }

    /**
     * Fetch Single Story
     *
     * Getting story of the current player
     *
     * @queryParam  story_id required story id is required
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Record found successfully!",
     *     "Result": {
     *         "id": 1,
     *         "author_id": 106,
     *         "thumbnail": "media/stories/thumbnails/5fc557fc735b61606768636.jpeg",
     *         "media": "media/stories/5fc557fc771c51606768636.jpeg",
     *         "media_type": "jpeg",
     *         "created_at": "2020-11-30 20:34:38",
     *         "updated_at": "2020-11-30 20:37:16",
     *         "deleted_at": null
     *     }
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
    public function show(Request $request)
    {
        Validator::make($request->all(), [
            'story_id' => 'required|exists:stories,id'
        ])->validate();

        $story = Story::where('author_id', Auth::user()->id)
            ->where('id', $request->story_id)
            ->first();
        $story->views()->sync(auth()->user()->id);
        if ($story) {
            return Helper::apiSuccessResponse(true, 'Record found successfully!', $story);
        }

        return Helper::apiNotFoundResponse(false, 'Record not found', new stdClass());
    }

    /**
     * Add-Update Story
     *
     * Adding/Updating story of the current player
     *
     * @queryParam  story_id optional
     * @queryParam  thumbnail required
     * @queryParam  media required
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Record has been saved",
     *     "Result": {
     *         "author_id": 106,
     *         "thumbnail": "media/stories/thumbnails/5fc55827e03e91606768679.jpeg",
     *         "media": "media/stories/5fc55827e3d141606768679.jpeg",
     *         "media_type": "jpeg",
     *         "updated_at": "2020-11-30 20:37:59",
     *         "created_at": "2020-11-30 20:37:59",
     *         "id": 2
     *     }
     * }
     *
     */
    public function addEdit(Request $request)
    {

        $story = Story::where('author_id', Auth::user()->id)->where('id', $request->story_id)->first();
        if (!$story) {
            $story = new Story();
        }
        $request->request->add(['author_id' => Auth::user()->id]);

        $thumbnail_path = "";
        if (Storage::exists($story->thumbnail)) {
            Storage::delete($story->thumbnail);
        }

        if ($request->thumbnail != "") {
            $thumbnail_path = Helper::uploadBase64File($request->thumbnail, Story::$media_thumbnails);
        }

        $media_path = "";
        if (Storage::exists($story->media)) {
            Storage::delete($story->media);
        }
        if ($request->media != "") {
            $media_path = Helper::uploadBase64File($request->media, Story::$media);
        }

        $media_file_extension = pathinfo($media_path, PATHINFO_EXTENSION);
        $story->author_id =  Auth::user()->id;
        $story->thumbnail = $thumbnail_path;
        $story->media = $media_path;
        $story->media_type = $media_file_extension;
        $story->save();
        return Helper::apiSuccessResponse(true, 'Record has been saved', $story);
    }

    /**
     * Delete a story
     *
     * Deleting story of the current player
     *
     * @queryParam  story_id required story id is required
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
    public function delete(Request $request)
    {
        Validator::make($request->all(), [
            'story_id' => 'required|exists:stories,id'
        ])->validate();

        $story = Story::where('author_id', Auth::user()->id)->where('id', $request->story_id)->first();
        if ($story) {
            $story->delete();
            return Helper::apiSuccessResponse(true, 'Record has been deleted', new stdClass());
        }
        return Helper::apiNotFoundResponse(false, 'Record not found', new stdClass());
    }
}
