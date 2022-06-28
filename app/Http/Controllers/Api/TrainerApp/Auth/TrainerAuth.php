<?php

namespace App\Http\Controllers\Api\TrainerApp\Auth;

use App\Club;
use App\Contact;
use App\Country;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\App\GetTeamPlayerResourceListing;
use App\Http\Requests\Api\Dashboard\Auth\VerifyForgetPasswordOtpRequest;
use App\Http\Requests\Api\Dashboard\Auth\ForgetPasswordRequest;
use App\Http\Requests\Api\Dashboard\Auth\UpdatePasswordRequest;
use App\PlayerTeam;
use App\SelectedClub;
use App\Team;
use App\User;
use App\UserCoupon;
use function Aws\filter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\UserDevice;
use Illuminate\Http\JsonResponse;
use stdClass;
use Mail;

/**
 * @group Trainer Auth
 *
 * API For Trainer App Auth
 */
class TrainerAuth extends Controller
{

    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Login
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "LogIn successful!",
     * "Result": {
     * "id": 461,
     * "nationality_id": null,
     * "first_name": null,
     * "middle_name": "''",
     * "last_name": null,
     * "surname": null,
     * "email": "m.f@gmail.com",
     * "humanox_username": null,
     * "humanox_user_id": null,
     * "humanox_pin": null,
     * "humanox_auth_token": null,
     * "phone": null,
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": null,
     * "date_of_birth": null,
     * "age": null,
     * "badge_count": 0,
     * "verification_code": "229915",
     * "verified_at": null,
     * "active": 0,
     * "status_id": 2,
     * "who_created": null,
     * "last_seen": null,
     * "online_status": null,
     * "created_at": "2021-05-03 17:18:43",
     * "updated_at": "2021-05-03 17:18:43",
     * "deleted_at": null,
     * "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxNSIsImp0aSI6IjRlNGVjNGQ2OTEzYzBlMDg1ZTU3ZGE2ZDVkZGM1ZTAzZDJmYzQ1OTg4YzU5YzIzOTQxMDY4MWJhMjA4MmQzOWRhMGU0NGNjNTZlOGYwOTQzIiwiaWF0IjoxNjIwMTA1ODExLCJuYmYiOjE2MjAxMDU4MTEsImV4cCI6MTY1MTY0MTgxMSwic3ViIjoiNDYxIiwic2NvcGVzIjpbXX0.PZdtOS36N7LY02jZ-9G_xYqjvZ1-yrMLTHbZKipD1ZOSGaS63ezgEGCRrdtTWQtRWJ1MjM_I4p-xcIBJHD06VgfGxCePRu78mEU7dUOhcPQWPK44F0JT11K2jx7F6_hwHJC28h_s673v52bNza9VLlAdYaz1x7Hhri5J9dm9fzxuBkzxQoj-DbMXnOYcKD7WuXg87wDnKUUmJF874nQBFNb--P4d--iKE2yV9b2neoSOvgY9UqC6_CRyjIetyOR4VeBSbKQf5elioQXVmpMEFVxE2ZGKzta-BQSFUHP5-bOy7IqXyWZn2HUJKDWJQKg4sAOjsrbep53zpjawbdhpJTPJngCGNkMwkjkdrq4dCxosXakzBKKfPYqFvLCTV_TJ2ctaryCPECCK-E5xAErx92YQ_M4BQmi3tSuEr3RDyxH14nDj6MVnkBdtjNERuqiItx2_uBFtZOmW0T6YiZPminFQjBiuk9K-93A8f-Eel8D16sgdfvJFUtKaK_dArLZDgZJqxkXo98QTafZXPj72N96ayEdvZI6TgBEZI5YMcfE1a0LhfB3OqO1WIswf2lvivmQ6D5g61CSkJqnaG_MJ_3BWfPQ7-wH8u_a8GKDzeDFqtHs_PBPNHqvC47lJ7YXIsKu-dnl-MhjwXS_qFpQw08fMci1OxjKW5XfCGWwsRHk",
     * "token_type": "Bearer",
     * "access_type": "freemium",
     * "permissions": [
     * "skill-assignment",
     * "exercises",
     * "player-database",
     * "settings"
     * ],
     * "roles": [
     * {
     * "id": 2,
     * "name": "trainer",
     * "guard_name": "api",
     * "created_at": null,
     * "updated_at": null,
     * "pivot": {
     * "model_id": 461,
     * "role_id": 2,
     * "model_type": "App\\User"
     * }
     * },
     * {
     * "id": 5,
     * "name": "freemium",
     * "guard_name": "api",
     * "created_at": "2021-04-01 10:39:26",
     * "updated_at": "2021-04-01 10:51:47",
     * "pivot": {
     * "model_id": 461,
     * "role_id": 5,
     * "model_type": "App\\User"
     * }
     * }
     * ],
     * "trainer_details": null,
     * "teams_trainers": []
     * }
     * }
     *
     * @bodyParam email string required
     * @bodyParam password string required
     * @bodyParam device_type string required options: ios, android
     * @bodyParam imei string required Required if device type is android
     * @bodyParam udid string required Required if device type is ios
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function login(Request $request)
    {

        Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'device_type' => 'required|in:ios,android',
            'imei' => 'required_if:device_type,android',
            'udid' => 'required_if:device_type,ios',
        ])->validate();

        $request->password = bcrypt($request->password);
        $credentials = request(['email', 'password']);


        $user = User::where('email', $request->email)->with("roles")->first();

