<?php
namespace App\Http\Controllers\Api\ParentSharing\Auth;
use App\Http\Controllers\Controller;
use App\User;
use App\Http\Requests\Api\ParentSharing\Auth\SignUpRequest;
use App\Http\Requests\Api\ParentSharing\Auth\SignInRequest;
use App\Http\Requests\Api\ParentSharing\Auth\AutoSignInRequest;
use App\Http\Requests\Api\ParentSharing\Auth\VerifyOtpRequest;
use App\Http\Requests\Api\ParentSharing\Auth\ResendOtpRequest;
use App\Http\Requests\Api\ParentSharing\Auth\ForgetPasswordRequest;
use App\Http\Requests\Api\ParentSharing\Auth\UpdatePasswordRequest;
use App\Http\Requests\Api\Dashboard\Auth\SignOutRequest;

/**
    * @group Parent Sharing / Auth
*/

class IndexController extends Controller
{
	private $userModel;

	public function __construct()
	{
		$this->userModel = User::class;
	}

	/**
        Sign up

        @response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "You've sign up successfully",
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

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @bodyParam firstName string required min:3 max:25 .Example: Shahzaib
        @bodyParam lastName string required min:3 max:25 .Example: Parent
        @bodyParam email string required min:8 max:254 .Example: shahzaibparent@yopmail.com
        @bodyParam newPassword string required min:8 max:55 .Example: 123456789
        @bodyParam confirmPassword string required min:8 max:55 .Example: 123456789
        @bodyParam deviceType string required options: web .Example: web
        @bodyParam deviceToken string optional .Example: dfnfsdnADBKFBSuhsd&&#@$jhksd
        @bodyParam ip string required .Example: 203.101.189.100
        @bodyParam macId string required .Example: 10:3b:b0:53:39:a7
    */

	protected function signUp(SignUpRequest $request)
	{
		$response = (new $this->userModel)->signUp($request);

		return $response;
	}

	/**
        Sign in
        
        @response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "You've sign in successfully",
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
            "Message": "Invalid email",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
		
		@bodyParam email string required min:8 max:254 .Example: shahzaibparent@yopmail.com
        @bodyParam password string required min:8 max:55 .Example: 123456789
        @bodyParam deviceType string required options: web .Example: web
        @bodyParam deviceToken string optional .Example: dfnfsdnADBKFBSuhsd&&#@$jhksd
        @bodyParam ip string required .Example: 203.101.189.100
        @bodyParam macId string required .Example: 10:3b:b0:53:39:a7
    */

	protected function signIn(SignInRequest $request)
	{
		$response = (new $this->userModel)->signIn($request);

		return $response;
	}

	/**
        Auto sign in
        
        @response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "You've sign in successfully",
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

        @response 422{
		    "Response": false,
		    "StatusCode": 422,
		    "Message": "Invalid Parameters",
		    "Result": {
		        "deviceType": [
		            "The device type must be a valid device type."
		        ]
		    }
		}

		@response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid ip",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
		
		@bodyParam email string required min:8 max:254 .Example: shahzaibparent@yopmail.com
        @bodyParam deviceType string required options: web .Example: web
        @bodyParam deviceToken string optional .Example: dfnfsdnADBKFBSuhsd&&#@$jhksd
        @bodyParam ip string required .Example: 203.101.189.100
        @bodyParam macId string required .Example: 10:3b:b0:53:39:a7
    */

	protected function autoSignIn(AutoSignInRequest $request)
	{
		$response = (new $this->userModel)->autoSignIn($request);

		return $response;
	}

	/**
        Verify otp

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
		        "email": [
		            "The email must be a valid email address."
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

        @bodyParam email string required min:8 max:254 .Example: shahzaibparent@yopmail.com
        @bodyParam otp integer required min:6 max:6 .Example: 123456
        @bodyParam deviceType string required options: web .Example: web
        @bodyParam deviceToken string optional .Example: dfnfsdnADBKFBSuhsd&&#@$jhksd
        @bodyParam ip string required .Example: 203.101.189.100
        @bodyParam macId string required .Example: 10:3b:b0:53:39:a7
    */

	protected function verifyOtp(VerifyOtpRequest $request)
	{
		$response = (new $this->userModel)->verifyOtp($request);

		return $response;
	}

	/**
        Resend otp
        
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
		        "email": [
		            "The email must be a valid email address."
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

        @bodyParam email string required min:8 max:254 .Example: shahzaibparent@yopmail.com
        @bodyParam deviceType string required options: web .Example: web
        @bodyParam deviceToken string optional .Example: dfnfsdnADBKFBSuhsd&&#@$jhksd
        @bodyParam ip string required .Example: 203.101.189.100
        @bodyParam macId string required .Example: 10:3b:b0:53:39:a7
    */

	protected function resendOtp(ResendOtpRequest $request)
	{
		$response = (new $this->userModel)->resendOtp($request);

		return $response;
	}

	/**
        Forget password
        
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
		        "email": [
		            "The email must be a valid email address."
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

        @bodyParam email string required min:8 max:254 .Example: shahzaibparent@yopmail.com
    */

	protected function forgetPassword(ForgetPasswordRequest $request)
	{
		$response = (new $this->userModel)->forgetPassword($request);

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
		        "email": [
		            "The email must be a valid email address."
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

        @bodyParam email string required min:8 max:254 .Example: shahzaibparent@yopmail.com
        @bodyParam newPassword string required min:8 max:55 .Example: 123456789
        @bodyParam confirmPassword string required min:8 max:55 .Example: 123456789
        @bodyParam otp integer required min:6 max:6 .Example: 123456
        @bodyParam deviceType string required options: web .Example: web
        @bodyParam deviceToken string optional .Example: dfnfsdnADBKFBSuhsd&&#@$jhksd
        @bodyParam ip string required .Example: 203.101.189.100
        @bodyParam macId string required .Example: 10:3b:b0:53:39:a7
    */

	protected function updatePassword(UpdatePasswordRequest $request)
	{
		$response = (new $this->userModel)->updatePassword($request);

		return $response;
	}

	/**
        Sign out
		
		@response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "You've sign out successfully",
		    "Result": {}
		}

		@response 422{
		    "Response": false,
		    "StatusCode": 422,
		    "Message": "Invalid Parameters",
		    "Result": {
		        "ip": [
		            "The ip must be a valid ip."
		        ]
		    }
		}

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @bodyParam deviceType string required options: web .Example: web
        @bodyParam deviceToken string optional .Example: dfnfsdnADBKFBSuhsd&&#@$jhksd
        @bodyParam ip string required .Example: 203.101.189.100
        @bodyParam macId string required .Example: 10:3b:b0:53:39:a7
    */

	protected function signOut(SignOutRequest $request)
	{
		$response = (new $this->userModel)->signOut($request);

		return $response;
	}
}