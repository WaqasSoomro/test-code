<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\User;
use App\UserCoupon;
use App\UserDevice;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use stdClass;

/**
 * @group Dashboard / Auth
 * APIs for dashboard authentication
 */
class WebAuthController extends Controller
{
    /**
     * Register
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "A One-Time password has been sent to your email",
     * "Result": {}
     * }
     *
     * @bodyParam first_name string nullable max 191 chars
     * @bodyParam last_name string nullable max 191 chars
     * @bodyParam surname string nullable max 191 chars
     * @bodyParam email string required max 191 chars
     * @bodyParam password string required max 191 chars
     * @bodyParam mac_id string required
     * @bodyParam device_type string required options: web
     * @bodyParam nationality_id string required
     * @bodyParam coupon string optional Promo Code
     *
     * @return JsonResponse
     */
    /*public function register(Request $request)
    {

        Validator::make($request->all(), [
            'first_name' => 'nullable|max:191',
            'last_name' => 'nullable|max:191',
            'surname' => 'nullable|max:191',
            'email' => 'required|max:191|unique:users',
            'password' => 'required',
            'mac_id' => 'required',
            'ip' => 'required',
            'device_type' => 'required|in:web',
            'nationality_id' => 'required|exists:countries,id'
        ])->validate();

        $otp_code = Helper::generateOtp();

        $user = new User();
        $request->request->add(['verification_code' => $otp_code]);
        $user = $user->registerWebUser($request);
        if ($request->coupon != '') {
            $userCoupon = new UserCoupon;
            $userCoupon->user_id = $user->id;
            $userCoupon->code = $request->coupon;
            $userCoupon->save();
        }

        try {
            Mail::send('emails.send_otp', ['user' => $user, 'otp_code' => $otp_code], function ($m) use ($user) {
                $m->to($user->email, $user->first_name)->subject('JOGO OTP-Code');
            });
        } catch (Exception $e) {
            activity()->causedBy($user)->performedOn($user)->log($e->getMessage());
        }

        return Helper::apiSuccessResponse(true, "A One-Time password has been sent to your email", new stdClass());
    }*/

