<?php
namespace App\Http\Controllers\Api\Dashboard\Auth;
use App\Http\Controllers\Controller;
use App\User;
use App\Http\Requests\Api\Dashboard\Auth\Profile\SetRequest;
use App\Http\Requests\Api\Dashboard\Auth\Profile\UpdateRequest;
use App\Http\Requests\Api\Dashboard\Auth\Profile\ShortUpdateRequest;
use App\Http\Requests\Api\Dashboard\Auth\Profile\VerifyOtpRequest;
use App\Http\Requests\Api\Dashboard\Auth\Profile\UpdatePasswordRequest;
use App\Http\Requests\Api\Dashboard\Auth\Profile\ViewProfileRequest;
use Illuminate\Http\Request;

/**
    @group Dashboard V4 / Profile
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
        Setup

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Profile has set successfully",
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
                    "The phone no must be numeric."
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

        @bodyParam firstName string required min:1 max:25 .Example: Shahzaib
        @bodyParam lastName string required min:1 max:25 .Example: Parent
        @bodyParam countryCode integer min:1 max:3 required .Example: 92
        @bodyParam phoneNo integer required min:4 max:12 .Example: 1234567890
        @bodyParam image file required mimes:jpeg, jpg, png
    */

    protected function setup(SetRequest $request)
    {
        $response = (new $this->userModel)->setupProfile($request);

        return $response;
    }

    /**
        Update
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Profile has updated successfully",
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

        @bodyParam firstName string required min:1 max:25 .Example: Shahzaib
        @bodyParam lastName string required min:1 max:25 .Example: Trainer 001
        @bodyParam email string required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
        @bodyParam countryCode integer min:1 max:3 required .Example: 92
        @bodyParam phoneNo integer required min:4 max:12 .Example: 1234567890
        @bodyParam nationalityId integer required .Example: 1
        @bodyParam languageId integer required .Example: 1
        @bodyParam image file nullable mimes:jpeg, jpg, png
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
                "otp": [
                    "The otp must be a valid otp."
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

    /**
        Edit setup profile
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Success",
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

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
    */

    protected function editProfile(Request $request)
    {
        $response = (new $this->userModel)->viewProfile($request, auth()->user()->id);
        
        return $response;
    }

    /**
        Update setup profile
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Profile has updated successfully",
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

        @bodyParam firstName string required min:1 max:25 .Example: Shahzaib
        @bodyParam lastName string required min:1 max:25 .Example: Trainer 001
        @bodyParam countryCode integer min:1 max:3 required .Example: 92
        @bodyParam phoneNo integer required min:4 max:12 .Example: 1234567890
        @bodyParam image file nullable mimes:jpeg, jpg, png
    */

    protected function updateProfile(ShortUpdateRequest $request)
    {
        $userId = auth()->user()->id;
        
        $response = (new $this->userModel)->updateRecord($request, $userId);

        return $response;
    }

    /**
        View profile
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Success",
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

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @queryParam token required string. Example: e6a4015b948dcd299e7b27ae8f95921f
    */

    protected function viewProfile(ViewProfileRequest $request)
    {
        $response = (new $this->userModel)->viewProfile($request, $request->token);
        
        return $response;
    }
}