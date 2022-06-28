<?php
namespace App\Http\Controllers\Api\Dashboard\ParentSharing;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\ParentSharing\InviteRequest;
use App\Http\Requests\Api\Dashboard\ParentSharing\ListingRequest;
use App\User;
use App\Status;
use Illuminate\Http\Request;

/**
    * @group Dashboard / Parent Sharing
*/

class IndexController extends Controller
{
    private $userModel, $limit, $offset, $sortingColumn, $sortingType, $status, $apiType;

    public function __construct()
    {
        $this->userModel = User::class;

        $this->limit = 10;
        
        $this->offset = 0;
        
        $this->sortingColumn = 'created_at';
        
        $this->sortingType = 'asc';
        
        $this->status = Status::select('id')
        ->where('name', 'active')
        ->first()
        ->id;

        $this->apiType = 'trainerDashboard';
    }

    /**
        Invited
        
        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found",
            "Result": [
                {
                    "id": 1,
                    "email": "parent@yopmail.com"
                }
            ]
        }
        
        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "playerId": [
                    "The player id must be a number."
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

        @response 500
        {
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": []
        }

        @queryParam playerId required integer .Example: 1
    */

    public function index(ListingRequest $request)
    {
        $response = (new $this->userModel)->playersParentsListing($request, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType, $this->status, $this->apiType);

        return $response;
    }

    /**
        Invite
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Player has shared with parent successfully",
            "Result": {}
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "email": [
                    "The email must be a valid email address."
                ]
            }
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid player",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
        
        @bodyParam playerId integer required .Example: 1
        @bodyParam email string required min:8 max:254 .Example: shahzaibparent@yopmail.com

        @return JsonResponse
    */

    protected function invite(InviteRequest $request)
    {
        $response = (new $this->userModel)->inviteParent($request);

        return $response;
    }

    /**
        Remove
        
        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "Parent Email removed successfully",
            "Result": {}
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid id",
            "Result": {}
        }

        @response 500
        {
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @bodyParam id integer required .Example: 1
    */

    public function remove(Request $request)
    {
        $response = (new $this->userModel)->removePlayerParents($request);

        return $response;
    }
}