    /**
     * Login
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "LogIn successful!",
     * "Result": {
     * "id": 3,
     * "nationality_id": null,
     * "first_name": "tr 2",
     * "middle_name": null,
     * "last_name": "rerum",
     * "surname": "quis",
     * "email": "shahzaib@jogo.ai",
     * "phone": null,
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": null,
     * "date_of_birth": null,
     * "verification_code": null,
     * "verified_at": "2020-07-21 13:32:40",
     * "active": 0,
     * "status_id": 2,
     * "created_at": "2020-07-21 13:30:47",
     * "updated_at": "2020-07-21 13:32:40",
     * "deleted_at": null,
     * "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYjA1YjExM2EzNGRkM2RlMjFjZDM2YTExN2FmNzlhYTdkMWEyN2ZiZTYyMzNhZGIxZDMzZmE5MzE5MGFhMmIwMTQ0MWU1MGY4MWVmMzljNTMiLCJpYXQiOjE1OTUzMzg1MjIsIm5iZiI6MTU5NTMzODUyMiwiZXhwIjoxNjI2ODc0NTIyLCJzdWIiOiIzIiwic2NvcGVzIjpbXX0.jxuInAZ47hv5A2-tpzFRAPr_6sB_D8zBdZ82qdP56bu8e4swZY17HnejWT6EMPZNYUu3ercRuiwoScjvuzpmNFyXvGo-0t9KCcQ5Vge_zvfI7Cpo8ZqrInUnGswmuXOGhZ-zaBCwOEfUud2R4cSUdnDJ0aXVoOLHT7DbLQ0RiEA0wKi2u-idqJZYz7sCrrlTB1_8b8TTgWygpLHaNCRjcmJOGzvRmaT04KwG0fRT1YnfB2kmuyv36MULzXf_bbPQaTX-ww_-81S7j5m1gXDr4YaoW9jxux_WBwxHKSngbNEYe4TfJp2SLBCpgd1fIAYGqMKvuW5DvyX4znNIrN3SPFkDwJKHSSviejl7n18OBjBZGAmnWuLeNzZKl63CWSFUzw8fLglN5uYCA7-W450dpzTxZiVn5LTAl3QySCwp6Dg6qjnVl_6zMbdlr_LQBoldE14TuEAqdBoEh1D3lxs2YCvmc8OUWgSIJOp0UpZDmzsWjf29rpGZEgH7LImci_m78G6zFYS0fXKL6bNQGg9rAnkod7uu71vMRyEEpaOaNpRElK42eBMPfYdVyG8pfkc9Wng_FIi_QWpWfEqyI7M0YR9e9smgA0mqMTTIJKbUaCmXSlkJDDMA7FRPredAJewNa1RTS6YI32yCqQStb3Zsc5NuMtBRPKaxt8M-S1CU-c8",
     * "token_type": "Bearer",
     * "roles": [
     * {
     * "id": 2,
     * "name": "trainer",
     * "guard_name": "api",
     * "created_at": null,
     * "updated_at": null,
     * "pivot": {
     * "model_id": 3,
     * "role_id": 2,
     * "model_type": "App\\User"
     * }
     * }
     * ],
     * "trainer_details": null,
     * "teams": [
     * {
     * "id": 2,
     * "team_name": "team 2",
     * "image": null,
     * "description": null,
     * "created_at": null,
     * "updated_at": null,
     * "deleted_at": null,
     * "pivot": {
     * "user_id": 3,
     * "team_id": 2,
     * "created_at": null
     * }
     * }
     * ]
     * }
     * }
     *
     * @bodyParam email string required max 191 chars
     * @bodyParam password string required max 191 chars
     * @bodyParam device_type string required options:web
     * @bodyParam mac_id string required Mac Address
     *
     * @return JsonResponse
     */
    /*public function login(Request $request)
    {
        Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'device_type' => 'required|in:web',
            'ip' => 'required',
            'mac_id' => 'required',
        ])->validate();

        $request->password = bcrypt($request->password);
        $credentials = request(['email', 'password']);

        $user = User::with('package')->where('email', $request->email)
        ->first();

        if (!$user) {
            return Helper::apiNotFoundResponse(false, 'User not found!', new stdClass());
        }

        if (!$user->verified_at) {
            return Helper::apiNotFoundResponse(false, 'please verify your account', new stdClass());
        }

        if (!Auth::attempt($credentials)) {
            return Helper::apiNotFoundResponse(false, 'Invalid email or password', new stdClass());
        }

        $package = $user->package;
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        $user_device = UserDevice::where('ip', $request->ip)->first();

        if (!$user_device) {
            $user_device = new UserDevice();
        }

        $user_device->user_id = $user->id;
        $user_device->ip = $request->ip;
        $user_device->mac_id = $request->mac_id;
        $user_device->device_type = $request->device_type;
        $user_device->save();

        $token->save();


        $user->access_token = $tokenResult->accessToken;
        $user->token_type = 'Bearer';
        $user->roles;
        $user->trainer_details;
        $user->teams_trainers;
        $user->access_type = Helper::checkTeamUpgradation();
        $user->permissions = !empty($user->access_type) ? Helper::getPermissions($user->access_type) : [];

        return Helper::apiSuccessResponse(true, "LogIn successful!", $user);
    }*/

