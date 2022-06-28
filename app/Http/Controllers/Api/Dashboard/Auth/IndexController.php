<?php
namespace App\Http\Controllers\Api\Dashboard\Auth;
use App\Http\Controllers\Controller;
use App\User;
use App\Http\Requests\Api\Dashboard\Auth\SignUpRequest;
use App\Http\Requests\Api\Dashboard\Auth\VerifyOtpRequest;
use App\Http\Requests\Api\Dashboard\Auth\VerifyForgetPasswordOtpRequest;
use App\Http\Requests\Api\Dashboard\Auth\ResendOtpRequest;
use App\Http\Requests\Api\Dashboard\Auth\SignInRequest;
use App\Http\Requests\Api\Dashboard\Auth\AutoSignInRequest;
use App\Http\Requests\Api\Dashboard\Auth\SignOutRequest;
use App\Http\Requests\Api\Dashboard\Auth\ForgetPasswordRequest;
use App\Http\Requests\Api\Dashboard\Auth\UpdatePasswordRequest;
use App\Http\Requests\Api\Dashboard\Auth\ResendSetupPasswordLinkRequest;
use App\Http\Requests\Api\Dashboard\Auth\SetPasswordRequest;
use Illuminate\Http\Request;

/**
    @group Dashboard V4 / Auth
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

        @bodyParam firstName string required min:1 max:25 .Example: Shahzaib
        @bodyParam lastName string required min:1 max:25 .Example: Trainer 001
        @bodyParam email string required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
        @bodyParam newPassword string required min:8 max:55 .Example: 123456789
        @bodyParam confirmPassword string required min:8 max:55 .Example: 123456789
        @bodyParam nationalityId integer required .Example: 1
        @bodyParam promoCode string optional min:3 max:55 .Example: 1
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

        @bodyParam email string required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
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

        @bodyParam email string required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
        @bodyParam password string required min:8 max:55 .Example: 123456789
        @bodyParam otp integer required min:6 max:6 .Example: 123456
        @bodyParam deviceType string required options: web .Example: web
        @bodyParam deviceToken string optional .Example: dfnfsdnADBKFBSuhsd&&#@$jhksd
        @bodyParam ip string required .Example: 203.101.189.100
        @bodyParam macId string required .Example: 10:3b:b0:53:39:a7
    */

    protected function verifyOtp(VerifyOtpRequest $request)
    {
        $response = (new $this->userModel)->verifyOtp($request);
        
        if ($response->original['StatusCode'] == 200)
        {
            $response = (new $this->userModel)->signIn($request);
        }

        return $response;
    }

    /**
        Sign in
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "You've sign in successfully",
            "Result": {
                "id": 1,
                "firstName": "Shahzaib",
                "lastName": "Trainer 001",
                "email": "shahzaibtrainer001@yopmail.com",
                "countryCode": {
                    "id": 164,
                    "code": 92
                },
                "phoneNo": "3482302450",
                "nationality": {
                    "id": 164,
                    "name": "Pakistan"
                },
                "language": {
                    "id": 1,
                    "name": "English"
                },
                "image": "media/users//oFhWoZUUotc1ggNa1WZ0HjUH2wQOaC0iVGrfia9Y.jpg",
                "nextScreen": "",
                "permissions": [
                    "skill-assignment",
                    "exercises",
                    "player-database",
                    "settings",
                    "player-database",
                    "settings"
                ],
                "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyMSIsImp0aSI6ImU5NmI2ODJlNDU5YjMyOTllZWM5OWEwMTZhNjFhYmFlMmY2NGM0YTdiMGU0MjVmMjIyMWYxODBiYjY3N2RlNmUwZDIyMjBhOGM0MGQzYjZkIiwiaWF0IjoxNjI0MjYzMzYzLCJuYmYiOjE2MjQyNjMzNjMsImV4cCI6MTY1NTc5OTM2Mywic3ViIjoiNTIyIiwic2NvcGVzIjpbXX0.i4W2Ev1uTgUr5YSWkMKZNMCMedN6Xi5Qz8PHPZEHyPV5jsaQCQ2Y-vu5FWsBoLI_R1udPmywKtWfsHGeCsJtUyFLsQs7waFCIeyu-qbg04UmwNjCU2rcKglJSlWhv6wKSwTM7QhQHLWShTpgJ2-QDEkt01LXs4jgtFCZIcq9wcgD6Ctnw4TpQcLl4fVv6on-kMoYsJNYNFUkn8OC_VqaVJ_MRluKkCDyLl7LPou0RalW4Xx8O1qGHWFDTI7Qp6YN6fywrCDWokljBwcFYo_bZ2XCKxkdcRYP50wlDGTfD_xWS8LVUFmy239MihFJ8wk-T8rNypPQ7v9owWpvndCbhtmnGt7d4ssZNSktVOYYbSwiiUq2i4vIOB-gk0NpmmtX-FkW3Q7DCj-gl7lyOkmBHOLogwBBeL5oFjSU_AiQ_f_RSmVNSdC50zjNBUC9TmZt0e1dreW3hbazVYpA88vO5uCHQiN1h5lAS11zPcDeeHxpl4lIjUp8TxBR_3NGcX5gF-6-NJe0eEybahN2sfQoToInMab_CDLPiV0J0ypbBrWtPKlkgC2VMMudM3WW_GiOb2CCylk-fwZIhIY0StjGNBH06jAOzbCyhy4jpc9meeCPXuPS8HO8J5R7d2IyWhKNo1chPTzNnT3QSpddxiggcIWfUD73gk5UfuWaCk27BBM",
                "myClubs": [
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
        
        @bodyParam email string required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
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
                "id": 1,
                "firstName": "Shahzaib",
                "lastName": "Trainer 001",
                "email": "shahzaibtrainer001@yopmail.com",
                "countryCode": {
                    "id": 164,
                    "code": 92
                },
                "phoneNo": "3482302450",
                "nationality": {
                    "id": 164,
                    "name": "Pakistan"
                },
                "language": {
                    "id": 1,
                    "name": "English"
                },
                "image": "media/users//oFhWoZUUotc1ggNa1WZ0HjUH2wQOaC0iVGrfia9Y.jpg",
                "nextScreen": "",
                "permissions": [
                    "skill-assignment",
                    "exercises",
                    "player-database",
                    "settings",
                    "player-database",
                    "settings"
                ],
                "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyMSIsImp0aSI6ImU5NmI2ODJlNDU5YjMyOTllZWM5OWEwMTZhNjFhYmFlMmY2NGM0YTdiMGU0MjVmMjIyMWYxODBiYjY3N2RlNmUwZDIyMjBhOGM0MGQzYjZkIiwiaWF0IjoxNjI0MjYzMzYzLCJuYmYiOjE2MjQyNjMzNjMsImV4cCI6MTY1NTc5OTM2Mywic3ViIjoiNTIyIiwic2NvcGVzIjpbXX0.i4W2Ev1uTgUr5YSWkMKZNMCMedN6Xi5Qz8PHPZEHyPV5jsaQCQ2Y-vu5FWsBoLI_R1udPmywKtWfsHGeCsJtUyFLsQs7waFCIeyu-qbg04UmwNjCU2rcKglJSlWhv6wKSwTM7QhQHLWShTpgJ2-QDEkt01LXs4jgtFCZIcq9wcgD6Ctnw4TpQcLl4fVv6on-kMoYsJNYNFUkn8OC_VqaVJ_MRluKkCDyLl7LPou0RalW4Xx8O1qGHWFDTI7Qp6YN6fywrCDWokljBwcFYo_bZ2XCKxkdcRYP50wlDGTfD_xWS8LVUFmy239MihFJ8wk-T8rNypPQ7v9owWpvndCbhtmnGt7d4ssZNSktVOYYbSwiiUq2i4vIOB-gk0NpmmtX-FkW3Q7DCj-gl7lyOkmBHOLogwBBeL5oFjSU_AiQ_f_RSmVNSdC50zjNBUC9TmZt0e1dreW3hbazVYpA88vO5uCHQiN1h5lAS11zPcDeeHxpl4lIjUp8TxBR_3NGcX5gF-6-NJe0eEybahN2sfQoToInMab_CDLPiV0J0ypbBrWtPKlkgC2VMMudM3WW_GiOb2CCylk-fwZIhIY0StjGNBH06jAOzbCyhy4jpc9meeCPXuPS8HO8J5R7d2IyWhKNo1chPTzNnT3QSpddxiggcIWfUD73gk5UfuWaCk27BBM",
                "myClubs": [
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
        
        @bodyParam email string required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
        @bodyParam clubId integer optional min:1 .Example: 1
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

        @bodyParam email string required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
    */

    /**
        Verify forget password otp

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

        @bodyParam email string required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
        @bodyParam otp integer required min:6 max:6 .Example: 123456
        @bodyParam deviceType string required options: web .Example: web
        @bodyParam deviceToken string optional .Example: dfnfsdnADBKFBSuhsd&&#@$jhksd
        @bodyParam ip string required .Example: 203.101.189.100
        @bodyParam macId string required .Example: 10:3b:b0:53:39:a7
    */

    protected function verifyForgetPasswordOtp(VerifyForgetPasswordOtpRequest $request)
    {
        $response = (new $this->userModel)->verifyOtp($request);
        
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
            "Message": "Account is inactive",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @bodyParam email string required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
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

        @bodyParam email string required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
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
        Resend setup password link

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "We've mailed you a new setup password link",
            "Result": {}
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "token": [
                    "The token must be a valid."
                ]
            }
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid user token",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @queryParam token required string. Example: e6a4015b948dcd299e7b27ae8f95921f
    */

    protected function resendSetupPasswordLink(ResendSetupPasswordLinkRequest $request)
    {
        $response = (new $this->userModel)->resendSetupPasswordLink($request);

        return $response;
    }

    /**
        Set password

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

        @bodyParam email string required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
        @bodyParam newPassword string required min:8 max:55 .Example: 123456789
        @bodyParam confirmPassword string required min:8 max:55 .Example: 123456789
        @bodyParam otp integer required min:6 max:6 .Example: 123456
        @bodyParam token string required .Example: 5ff1276afe77d0d9ed489ba68dbc0fe2
        @bodyParam deviceType string required options: web .Example: web
        @bodyParam deviceToken string optional .Example: dfnfsdnADBKFBSuhsd&&#@$jhksd
        @bodyParam ip string required .Example: 203.101.189.100
        @bodyParam macId string required .Example: 10:3b:b0:53:39:a7
    */

    protected function setPassword(SetPasswordRequest $request)
    {
        $response = (new $this->userModel)->updatePassword($request);

        return $response;
    }
}