<?php
namespace App\Http\Controllers\Api\Dashboard\Clubs;
use App\Http\Controllers\Controller;
use App\User;
use App\Club;
use App\Http\Requests\Api\Dashboard\Clubs\JoiningRequest;
use App\Http\Requests\Api\Dashboard\Clubs\CreateRequest;
use App\Http\Requests\Api\Dashboard\Clubs\UpdateRequest;
use App\Http\Requests\Api\Dashboard\Clubs\VerificationRequest;
use Illuminate\Http\Request;

/**
    @group Dashboard V4 / Clubs
*/
        
class IndexController extends Controller
{
    private $userModel, $clubModel;

    public function __construct()
    {
        $this->userModel = User::class;

        $this->clubModel = Club::class;
    }

    /**
        Joining

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "You've joined this club successfully",
            "Result": {
                "clubId": 1,
                "nextScreen": "/register/requested"
            }
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "id": [
                    "The id must be numeric."
                ]
            }
        }
    
        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid user",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @bodyParam id integer required min:1 required .Example: 1
    */

    protected function joining(JoiningRequest $request)
    {
        $response = (new $this->userModel)->joiningClub($request);

        return $response;
    }

    /**
        Remind Owner

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Email has sent successfully to the club owner",
            "Result": {}
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid user",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
    */

    protected function remindOwner(Request $request)
    {
        $response = (new $this->userModel)->remindClubOwner($request);

        return $response;
    }

    /**
        Explore jogo

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "You've created your club successfully",
            "Result": {}
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid user",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "You've created your club already",
            "Result": {}
        }
    */

    protected function exploreJogo(Request $request)
    {
        $response = (new $this->clubModel)->create($request);

        return $response;
    }

    /**
        Create

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "You've created your club successfully",
            "Result": {
                "id": 42,
                "userName": "karachiclub",
                "name": "Karachi Club",
                "type": "Pro Club",
                "primaryColor": "#ff0000",
                "secondaryColor": "#0000ff",
                "privacy": "Closed For Invites",
                "image": "media/clubs/qaFG4iqNxb1MxdEL9haEdyTAL36BKBB5RRFeyKZw.png",
                "is_verified": "No"
            }
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "userName": [
                    "The club username must be string."
                ]
            }
        }
    
        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid user",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @bodyParam userName string required min:1 max:50 required .Example: ClubOne
        @bodyParam name string required min:1 max:50 required .Example: Club One
        @bodyParam type string required options:Amateur Club, Football Academy, Pro Club .Example: Amateur Club
        @bodyParam primaryColor string required min:3 max:5 .Example: #FFFF00
        @bodyParam secondaryColor string required min:3 max:5 .Example: #000000
        @bodyParam privacy string options:open_to_invites, closed_for_invites .Example: open_to_invites
        @bodyParam image file required mimes:jpeg, jpg, png
    */

    protected function create(CreateRequest $request)
    {
        $response = (new $this->clubModel)->create($request);

        return $response;
    }

    /**
        Create another

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "You've created your club successfully",
            "Result": {
                "id": 42,
                "userName": "karachiclub",
                "name": "Karachi Club",
                "type": "Pro Club",
                "primaryColor": "#ff0000",
                "secondaryColor": "#0000ff",
                "privacy": "Closed For Invites",
                "image": "media/clubs/qaFG4iqNxb1MxdEL9haEdyTAL36BKBB5RRFeyKZw.png",
                "is_verified": "No"
            }
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "userName": [
                    "The club username must be string."
                ]
            }
        }
    
        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid user",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @bodyParam userName string required min:1 max:50 required .Example: ClubOne
        @bodyParam name string required min:1 max:50 required .Example: Club One
        @bodyParam type string required options:Amateur Club, Football Academy, Pro Club .Example: Amateur Club
        @bodyParam primaryColor string required min:3 max:5 .Example: #FFFF00
        @bodyParam secondaryColor string required min:3 max:5 .Example: #000000
        @bodyParam privacy string options:open_to_invites, closed_for_invites .Example: open_to_invites
        @bodyParam image file required mimes:jpeg, jpg, png
    */

    protected function createAnother(CreateRequest $request)
    {
        $response = (new $this->clubModel)->create($request);

        return $response;
    }

    /**
        Own

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found",
            "Result": [
                {
                    "id": 42,
                    "name": "Karachi Club",
                    "image": "media/clubs/qaFG4iqNxb1MxdEL9haEdyTAL36BKBB5RRFeyKZw.png",
                    "primaryColor": "#ff0000",
                    "secondaryColor": "#0000ff",
                    "isOwner": true
                }
            ]
        }
        
        @response 404{
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
    */

    protected function myCLubs(Request $request)
    {
        $response = (new $this->clubModel)->myCLubs($request);

        return $response;
    }

    /**
        Edit

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Record found",
            "Result": {
                "id": 42,
                "userName": "karachiclub",
                "name": "Karachi Club",
                "type": "Pro Club",
                "primaryColor": "#ff0000",
                "secondaryColor": "#0000ff",
                "privacy": "Closed For Invites",
                "image": "media/clubs/qaFG4iqNxb1MxdEL9haEdyTAL36BKBB5RRFeyKZw.png",
                "is_verified": "No"
            }
        }
        
        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "No record found",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
    */

    protected function edit(Request $request, $id)
    {
        $response = (new $this->clubModel)->editCLub($request, $id);

        return $response;
    }

    /**
        Update

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "You've updated your club successfully",
            "Result": {
                "id": 42,
                "userName": "karachiclub",
                "name": "Karachi Club",
                "type": "Pro Club",
                "primaryColor": "#ff0000",
                "secondaryColor": "#0000ff",
                "privacy": "Closed For Invites",
                "image": "media/clubs/qaFG4iqNxb1MxdEL9haEdyTAL36BKBB5RRFeyKZw.png",
                "is_verified": "No"
            }
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "userName": [
                    "The username must be string."
                ]
            }
        }
    
        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid user",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @bodyParam userName string required min:1 max:50 required .Example: ClubOne
        @bodyParam name string required min:1 max:50 required .Example: Club One
        @bodyParam type string required options:Amateur Club, Football Academy, Pro Club .Example: Amateur Club
        @bodyParam primaryColor string required min:3 max:5 .Example: #FFFF00
        @bodyParam secondaryColor string required min:3 max:5 .Example: #000000
        @bodyParam privacy string options:open_to_invites, closed_for_invites .Example: open_to_invites
        @bodyParam image file optional mimes:jpeg, jpg, png
    */

    protected function update(UpdateRequest $request, $id)
    {
        $response = (new $this->clubModel)->create($request, $id);

        return $response;
    }

    /**
        Verification request

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Your request to verify this club has sent successfully to Jogo admin",
            "Result": {}
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "id": [
                    "The id must be numeric."
                ]
            }
        }
    
        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid club id",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @bodyParam id integer required min:1 required .Example: 1
    */

    protected function verificationRequest(VerificationRequest $request)
    {
        $response = (new $this->clubModel)->verificationRequest($request);

        return $response;
    }

    /**
        Delete

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "You've successfully deleted your club",
            "Result": {}
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid club id",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
    */

    protected function delete(Request $request, $id)
    {
        $response = (new $this->clubModel)->remove($request, $id);

        return $response;
    }
}