    /**
     * Auto Login
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "User logged-in successfully!",
     * "Result": {
     * "id": 3,
     * "nationality_id": null,
     * "first_name": "tr 2",
     * "middle_name": null,
     * "last_name": "rerum",
     * "surname": "quis",
     * "email": "shahzaib@jogo.ai",
     * "phone": null,
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": null,
     * "date_of_birth": null,
     * "verification_code": null,
     * "verified_at": "2020-07-21 13:32:40",
     * "active": 0,
     * "status_id": 2,
     * "created_at": "2020-07-21 13:30:47",
     * "updated_at": "2020-07-21 13:32:40",
     * "deleted_at": null,
     * "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZDhkYWViYTQ5MzUzZGE1NDc3ODI4ODYwY2ZkZDY3NDllMjgyNGUxYjA1ODBkNTQyNzNlNDFjZjg4YTkwZDQyN2I0ZjBkMjY3ZTkxMWIyOWQiLCJpYXQiOjE1OTUzMzg2OTcsIm5iZiI6MTU5NTMzODY5NywiZXhwIjoxNjI2ODc0Njk3LCJzdWIiOiIzIiwic2NvcGVzIjpbXX0.QZMueRek0OAEXLy1aYxNakY2Mxdo22Htm4vNcHEWFGAi4BznX5AUA7Gsr25-LTMANc2UBLyeCWGQhD4dNDx5mJ8va4df4k1Jd68Yj6MKIb3m9g4EBaJkcP9XRkar2NOx3edz0ARieanl3Gyp9ezy_vgnwvFOfd8j7NMUK_SpTHARMjhCJNAhoRfYNlO47-m4NpS6322FAbeouue8rQRUFPBsnfStf6Xvfv4EnHK92GkQu8KaXd91WX_ijgcFTZO1K4cyMQquXprMUcK7zzlHtiTOIKpg3ecP_AcvQBUohqV4RVx29LpOHA_VJsK_QkYv0bjcje6TijtPnZ5yiHD3jTCRZ-nLdNr-OSMhdavmapVkJ7fITXrbrJ5YNfcRiUmpYyvLN6WBfFhS9NvP6dtiT9mCS0eFlpXup725gIi23ovDs6Qvqe6oNom3LyLiV3otj4X6WHH78Oc2CO45GXSPn29dg8xjJ8nrZ64ec3YiqHvhya8ieuudlxaEsf8ZIjvmAJu7FNZfk2cAAZrVQFNZsfV5IZVY0Ds09rs5n1eNvcREYJdAhfi31qakQYYrGFsQOWI8YQq_zhJfkf0LLOUsg5biGrEolAQcEtetaV5GFugiDSLrR6PQH2YQx7fj5cZykkSf4ckMt2WtF3qDEqNmLPeQRCX32XHsjU4L5I2Tni4",
     * "token_type": "Bearer",
     * "roles": [
     * {
     * "id": 2,
     * "name": "trainer",
     * "guard_name": "api",
     * "created_at": null,
     * "updated_at": null,
     * "pivot": {
     * "model_id": 3,
     * "role_id": 2,
     * "model_type": "App\\User"
     * }
     * }
     * ],
     * "trainer_details": null,
     * "teams": [
     * {
     * "id": 2,
     * "team_name": "team 2",
     * "image": null,
     * "description": null,
     * "created_at": null,
     * "updated_at": null,
     * "deleted_at": null,
     * "pivot": {
     * "user_id": 3,
     * "team_id": 2,
     * "created_at": null
     * }
     * }
     * ]
     * }
     * }
     *
     * @bodyParam device_type string required web
     * @bodyParam onesignal_token string
     * @bodyParam mac_id string required
     *
     * @return JsonResponse
     */
    /*public function autoLogin(Request $request)
    {
        Validator::make($request->all(), [
            'device_type' => 'required|in:web',
            'ip' => 'required',
            'email' => 'required',
        ])->validate();

        $user = User::whereHas('user_devices', function ($q) use ($request) {

            $q->where('ip', $request->ip);

        })->whereHas('roles', function ($q) {
            $q->where('roles.name', 'trainer');
        })->where('email', $request->email)->orderBy('id', 'DESC')->first();

        if (!$user) {
            return Helper::apiNotFoundResponse(false, 'User not found', new stdClass());
        }

        if (!$user->verified_at) {
            return Helper::apiNotFoundResponse(false, 'please verify your account', new stdClass());
        }

        Auth::login($user);

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();

        $user_device = UserDevice::find($user->id);

        $user_device->onesignal_token = $request->onesignal_token ?? null;

        $user->access_token = $tokenResult->accessToken;
        $user->token_type = 'Bearer';
        $user->roles;
        $user->trainer_details;
        $user->teams;
        $user->access_type = Helper::checkTeamUpgradation();
        $user->permissions = !empty($user->access_type) ? Helper::getPermissions($user->access_type) : [];
        return Helper::apiSuccessResponse(true, "User logged-in successfully!", $user);
    }*/