//        $user = User::where('email', $request->email)->whereHas('roles', function ($q) {
//            $q->where('roles.name', 'trainer');
//        })->first();

        // IF THERE IS NO USER WITH THE EMAIL AND PASSWORD RETURN FALSE
        if (!$user) {
            return Helper::apiNotFoundResponse(false, "User not found", new stdClass());
        }

        // IF THE INPUTED CREDIENTALS ARE INCORRECT
        if (!Auth::attempt($credentials)) {
            return Helper::apiNotFoundResponse(false, 'Invalid email or password', new stdClass());
        }

        Helper::saveToken($request,$user->id);

        $package = $user->package;
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();

        for ($i = 0; $i < count($user->roles); $i++) {
            if ($user->roles[$i]->name == "trainer") {
                $user->role = "Trainer";
                break;
            } elseif ($user->roles[$i]->name == "player") {
                $user->role = "Player";
                break;
            }
        }

        $get_club_id = SelectedClub::select("club_id")->where("trainer_user_id", auth()->user()->id)->first();

        $user->access_token = $tokenResult->accessToken;
        $user->token_type = 'Bearer';
        $user->roles;
        $user->selected_club_id = $get_club_id != null ? $get_club_id->club_id : null;
        $user->trainer_details;
        $user->teams_trainers;
        $user->access_type = $package->plan->role->name ?? "";
        $user->permissions = !empty($user->access_type) ? Helper::getPermissions($user->access_type) : [];
        $color_code = [
            "primary" => "#dbff00",
            "secondary" => "#aa37ff"
        ];
        $user->color_codes = $color_code;
        return Helper::apiSuccessResponse(true, "LogIn successful!", $user);
    }

    /**
     * Verify User
     *
     * @response
     *
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "User verified successfully!",
     * "Result": {
     * "id": 461,
     * "nationality_id": null,
     * "first_name": null,
     * "middle_name": "''",
     * "last_name": null,
     * "surname": null,
     * "email": "m.fahad1161681@gmail.com",
     * "humanox_username": null,
     * "humanox_user_id": null,
     * "humanox_pin": null,
     * "humanox_auth_token": null,
     * "phone": "+923332154785",
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": null,
     * "date_of_birth": null,
     * "age": null,
     * "badge_count": 0,
     * "verification_code": null,
     * "verified_at": "2021-05-24T10:33:20.664402Z",
     * "active": 0,
     * "status_id": 2,
     * "who_created": null,
     * "last_seen": "2021-05-24 10:33:20",
     * "online_status": "1",
     * "created_at": "2021-05-03 17:18:43",
     * "updated_at": "2021-05-24 10:33:20",
     * "deleted_at": null,
     * "user_id": 461,
     * "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxNSIsImp0aSI6IjBiNGIzNDg4ZjhjNTZiZDI4NTdjMmY0ZTM1ZTViOWY3NTAxZmQwZTMzZGQxMjcxYzdhMjFjMjNkMWQ3YTBmODIwYTk5NDE4MDg5ZmE3NGM3IiwiaWF0IjoxNjIxODUyNDAwLCJuYmYiOjE2MjE4NTI0MDAsImV4cCI6MTY1MzM4ODQwMCwic3ViIjoiNDYxIiwic2NvcGVzIjpbXX0.iPresxllTwqdIwn1LmcZYmBs093RfKg0cIpttVcJUJexfU8SlPYVGYjkkNhv3Ss_OQaeJNiAenCg0VmfpIMLEkaR9Nzcpr8dRWKnPBvS5QeSWfklsPRcPTKfJivTSRr5_brL5JTSjaNtLgTh6u90tP3OmZxQagETgFZJznf8pqCbZgliiJLuLFL4Lu60xgs40NiLhMKTQ_LI9fq3eLg0zd8VjLkeQCcCX44yzdophBFPlQoM6n20kx-AI2YwOY2ESuEAlTXRwTxA01jPYRm1Q7xbLa2shEWyym2J0r7g98-sbI50LntouAUR8RDPp8xBW4OEjMXYl5rcccQ8wBLlhb50IRDQi5AcuA1JyItz_uFL0X1c95YWUQliP5uHfC8QUScA2J_njHlSK6keQ8ZCmFbp_r0sH3yyfbQ_umpphO7jIsx5iGKhQltFWwtw8rLimyhW5cgDMEydOrNsCh9dbAYJ8Fw6YgIrpbP8ryWNmko9Gnzbz9kNZu1o9i6tPYatGbVPY-r8B4Wn1w4Sd6Wk6JpODxe52Bp2kSamPBBbb7v9jcVg-33wSe8Hm6gnPPtE8lM5sfpfHter871Fv3QMyIn-0UwovIROPsvzk_l0fupzy7zKMzkqUJ-r2siR_ctorNSp-b_DFIpAuBKq2TB1qXaOePPqNv8gzbuTEZloMtA",
     * "token_type": "Bearer",
     * "access_type": "freemium",
     * "permissions": [
     * "skill-assignment",
     * "exercises",
     * "player-database",
     * "settings"
     * ],
     * "roles": [
     * {
     * "id": 2,
     * "name": "trainer",
     * "guard_name": "api",
     * "created_at": null,
     * "updated_at": null,
     * "pivot": {
     * "model_id": 461,
     * "role_id": 2,
     * "model_type": "App\\User"
     * }
     * },
     * {
     * "id": 5,
     * "name": "freemium",
     * "guard_name": "api",
     * "created_at": "2021-04-01 10:39:26",
     * "updated_at": "2021-04-01 10:51:47",
     * "pivot": {
     * "model_id": 461,
     * "role_id": 5,
     * "model_type": "App\\User"
     * }
     * }
     * ],
     * "trainer_details": null,
     * "teams": []
     * }
     * }
     *
     */

    public function verifyUser(Request $request)
    {
        Validator::make($request->all(), [
            'email' => 'required|exists:users',
            'verification_code' => 'required',
            'device_type' => 'required|in:ios,android',
            'imei' => 'required_if:device_type,android',
            'udid' => 'required_if:device_type,ios',
        ])->validate();

        $response = (new User())->verifyUser($request,'trainer');

        if (!$response['status']) {
            return Helper::apiNotFoundResponse(false, $response['msg'], new stdClass());
        }

        $user = $response['user'];
        Auth::login($user);

        if ($request->device_type == "ios") {
            $user_device = UserDevice::where('imei', $request->imei)->first();

        } else if ($request->device_type == "android") {
            $user_device = UserDevice::where('udid', $request->udid)->first();
        } else {
            return Helper::apiNotFoundResponse(false, 'Invalid device type', new stdClass());
        }

        if (!$user_device) {
            $user_device = new UserDevice();
        }

        $user->user_id = $user->id;
        $user_device->ip = $_SERVER['REMOTE_ADDR'];

        if ($request->device_type == "ios") {
            $user_device->udid = $request->udid;
        }
        $user_device->imei = $request->imei;

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
        $user->access_type = $user->package->plan->role->name;
        $user->permissions = !empty($user->access_type) ? Helper::getPermissions($user->access_type) : [];;

        return Helper::apiSuccessResponse(true, "User verified successfully!", $user);
    }

    /**
     * Reset Password
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Password reset successfully",
     * "Result": {}
     * }
     *
     * @bodyParam email string required
     * @bodyParam password string required
     * @bodyParam code string required
     *
     * @return JsonResponse
     */
    public function reset_password(Request $request)
    {
        Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'code' => 'required'
        ])->validate();

        $user = $this->userModel->getUserWithEmailAndCode($request);

        if (!$user) {
            return Helper::apiNotFoundResponse(false, 'Invalid email or code', new stdClass());
        }

        $user->password = bcrypt($request->password);
        $user->verification_code = null;
        $user->save();

        return Helper::apiSuccessResponse(true, "Password reset successfully", new stdClass());
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
    public function verify_code(Request $request)
    {
        Validator::make($request->all(), [
            'email' => 'required',
            'code' => 'required'
        ])->validate();

        $user = $this->userModel->getUserWithEmailAndCode($request);

        if (!$user) {
            return Helper::apiNotFoundResponse(false, 'Invalid email or code', new stdClass());
        }

        return Helper::apiSuccessResponse(true, "Code verified", new stdClass());
    }


    /**
     * Send or Resend Code
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
    public function send_code(Request $request)
    {
        Validator::make($request->all(), [
            'email' => 'required',
            'type' => 'required|in:reset_pwd,resend_code'
        ])->validate();

        $response = $this->userModel->send_code($request, 'trainer');
        if ($response['status']) {
            return Helper::apiSuccessResponse(true, $response['msg'], new stdClass());
        } else {
            Helper::apiNotFoundResponse(false, $response['msg'], new stdClass());
        }
    }


    /**
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
     * @bodyParam device_identifier string required it can be imei or udid
     *
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        Validator::make($request->all(), [
            'device_identifier' => 'required',
        ])->validate();

        return $this->userModel->logoutUser($request);
    }

    /**
     * Get Trainer Profile
     *
     * @queryParam  trainer_id required
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": {
     * "id": 40,
     * "nationality_id": 164,
     * "first_name": "Umer",
     * "middle_name": null,
     * "last_name": "Shaikh",
     * "surname": "Shaikh",
     * "email": "umer@jogo.ai",
     * "new_temp_email": null,
     * "humanox_username": null,
     * "humanox_user_id": null,
     * "humanox_pin": null,
     * "humanox_auth_token": null,
     * "country_code_id": 152,
     * "phone": "+934342336633",
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": "media/users/OF4YBPrj3vkx7QtIztDKOAZod45t1sSsXQfc1cPE.jpeg",
     * "date_of_birth": null,
     * "age": null,
     * "badge_count": 0,
     * "verification_code": null,
     * "verified_at": "2021-08-13 13:15:08",
     * "active": 0,
     * "status_id": 1,
     * "who_created": 40,
     * "last_seen": "2021-09-08 09:48:46",
     * "online_status": "1",
     * "created_at": "2020-07-30 21:26:43",
     * "updated_at": "2021-09-08 09:48:46",
     * "deleted_at": null,
     * "country_code": null,
     * "selected_club_id": null,
     * "total_team_requests": 1,
     * "trainer": {
     * "id": 1,
     * "user_id": 40,
     * "country": "1",
     * "jersey_number": null,
     * "created_at": null,
     * "updated_at": null,
     * "deleted_at": null
     * },
     * "teams_trainers": [
     * {
     * "id": 6,
     * "team_name": "Test",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "mixed",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": "23",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2020-12-14 15:02:44",
     * "updated_at": "2021-01-11 16:06:20",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 6,
     * "created_at": "2021-01-11 13:56:19"
     * }
     * },
     * {
     * "id": 33,
     * "team_name": "T2",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": "10",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-01-14 10:34:37",
     * "updated_at": "2021-01-14 10:34:37",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 33,
     * "created_at": "2021-01-14 10:34:37"
     * }
     * },
     * {
     * "id": 34,
     * "team_name": "T2",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": "10",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-01-14 13:00:32",
     * "updated_at": "2021-01-14 13:00:32",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 34,
     * "created_at": "2021-01-14 13:00:32"
     * }
     * },
     * {
     * "id": 51,
     * "team_name": "yyyy",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "11",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-03-04 17:48:30",
     * "updated_at": "2021-03-04 17:48:30",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 51,
     * "created_at": "2021-03-04 17:48:30"
     * }
     * },
     * {
     * "id": 52,
     * "team_name": "yyyyp",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "11",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-03-04 17:49:48",
     * "updated_at": "2021-03-04 17:49:48",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 52,
     * "created_at": "2021-03-04 17:49:48"
     * }
     * },
     * {
     * "id": 55,
     * "team_name": "Team",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "11",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-03-16 09:01:01",
     * "updated_at": "2021-03-16 09:01:01",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 55,
     * "created_at": "2021-03-16 09:01:01"
     * }
     * },
     * {
     * "id": 66,
     * "team_name": "s",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "10",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-04-19 11:27:01",
     * "updated_at": "2021-04-19 11:27:01",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 66,
     * "created_at": "2021-04-19 11:27:01"
     * }
     * },
     * {
     * "id": 7,
     * "team_name": "Argentina",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": "U16",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2020-12-14 15:03:34",
     * "updated_at": "2020-12-17 10:36:51",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 7,
     * "created_at": null
     * }
     * },
     * {
     * "id": 69,
     * "team_name": "team3",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 9,
     * "max_age_group": 12,
     * "created_at": "2021-04-21 11:51:09",
     * "updated_at": "2021-04-21 11:51:09",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 69,
     * "created_at": "2021-04-21 11:51:09"
     * }
     * },
     * {
     * "id": 76,
     * "team_name": "wew",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 11,
     * "max_age_group": 12,
     * "created_at": "2021-05-05 10:15:22",
     * "updated_at": "2021-05-05 10:15:22",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 76,
     * "created_at": "2021-05-05 10:15:22"
     * }
     * },
     * {
     * "id": 78,
     * "team_name": "newTeams",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "woman",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 7,
     * "max_age_group": 13,
     * "created_at": "2021-06-22 10:36:55",
     * "updated_at": "2021-06-22 10:36:55",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 78,
     * "created_at": "2021-06-22 10:36:55"
     * }
     * },
     * {
     * "id": 79,
     * "team_name": "teamchat",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "woman",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 10,
     * "max_age_group": 13,
     * "created_at": "2021-06-22 10:38:02",
     * "updated_at": "2021-06-22 10:38:02",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 79,
     * "created_at": "2021-06-22 10:38:02"
     * }
     * },
     * {
     * "id": 80,
     * "team_name": "my team",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "woman",
     * "team_type": "outdoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 12,
     * "max_age_group": 14,
     * "created_at": "2021-06-22 12:49:23",
     * "updated_at": "2021-06-22 12:49:23",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 80,
     * "created_at": "2021-06-22 12:49:23"
     * }
     * },
     * {
     * "id": 81,
     * "team_name": "sdfs",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 7,
     * "max_age_group": 11,
     * "created_at": "2021-06-22 12:54:54",
     * "updated_at": "2021-06-22 12:54:54",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 81,
     * "created_at": "2021-06-22 12:54:55"
     * }
     * },
     * {
     * "id": 82,
     * "team_name": "New TEST UPDATED",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 5,
     * "max_age_group": 10,
     * "created_at": "2021-06-22 12:56:03",
     * "updated_at": "2021-06-30 07:48:29",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 82,
     * "created_at": "2021-06-22 12:56:03"
     * }
     * },
     * {
     * "id": 14,
     * "team_name": "Updated Team 14 team AGAIN",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "woman",
     * "team_type": "field",
     * "description": null,
     * "age_group": "19",
     * "min_age_group": 0,
     * "max_age_group": 0,
     * "created_at": "2020-12-19 13:56:55",
     * "updated_at": "2021-08-17 09:26:07",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 14,
     * "created_at": "2021-08-17 09:26:07"
     * }
     * },
     * {
     * "id": 89,
     * "team_name": "New Team",
     * "privacy": "open_to_invites",
     * "image": null,
     * "gender": "woman",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 0,
     * "max_age_group": 0,
     * "created_at": "2021-08-17 09:28:13",
     * "updated_at": "2021-08-17 09:28:13",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 89,
     * "created_at": "2021-08-17 09:28:13"
     * }
     * },
     * {
     * "id": 90,
     * "team_name": "New Team",
     * "privacy": "open_to_invites",
     * "image": null,
     * "gender": "woman",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": null,
     * "max_age_group": null,
     * "created_at": "2021-08-17 09:44:07",
     * "updated_at": "2021-08-17 09:44:07",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 90,
     * "created_at": "2021-08-17 09:44:07"
     * }
     * }
     * ],
     * "nationality": {
     * "id": 164,
     * "name": "Pakistan",
     * "iso": "PK",
     * "phone_code": 92,
     * "flag": "https://flagcdn.com/w160/media/countries/pk.png.png",
     * "created_at": null,
     * "updated_at": null,
     * "deleted_at": null
     * }
     * }
     * }
     * @queryParam club_id integer
     */
    public function get_trainer_profile(Request $request)
    {
        Validator::make($request->all(), [
            'trainer_id' => 'required|integer',
            'club_id' => 'integer'
        ])->validate();

        $clubTeams = function ($clubteam) use ($request) {
            $clubteam->select("team_id")->where("club_teams.club_id", $request->club_id);
        };

        $trainer = User::whereId($request->trainer_id ?? auth()->user()->id)->
        with(['trainer', 'teams_trainers', 'nationality'])
            ->with(["clubs_teams" => $clubTeams])->first();

        $trainer_teams = $trainer->clubs_teams->pluck("team_id");
        unset($trainer->clubs_teams);
        $total_club_team_requests = DB::table('player_team_requests')->whereIn('team_id', $trainer_teams)->count('team_id');
        $trainer_selected = SelectedClub::select("club_id")
            ->where("trainer_user_id", $request->trainer_id ?? auth()->user()->id)
            ->first();
        $trainer->selected_club_id = $trainer_selected->club_id ?? null;
        $trainer->total_team_requests = intval($total_club_team_requests) ?? 0;

        //$trainer = User::role('trainer')->with(['teams_trainers',"nationality"])->select('id', 'first_name', 'last_name', 'profile_picture')->find($request->trainer_id);
        if ($trainer) {
            return Helper::apiSuccessResponse(true, 'Success', $trainer);
        }
        return Helper::apiErrorResponse(false, 'No records found', new stdClass());
    }

    /**
     * GetTeamPlayers
     *
     *
     * @queryParam  team_id required integer
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "players found",
     * "Result": {
     * "data": {
     * "id": 2,
     * "team_name": "Ajax U16",
     * "privacy": "open_to_invites",
     * "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     * "gender": "man",
     * "team_type": "field",
     * "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
     * "age_group": null,
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2020-07-17 16:17:06",
     * "updated_at": "2020-07-17 16:17:06",
     * "deleted_at": null,
     * "players": [
     * {
     * "id": 128,
     * "first_name": "baran",
     * "last_name": "erdogan",
     * "profile_picture": "media/users/5f99cb1c40f871603914524.jpeg"
     * },
     * {
     * "id": 586,
     * "first_name": "Player By F",
     * "last_name": "MF",
     * "profile_picture": ""
     * }
     * ],
     * "trainers": []
     * },
     * "meta": {
     * "current_page": 1,
     * "next_page": 2
     * }
     * }
     * }
     *
     * @response 401 {
     *    "Response": false,
     *    "StatusCode": 401,
     *    "Message": "apiInvalidParamResponse",
     *    "Result": [
     *        {
     *            "team_id": [
     *                "The team_id field is required."
     *            ]
     *        }
     *    ]
     * }
     *
     * @response 401 {
     *     "Response": false,
     *     "StatusCode": 401,
     *     "Message": "Unauthenticated user to access this route",
     *     "Result": {}
     *  }
     * @queryParam limit required integer
     * @queryParam page required integer for page number
     */

    public function getTeamPlayers(Request $request)
    {
        $request->validate([
            "limit" => ["required", "min:1", "integer"],
            "team_id" => ["required", "integer"]
        ]);
        $team = Team::has("players")->with("trainers")->find($request->team_id);
        if (!$team) {
            return Helper::apiUnAuthenticatedResponse(false, 'Players not found', new stdClass());
        }
        $data = Helper::getTeamPlayers($team,$request,false);
        return Helper::apiSuccessResponse(true, 'players found', $data);
    }

    /**
     * Get Trainer Team
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Teams found",
     * "Result": [
     * {
     * "id": 6,
     * "team_name": "Test",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "mixed",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": "23",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2020-12-14 15:02:44",
     * "updated_at": "2021-01-11 16:06:20",
     * "deleted_at": null,
     * "players_count": 9,
     * "requests_count": 1
     * },
     * {
     * "id": 36,
     * "team_name": "Street 12",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "man",
     * "team_type": "indoor",
     * "description": null,
     * "age_group": "12",
     * "min_age_group": 13,
     * "max_age_group": 13,
     * "created_at": "2021-01-15 15:55:26",
     * "updated_at": "2021-01-15 15:55:26",
     * "deleted_at": null,
     * "players_count": 21,
     * "requests_count": 0
     * },
     * {
     * "id": 14,
     * "team_name": "Updated Team 14 team AGAIN",
     * "privacy": "open_to_invites",
     * "image": "",
     * "gender": "woman",
     * "team_type": "field",
     * "description": null,
     * "age_group": "19",
     * "min_age_group": 0,
     * "max_age_group": 0,
     * "created_at": "2020-12-19 13:56:55",
     * "updated_at": "2021-08-17 09:26:07",
     * "deleted_at": null,
     * "players_count": 2,
     * "requests_count": 0
     * },
     * {
     * "id": 89,
     * "team_name": "New Team",
     * "privacy": "open_to_invites",
     * "image": null,
     * "gender": "woman",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": 0,
     * "max_age_group": 0,
     * "created_at": "2021-08-17 09:28:13",
     * "updated_at": "2021-08-17 09:28:13",
     * "deleted_at": null,
     * "players_count": 0,
     * "requests_count": 0
     * },
     * {
     * "id": 90,
     * "team_name": "New Team",
     * "privacy": "open_to_invites",
     * "image": null,
     * "gender": "woman",
     * "team_type": "field",
     * "description": null,
     * "age_group": null,
     * "min_age_group": null,
     * "max_age_group": null,
     * "created_at": "2021-08-17 09:44:07",
     * "updated_at": "2021-08-17 09:44:07",
     * "deleted_at": null,
     * "players_count": 0,
     * "requests_count": 0
     * }
     * ]
     * }
     *
     * @queryParam trainer_id required integer
     * @queryParam club_id required integer
     *
     * @return JsonResponse
     */
    public function trainer_teams(Request $request)
    {
        Validator::make($request->all(), [
            'trainer_id' => 'required',
            'club_id' => 'required|integer'
        ])->validate();

        $myClubs = (new Club())->myCLubs($request);

        if (!in_array($request->club_id, array_column($myClubs->original['Result'], 'id'))) {
            return Helper::apiErrorResponse(false, 'Trainer Not In Club', new \stdClass());
        }

        $teams = Team::whereHas('clubs', function ($q) use ($request) {
            $q->where('club_id', $request->club_id);
        })->withCount('players')->withCount("requests")->get();
        if ($teams->count()) {
            return Helper::apiSuccessResponse(true, 'Teams found', $teams);
        }
        return Helper::apiSuccessResponse(false, 'Teams Not found', []);
    }

    public function forgetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|min:8|max:254|exists:users,email,deleted_at,NULL'
        ]);

        return $this->userModel->forgetPassword($request);
    }

    protected function verifyForgetPasswordOtp(Request $request)
    {
        $response = $this->userModel->verifyOtp($request);
        return $response;
    }

    protected function updatePassword(Request $request)
    {
        Validator::make($request->all(), [
            'email' => 'required|email|min:8|max:254|exists:users,email,deleted_at,NULL',
            'newPassword' => 'required|string|min:8|max:55',
            'confirmPassword' => 'required|string|min:8|max:55|same:newPassword',
            'otp' => 'required|numeric|digits:6|exists:users,verification_code,email,' . $this->email
        ])->validate();

        return $this->userModel->updatePassword($request);

    }

}