<?php
namespace App\Http\Controllers\Api\ParentSharing\Auth;
use App\Http\Requests\Api\ParentSharing\Auth\Profile\UpdateRequest;
use App\Http\Requests\Api\ParentSharing\Auth\Profile\UpdatePasswordRequest;
use App\Http\Requests\Api\ParentSharing\Auth\Profile\VerifyOtpRequest;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
    * @group Parent Sharing / Profile
*/

class ProfileController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = User::class;
    }

    /**
        Edit

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Success",
            "Result": {
                "firstName": "Shahzaib",
                "lastName": "Parent",
                "email": "shahzaibparent@yopmail.com",
                "permissions": [
                    "player-database",
                    "settings"
                ],
                "token": "dasujdasdyuasuidyasuidhasjdajsdasd8783749387430895789@%&(%#&*(&%#*(%&#()hfjsdhfjksdhfkhsdjkfhsdjkfhsdkf"
            }
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
    */

    protected function index(Request $request)
    {
        $response = (new $this->userModel)->viewProfile($request, auth()->user()->id);

        return $response;
    }

    /**
        Update
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Profile has updated successfully",
            "Result": {}
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "email": [
                    "The email has already been taken."
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

        @bodyParam firstName string required min:3 max:25 .Example: Shahzaib
        @bodyParam lastName string required min:3 max:25 .Example: Parent
        @bodyParam email string required min:8 max:254 .Example: shahzaibparent@yopmail.com
    */

    protected function update(UpdateRequest $request)
    {
        $userId = auth()->user()->id;

        $response = (new $this->userModel)->updateRecord($request, $userId);

        return $response;
    }

    /**
        Verify otp for updating email

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Your otp code has verified successfully",
            "Result": {}
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "ip": [
                    "The ip must be a valid ip address."
                ]
            }
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid otp",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
        
        @bodyParam otp integer required min:6 max:6 .Example: 123456
    */

    protected function verifyOtp(VerifyOtpRequest $request)
    {
        $request->merge([
            'email' => auth()->user()->email
        ]);

        $response = (new $this->userModel)->verifyOtp($request);

        return $response;
    }

    /**
        Resend otp for updating email
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "We've sent you a otp code on your email",
            "Result": {}
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "ip": [
                    "The ip must be a valid up address."
                ]
            }
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid email",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
    */

    protected function resendOtp(Request $request)
    {
        $request->merge([
            'email' => auth()->user()->email
        ]);

        $response = (new $this->userModel)->resendOtp($request);

        return $response;
    }

    /**
        Update password

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Password has updated successfully",
            "Result": {}
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "currentPassword": [
                    "The current password is invalid"
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

        @bodyParam currentPassword string required min:8 max:55 .Example: 123456789
        @bodyParam newPassword string required min:8 max:55 .Example: 123456789
        @bodyParam confirmPassword string required min:8 max:55 .Example: 123456789
        @bodyParam deviceType string required options: web .Example: web
        @bodyParam deviceToken string optional .Example: dfnfsdnADBKFBSuhsd&&#@$jhksd
        @bodyParam ip string required .Example: 203.101.189.100
        @bodyParam macId string required .Example: 10:3b:b0:53:39:a7
    */

    protected function updatePassword(UpdatePasswordRequest $request)
    {
        $userId = auth()->user()->id;

        $response = (new $this->userModel)->changePassword($request, $userId);

        return $response;
    }
}