    /**
     * Verify User
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "User verified successfully!",
     * "Result": {
     * "id": 3,
     * "nationality_id": null,
     * "first_name": "tr 2",
     * "middle_name": null,
     * "last_name": "rerum",
     * "surname": "quis",
     * "email": "shahzaib@jogo.ai",
     * "phone": null,
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": null,
     * "date_of_birth": null,
     * "verification_code": null,
     * "verified_at": "2020-07-21T13:32:40.344494Z",
     * "active": 0,
     * "status_id": 2,
     * "created_at": "2020-07-21 13:30:47",
     * "updated_at": "2020-07-21 13:32:40",
     * "deleted_at": null,
     * "user_id": 3,
     * "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiN2Y3ZmVmNzRiNzQyODVjNTc4MjNkMzZmM2EyYmQ3ZDVlYWJkZjQzNmM2NWIxYzQ1YTdkODQ4N2IzNmZjZDY4NWYzYzE5YWVjMWM5NGNmMTYiLCJpYXQiOjE1OTUzMzgzNjAsIm5iZiI6MTU5NTMzODM2MCwiZXhwIjoxNjI2ODc0MzYwLCJzdWIiOiIzIiwic2NvcGVzIjpbXX0.oK9JXAmiQ65W3mYtULqclt8crgVT4JuH24QDVpX1IIptjnAfVQ32pUpURp05tsdtzIy3KRN1ZojR0t_sahkMdJpNLH_XQlrRjY9M9MiEx18i8gmc0XaT7RAkmKXLaWgsQWuagDqyC1cQJzKdD_DJHnUP1UseD5IidrURIb1WNYik9wEceK4NIK80KqldnsUKo6z3xiX5T980PfyRiwR839YxJkMJo_WN5eMxT3mEN2Ok-igeA-q9f-JANBESECbDCul71isLEdYlhwt96WDT-JCsyU59OzD6TTfzNZzZW5pRRe-E8hUZeIOXB9ZiUZL7TzVhZyffdC1EDKlBN-2EQBYd3R8xb7oFDZqJ0NhsNrdRyGRdgJeQJz3WF3PS14jmb6gcz3ERHHf0g1eeoNQMb7yZs2RlbR3aYLpgNhK3XJ6beD1uPGjRjAo8znmPxS_NxpJrRPFDFIk0Nh_scriNs4rHsOheWIPCACQ50sCzna64T3wiyqsFmTp9a2AkP7AGCw_NukQcotK5TcfaSLe91gD9-CuRWH_DwwtNAx2VMC-M2-YoUwMqYpDAk3WgPPg3Y9-wegBjAHqGMYvwg2xyuW9RZQ1H-P2cF0kik3_E3-9R8oOI4k9Cql1wptPqdNfBvHYr7KE2MLVQ0VrfUPG1oO-5dFIKW6vsxdtTV-_j9-E",
     * "token_type": "Bearer",
     * "roles": [
     * {
     * "id": 2,
     * "name": "trainer",
     * "guard_name": "api",
     * "created_at": null,
     * "updated_at": null,
     * "pivot": {
     * "model_id": 3,
     * "role_id": 2,
     * "model_type": "App\\User"
     * }
     * }
     * ],
     * "trainer_details": null,
     * "teams": []
     * }
     * }
     *
     * @bodyParam email string required max 191 chars
     * @bodyParam verification_code string required
     * @bodyParam device_type string required options: web
     * @bodyParam mac_id string required Mac Address
     *
     * @return JsonResponse
     */
    /*public function verifyUser(Request $request)
    {
        Validator::make($request->all(), [
            'email' => 'required|exists:users',
            'verification_code' => 'required',
            'device_type' => 'required|in:web',
            'mac_id' => 'required'
        ])->validate();

        $response = (new User())->verifyUser($request,'trainer');

        if (!$response['status']) {
            return Helper::apiNotFoundResponse(false, $response['msg'], new stdClass());
        }

        $user = $response['user'];
        Auth::login($user);

        $user_device = UserDevice::where('mac_id', $request->mac_id)->first();

        if (!$user_device) {
            $user_device = new UserDevice();
        }
        $user->user_id = $user->id;
        $user_device->ip = $_SERVER['REMOTE_ADDR'];
        $user_device->mac_id = $request->mac_id;
        $user_device->device_type = $request->device_type;
        $user_device->save();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();

        $user->access_token = $tokenResult->accessToken;
        $user->token_type = 'Bearer';
        $user->roles;
        $user->trainer_details;
        $user->teams;
        $user->access_type = Helper::checkTeamUpgradation();
        $user->permissions = !empty($user->access_type) ? Helper::getPermissions($user->access_type) : [];

        return Helper::apiSuccessResponse(true, "User verified successfully!", $user);
    }*/

