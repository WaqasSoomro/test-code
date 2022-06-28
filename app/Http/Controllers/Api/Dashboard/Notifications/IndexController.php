<?php
namespace App\Http\Controllers\Api\Dashboard\Notifications;
use App\Http\Controllers\Controller;
use App\UserNotification;
use App\Http\Requests\Api\Dashboard\Notifications\IndexRequest;
use Illuminate\Http\Request;

/**
    @group Dashboard V4 / Notifications
*/

class IndexController extends Controller
{
    private $notificationModel;

    public function __construct()
    {
        $this->notificationModel = UserNotification::class;
    }

    /**
        Listing

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found",
            "Result": [
                {
                    "id": 1736,
                    "recordId": 3,
                    "type": "new-message",
                    "description": "Hy",
                    "actionType": "Chat",
                    "notificationFrom": {
                        "id": 527,
                        "firstName": "Shahzaib",
                        "lastName": "Trainer 003"
                    }
                }
            ]
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "limit": [
                    "The limit field is required."
                ]
            }
        }
    
        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "No records found",
            "Result": []
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": []
        }

        @queryParam types[] optional array. Example: new-message
        @queryParam limit required integer. Example: 10
        @queryParam offset required integer. Example: 0
    */

    protected function index(IndexRequest $request)
    {
        $userId = auth()->user()->id;

        $response = (new $this->notificationModel)->viewRecords($request, $userId);

        return $response;
    }
}