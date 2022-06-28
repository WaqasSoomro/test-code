<?php
namespace App\Http\Controllers\Api\Dashboard;
use App\UserNotification;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use stdClass;

/**
    * @authenticated
    * @group Dashboard / Notifications
    * APIs to manage Trainer Notifications
*/

class NotificationsController extends Controller
{

    /**
        * SetNotification
        *
        * @response {
        * "Response": true,
        * "StatusCode": 200,
        * "Message": "Successfully created notification!",
        * "Result": {}
        * }
        *
        * @bodyParam user_id integer required Target User Id
        * @bodyParam name string required Name max 191 chars
        * @bodyParam description string required Description max 1350 chars
        * @bodyParam image mimes optional
        *
        * @return JsonResponse
     */

    public function setNotification(Request $request)
    {
        //Validate Data
        Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|max:191',
            'description'=> 'required|max:1350',
            'image' => 'mimes:jpeg,png'

        ])
        ->validate();

        //Store Data
        $user_notifications = new UserNotification();

        $user_notifications->name = $request->name;
        $user_notifications->description = $request->description;
        $user_notifications->user_id = $request->user_id;

        //Store image in storage
        if (Storage::exists($request->image) && $request->hasFile('image'))
        {
            Storage::delete($request->image);
        }

        $path = "";

        if ($request->hasFile('image'))
        {
            $path = Storage::putFile($user_notifications->media, $request->image);
        }

        $user_notifications->image = $path;
        $user_notifications->save();

        //Check response
        if($user_notifications->save())
        {
            return Helper::apiSuccessResponse(true, "Successfully created notification!", new stdClass());
        }
        else
        {
            return Helper::apiErrorResponse(true, "There was an error creating notification, please try again later.", new stdClass());
        }
    }

    /**
        Notifications Listing
        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "Notifications found",
            "Result": [
                {
                    "id": 1555,
                    "profile_picture": "",
                    "description": "aah knew it",
                    "click_action": "Chat",
                    "model_type": "new-message",
                    "model_type_id": 8,
                    "role": "player",
                    "status": "unread",
                    "created_at": "1 week ago"
                }
            ]
        }

        @response 500
        {
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": []
        }
        
        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Notifications not found",
            "Result": []
        }
    **/

    public function getNotification()
    {
        try
        {
            $notifications = UserNotification::where('to_user_id', auth()->user()->id)
            ->with('status')
            ->with('from_user.roles')
            ->latest()
            ->get();
            
            if (count($notifications) == 0)
            {
                return Helper::apiNotFoundResponse(false, 'Notifications not found', []);
            }

            $_notifications = $notifications->map(function ($item)
            {
                return Helper::getUserNotificationObject($item);
            });

            return Helper::apiSuccessResponse(true, 'Notifications found', $_notifications);
        }
        catch (Exception $e)
        {
            return Helper::apiErrorResponse(false, 'Something wen\'t wrong', []);
        }
    }
}