    /**
     *
     * Logout
     *
     * To access this route you need to give Bearer Token in header which you received in autologin or verify user api's
     *
     * @urlParam Authorization required Required Bearer token in header
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "You have been successfully logged out!",
     * "Result": {}
     * }
     *
     *
     *
     * @return JsonResponse
     */
    /*public function logout(Request $request)
    {
        Validator::make($request->all(), [
            'mac_id' => 'required'
        ])->validate();

        $user_device = UserDevice::where('mac_id', $request->mac_id)->where('user_id', Auth::user()->id)->first();

        if (!$user_device) {
            return Helper::apiNotFoundResponse(false, 'User not found with this mac id', new stdClass());
        }

        $user_device->delete();
        $token = $request->user()->token();
        $token->revoke();


        return Helper::apiSuccessResponse(true, "You have been successfully logged out!", new stdClass());
    }*/

    /**
     * Send / Resend Code
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Code has been sent to your email",
     * "Result": {}
     * }
     *
     * @bodyParam email string required
     * @bodyParam type string required To reset pwd use reset_pwd, for code resend use resend_code
     * @return JsonResponse
     */
    public function sendCode(Request $request)
    {
        Validator::make($request->all(), [
            'email' => 'required',
            'type' => 'required|in:reset_pwd,resend_code'
        ])->validate();

        $response = (new User())->send_code($request,'trainer');
        if($response['status']){
            return Helper::apiSuccessResponse(true, $response['msg'], new stdClass());
        }else{
            Helper::apiNotFoundResponse(false, $response['msg'], new stdClass());
        }
    }

    /**
     * Verify Code
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Code verified",
     * "Result": {}
     * }
     *
     * @bodyParam email string required
     * @bodyParam code string required
     * @return JsonResponse
     */
    /*public function verifyCode(Request $request)
    {
        Validator::make($request->all(), [
            'email' => 'required',
            'code' => 'required'
        ])->validate();

        $user = User::where('email', $request->email)->where('verification_code', $request->code)->whereHas('roles', function ($q) {
            $q->where('roles.name', 'trainer');
        })->first();

        if (!$user) {
            return Helper::apiNotFoundResponse(false, 'Invalid email or code', new stdClass());
        }

        return Helper::apiSuccessResponse(true, "Code verified", new stdClass());
    }*/


    /**
     * Reset Password
     *
     * @bodyParam email string required
     * @bodyParam password string required
     * @bodyParam code string required
     *
     * @return JsonResponse
     */
    /*public function resetPassword(Request $request)
    {
        Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'code' => 'required'
        ])->validate();

        $user = User::where('email', $request->email)->where('verification_code', $request->code)->whereHas('roles', function ($q) {
            $q->where('roles.name', 'trainer');
        })->first();

        if (!$user) {
            return Helper::apiNotFoundResponse(false, 'Invalid email or code', new stdClass());
        }

        $user->password = bcrypt($request->password);
        $user->verification_code = null;
        $user->save();

        return Helper::apiSuccessResponse(true, "Password reset successfully", new stdClass());
    }*/
}