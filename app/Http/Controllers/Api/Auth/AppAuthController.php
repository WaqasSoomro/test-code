<?php

namespace App\Http\Controllers\Api\Auth;

use App\Country;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Status;
use App\User;
use App\SelectedClub;
use App\UserDevice;
use App\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPUnit\TextUI\Help;
use stdClass;

/**
 * @group App Auth
 *
 * APIs for app authentication.
 */
class AppAuthController extends Controller
{

    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    private function getReadMessages($user,$user_groups){
        return DB::table('chat_read_messages')->where('user_id', $user->id)->whereIn('group_id', $user_groups)->selectRaw("COUNT(*) as read_messages")->first();
    }

    private function getTotalMessages($user_groups){
        return DB::table('chat_group_messages')
            ->selectRaw("COUNT(chat_group_messages.id) as total_messages")
            ->join('chat_group_members', 'chat_group_messages.group_id', '=', 'chat_group_members.group_id')
            ->whereIn("chat_group_members.group_id", $user_groups)->first();
    }

    private function getGroupIdArray($user){
        return DB::table("chat_group_members")->where('user_id', $user->id)->pluck('group_id')->toArray();
    }

    private function SendOTP($request){
        if (substr($request->phone, 0, 3) != '+86') {
            $response = Helper::sendOtp("+" . $request->country_code . $request->phone);

            if (gettype($response) == 'string') {
                activity()->performedOn(new User())->log($response);
                return Helper::apiErrorResponse(false, $response, new stdClass(), 400);
            }
        }
        return 1;
    }

    /**
     * CheckUpdates
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": {"update": 1.0, "force_update": 1}
     * }
     *
     * @bodyParam current_version string required Current Version
     * @bodyParam device_type string required Device Type (android, ios)
     *
     * @return JsonResponse
     */
    public function checkUpdates(Request $request)
    {
        Validator::make($request->all(), [
            'current_version' => 'required',
            'device_type' => 'required|in:ios,android'
        ])->validate();

        $update_version_ios = Helper::settings('version', 'IOS');
        $update_version_android = Helper::settings('version', 'ANDROID');
        if ($request->device_type == 'android') {
            $data = Helper::checkUpdate($update_version_android, $request->current_version);
        } elseif ($request->device_type == 'ios') {
            $data = Helper::checkUpdate($update_version_ios, $request->current_version);
        } else {
            $data = [
                'update' => 0,
                'force_update' => 0
            ];
        }

        return Helper::apiSuccessResponse(true, "Success", $data);
    }

    /**
     * Login
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "A One-Time password has been sent to your phone",
     * "Result": {}
     * }
     *
     * @bodyParam country_code string required
     * @bodyParam phone string required
     * @bodyParam device_type string required options: ios, android
     * @bodyParam imei string required Required if device type is android
     * @bodyParam udid string required Required if device type is ios
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {

        Validator::make($request->all(), [
            'country_code' => 'required|exists:countries,phone_code',
            'phone' => 'required',
            'device_type' => 'required|in:ios,android',
            'imei' => 'required_if:device_type,android',
            'udid' => 'required_if:device_type,ios',

        ])->validate();

//        if ($request->phone[0] != '+') {
//            $request->phone = '+' . $request->phone;
//        }

        $country_code_id = Country::wherePhoneCode($request->country_code)->first()->id;

        $user = User::wherePhoneAndCountryCodeId($request->phone, $country_code_id)->whereHas('roles', function ($q) {
            $q->where('roles.name', 'player');
        })->first();

        if (!$user) {
            return Helper::apiNotFoundResponse(false, "User not found", new stdClass());
        }

        $this->SendOTP($request);

        $data = $this->autoLoginAndVerifyChineseUser($request);


        if ($data['status']) {
            return Helper::apiSuccessResponse(true, "User verified successfully!", $data['user']);
        } else {
            return Helper::apiNotFoundResponse(false, $data['msg'], new stdClass());
        }
    }

    public function autoLoginAndVerifyChineseUser($request, $verify = false)
    {
        $user = (new User())->getUser();
        $user = (new User())->withPlayerDetail($user);
        $user = $user->where('phone', $request->phone)
            ->whereHas('roles', function ($q) {
                $q->where('roles.name', 'player');
            })->first();

        if (!$user) {
            return ['status' => false, 'error' => 'User not found'];
        }

        $status = Helper::getStatus('active');

        if ($verify) {
            $user->verified_at = now();
            $user->verification_code = null;
            $user->status_id = $status->id ?? null;
            $user->save();
        }

        Auth::login($user);

        $user_details = $this->generateTokenAndDetails($request,$user);
        return ['status' => true, 'user' => $user_details];
    }

    /**
     * Register
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "A One-Time password has been sent to your phone",
     * "Result": {}
     * }
     *
     * @bodyParam country_code string required
     * @bodyParam phone string required
     * @bodyParam device_type string required options: ios, android
     * @bodyParam imei string required Required if device type is android
     * @bodyParam udid string required Required if device type is ios
     *
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        Validator::make($request->all(), [
            'country_code' => 'required|exists:countries,phone_code',
            'phone' => 'required',
            'device_type' => 'required|in:ios,android',
            'imei' => 'required_if:device_type,android',
            'udid' => 'required_if:device_type,ios',
            'role' => 'required|in:player,trainer',

        ])->validate();

        $this->SendOTP($request);

        $user = User::where('phone', $request->phone)->whereHas('roles', function ($q) {
            $q->where('roles.name', 'player');
        })->first();

        if (!$user) {
            $user = new User();
            $user->registerPlayer($request);
        }
        if (substr($request->phone, 0, 3) != '+86') {
            return Helper::apiSuccessResponse(true, "A One-Time password has been sent to your phone", new stdClass());
        }


        $data = $this->autoLoginAndVerifyChineseUser($request, true);
        if ($data['status']) {
            return Helper::apiSuccessResponse(true, "User verified successfully!", $data['user']);
        } else {
            return Helper::apiNotFoundResponse(false, $data['msg'], new stdClass());
        }
    }

    /**
     * Verify User
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "User verified successfully!",
     * "Result": {
     * "id": 1,
     * "nationality_id": 1,
     * "first_name": null,
     * "middle_name": null,
     * "last_name": null,
     * "surname": null,
     * "email": null,
     * "phone": "+923361227406",
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": "media/users/5f185eaf7703c1595432623.jpeg",
     * "date_of_birth": null,
     * "verification_code": "536713",
     * "verified_at": "2020-07-22T16:50:25.742786Z",
     * "active": 0,
     * "status_id": 1,
     * "created_at": "2020-07-20 20:10:44",
     * "updated_at": "2020-07-22 16:50:25",
     * "deleted_at": null,
     * "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYjAzN2YxMzRiZDkzMjAzYzA5NzM3MDE1NDM4NGI2YzM1ZDEzZThkY2RhMDA0NzhkOWQxYjk1YzRiMzg5MTdiZGIwZjQ3MzQ3MTI1Yjk2ZDMiLCJpYXQiOjE1OTU0MzY2MjUsIm5iZiI6MTU5NTQzNjYyNSwiZXhwIjoxNjI2OTcyNjI1LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.CFdlkvjosQKCvPF7Ohb8A-QmVffWQ3B94q-ka-_IEHwf5COl9rVjCCc1bmusprH0tPMl9C5dT4YXvpamtOfw5R_z6t1Rl4BRXmS_5NN893BatF4mcnuRx5YvLBdT7fQeF_Gkww8yX2pGqNR0RCxFbT72_GJSQzYbSu1RMTXy34UmUF8cqKLyk_TxC8gM3arPxiv25vCq4nmXOm_RcnoguCM7RN-QI0firuTlb6XNT4bZ6TkXkOIy7NnFMfEpRMC34C4BeVZjhuQrcjHEJb4uqKM8FcSW-VBkP-6q6h5-sXd8xZJ21ui7L1PlN6sFF620JEuvTPIDo3NFLd44Q2c_9N3_qmpLAXzr1NG2GLPLJ74O6VIeC75EC7l90vub2lp57ryOge1IKIX8Bm2bWffBtgMSqhT5nXEqTaZvNKrdLWe7TYCMWPa_or090iHcZN2uwsh3vEylckeeOJT4SMdXvWZOXgj_HvTRRbxHcwpDstR2Imu628j41pqtO5gfZ687B76JBGQXmyPnO77i1DAxfAA8uz9IRTHNsy3MAKDj_g9jo2My6jW5TvRZWc2lwFmWquj0vc5mrfSpCQxF2I0B8OHEM6hhCjirsVjcWmJeHK8WoCeflYMCNyacojIoRH2KkVpIOsg4o6WrRv-7wyLX7dgC6Ox3aSr59GJeLdpot6E",
     * "token_type": "Bearer",
     * "nationality": {
     * "id": 1,
     * "name": "Netherlands"
     * },
     * "roles": [
     * {
     * "id": 1,
     * "name": "player",
     * "pivot": {
     * "model_id": 1,
     * "role_id": 1,
     * "model_type": "App\\User"
     * }
     * }
     * ],
     * "player_details": {
     * "user_id": 1,
     * "height": 5.8,
     * "weight": 6.9,
     * "jersey_number": "10",
     * "position_id": 1,
     * "customary_foot_id": 1,
     * "positions": [
     * {
     * "id": 3,
     * "name": "Goal Keeper",
     * "lines": 2,
     * "pivot": {
     * "player_id": 1,
     * "position_id": 3
     * },
     * "line": {
     * "id": 2,
     * "name": "GoalKeepers"
     * }
     * }
     * ],
     * "customary_foot": {
     * "id": 1,
     * "name": "Left"
     * }
     * }
     * }
     * }
     *
     *
     * @bodyParam country_code string required
     * @bodyParam phone string required eg: +923361227406, it should be a valid number
     * @bodyParam device_type string required options: ios, android
     * @bodyParam verification_code string required Required Verification code
     * @bodyParam imei string required Required if device type is android
     * @bodyParam udid string required Required if device type is ios
     * @bodyParam onesignal_token string
     * @return JsonResponse
     */
    public function verifyUser(Request $request)
    {
        Validator::make($request->all(), [
            'country_code' => 'required|exists:countries,phone_code',
            'phone' => 'required',
            'device_type' => 'required|in:ios,android',
            'imei' => 'required_if:device_type,android',
            'udid' => 'required_if:device_type,ios',
            'verification_code' => 'required'
        ])->validate();

        $country_code_id = Country::wherePhoneCode($request->country_code)->first()->id;

        $user = (new User())->getUser();
        $user = (new User())->withPlayerDetail($user);
        $user = $user->wherePhoneAndCountryCodeId($request->phone, $country_code_id)
            ->whereHas('roles', function ($q) {
                $q->where('roles.name', 'player');
            })->first();

        if (!$user) {
            return Helper::apiNotFoundResponse(false, 'User not found', new stdClass());
        }


        $verify_response = Helper::verifyOtp("+" . $request->country_code . $request->phone, $request->verification_code);

        if (!$verify_response) {
            return Helper::apiNotFoundResponse(false, 'Invalid verification code', new stdClass());
        }

        $status = Status::where('name', 'active')->first();

        $user->verified_at = now();
        $user->verification_code = $request->verification_code;
        $user->status_id = $status->id ?? null;
        $user->save();

        Auth::login($user);

        $user_details = $this->generateTokenAndDetails($request,$user);
        return Helper::apiSuccessResponse(true, "User verified successfully!", $user_details);
    }

    /**
     * Auto Login
     *
     * @response {
     *   "Response": true,
     *   "StatusCode": 200,
     *   "Message": "User logged-in successfully!",
     *   "Result": {
     *       "id": 174,
     *       "nationality_id": null,
     *       "first_name": "Bo-Jane",
     *       "middle_name": "''",
     *       "last_name": "Ladru",
     *       "surname": null,
     *       "email": null,
     *       "new_temp_email": null,
     *       "humanox_username": null,
     *       "humanox_user_id": null,
     *       "humanox_pin": null,
     *       "humanox_auth_token": null,
     *       "phone": "+86624154844",
     *       "gender": null,
     *       "language": null,
     *       "address": null,
     *       "profile_picture": null,
     *       "date_of_birth": "1996-01-30",
     *       "age": null,
     *       "badge_count": 0,
     *       "verification_code": "469616",
     *       "verified_at": "2020-12-21 13:08:37",
     *       "active": 0,
     *       "status_id": 1,
     *       "who_created": null,
     *       "last_seen": "2021-06-29 09:58:53",
     *       "online_status": "1",
     *       "created_at": "2020-12-21 13:08:10",
     *       "updated_at": "2021-06-29 09:58:53",
     *       "deleted_at": null,
     *       "role": "Player",
     *       "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxNSIsImp0aSI6ImVlMjNmNzJhYmVlZjg1OGQ2ZGVmNjEyNTBhNGRlYjk0NDY2MzdlYWU0NGM2MWU1YjJhYTgwMDkwMDk5OTVlNzAxZDY4MWE3Zjg0NTY0ZmY3IiwiaWF0IjoxNjI0OTYwNzMzLjkzMTc4MSwibmJmIjoxNjI0OTYwNzMzLjkzMTc4NiwiZXhwIjoxNjU2NDk2NzMzLjkwMzA1Mywic3ViIjoiMTc0Iiwic2NvcGVzIjpbXX0.OIJCfotOMtsVaPOaxutCEt05R9u--yrbNpitwGFYnnPGL_XqwuYkVuYfdBOKXTwU-Zz2as0Xxn9x97BSk65_JTobppFPHkWkH006iXIApsXxqQTTEAy3TcrPdsh_QAWJ_a366DNOpjFtL3p9J-t2PlFtaT4KRYz_Vs1Uo1iwynxpGfUjhymtNO4OnSSFIDcymP5bHBvoVvY1BUQadx1SFDIS_Mxl5vWvrZiw0Ebhneupd8bY3d5uRxkB_0MGPqdiAuzAnOCn9d_SEa_zvM2O8xHgCq6WacH_hl1qlBYp92i_pxwXwT68IVYCs5WI8GeosWM7imMbgevMF1PmVwWzmwaji4sW1GozYcs48Y1arDl-I7f10LLGE1PVF5qyVZfspTFEUoRThQXFMabKMXGV9DDGuT6ERnAfoA_GVuhtojHcQvrJQGFLRpVYqjWeopZhdvv4jr4AIb1jorwTsSH0Qqej45YDhBnb_b7LXpnSg7-x2pDo14Mp3tfWyAgittaHSokQHFzhRXfmd7Rb5gm_FjaMD97h73kZpnt3Pzj_RI1uPK1IXeWKA76w2XphOY2twJSTCVypKOljxt63K97CPQjQ7UQdIEHgMVdHcqeYZG5T4tURiXzI5c97Y2_tvqrufawONMloYXj06alSt2VkP9ewVV_w1_LhpPfbqCtD4IM",
     *       "token_type": "Bearer",
     *       "notification_unread_count": 0,
     *       "unread_count": 0,
     *       "rank": 13,
     *       "color_codes": {
     *           "primary": "#dbff00",
     *           "Secondary": "#aa37ff"
     *       },
     *       "nationality": null,
     *       "roles": [
     *           {
     *               "id": 1,
     *               "name": "player",
     *               "guard_name": "api",
     *               "created_at": null,
     *               "updated_at": null,
     *               "pivot": {
     *                   "model_id": 174,
     *                   "role_id": 1,
     *                   "model_type": "App\\User"
     *               }
     *           }
     *       ],
     *       "player_details": {
     *           "user_id": 174,
     *           "height": null,
     *           "weight": null,
     *           "jersey_number": null,
     *           "position_id": null,
     *           "customary_foot_id": null,
     *          "positions": [
     * {
     * "id": 3,
     * "name": "Goal Keeper",
     * "lines": 2,
     * "pivot": {
     * "player_id": 1,
     * "position_id": 3
     * },
     * "line": {
     * "id": 2,
     * "name": "GoalKeepers"
     * }
     * }
     * ],
     *           "customary_foot": null
     *       },
     *       "teams": [
     *           {
     *               "id": 5,
     *               "team_name": "consequatur",
     *               "image": "",
     *               "description": "tempore",
     *               "pivot": {
     *                   "user_id": 174,
     *                   "team_id": 5,
     *                   "created_at": "2020-12-21 13:08:10"
     *               },
     *               "trainers": [
     *                   {
     *                       "id": 91,
     *                       "first_name": "Trainer Durgan",
     *                       "middle_name": null,
     *                       "last_name": null,
     *                       "surname": "Trainer Durgan",
     *                       "profile_picture": "media/users/z6IJLTTG0tLsW6LbDtAYEQvAE09UUisv94dV8jw1.jpeg",
     *                       "pivot": {
     *                           "team_id": 5,
     *                           "trainer_user_id": 91,
     *                           "created_at": null
     *                       }
     *                   }
     *               ]
     *           }
     *       ]
     *   }
     *}
     *
     * @bodyParam device_type string required options: ios, android
     * @bodyParam imei string required Required if device type is android
     * @bodyParam udid string required Required if device type is ios
     * @bodyParam device_token string
     * @bodyParam onesignal_token string
     * @return JsonResponse
     */
    public function autoLogin(Request $request)
    {
        Validator::make($request->all(), [
            'device_type' => 'required|in:ios,android',
            'imei' => 'required_if:device_type,android',
            'udid' => 'required_if:device_type,ios'
        ])->validate();
        $user = (new User())->getUser();
        $user = (new User())->withPlayerDetail($user);
        $user = (new User())->withTeam($user);
        $user = $user->whereHas('user_devices', function ($q) use ($request) {

                if ($request->device_type == 'ios') {
                    $q->where('udid', $request->udid);
                } else {
                    $q->where('imei', $request->imei);
                }

            })->with("roles")->whereNotNull('verified_at')
            ->first();

        if (!$user) {
            return Helper::apiNotFoundResponse(false, 'User not found', new stdClass());
        }

        for ($i = 0; $i < count($user->roles); $i++) {
            if ($user->roles[$i]->name == "trainer") {
                $user->role = "Trainer";
                break;
            } elseif ($user->roles[$i]->name == "player") {
                $user->role = "Player";
                break;
            }

        }

        Auth::login($user);

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();

        $user->access_token = $tokenResult->accessToken;
        $user->token_type = 'Bearer';

        if ($request->device_type == 'ios') {
            $match_these['udid'] = $request->udid;
        } else {
            $match_these['imei'] = $request->imei;
        }

        $user_device = UserDevice::where($match_these)->first();

        if ($user_device) {
            $user_device->device_token = $request->device_token ?? null;
            $user_device->onesignal_token = $request->onesignal_token ?? null;
            $user_device->save();
        }

        User::where('id', $user->id)->update([
            'badge_count' => 0
        ]);

        $user->notification_unread_count = UserNotification::where('to_user_id', $user->id)->whereHas('status', function ($q) {
            $q->where('name', 'unread');
        })->count();


        $user_groups = $this->getGroupIdArray($user);
        $total_messages = $this->getTotalMessages($user_groups);

        $read_messages = $this->getReadMessages($user,$user_groups);

        $color_code = [
            "primary" => "#dbff00",
            "secondary" => "#aa37ff"
        ];
        $user->unread_count = $total_messages->total_messages - $read_messages->read_messages;
        $user->rank = rand(2, 22);
        $user->color_codes = $color_code;
        if (strtolower($user->role) == "trainer") {
            $get_club_id = SelectedClub::select("club_id")->where("trainer_user_id", auth()->user()->id ?? $user->id)->first();
            $user->selected_club_id = $get_club_id != null ? $get_club_id->club_id : null;
        }
        return Helper::apiSuccessResponse(true, "User logged-in successfully!", $user);
    }

    /**
     *
     * Logout
     *
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
     * Send Code
     *
     * @bodyParam phone required
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "A One-Time password has been sent to your phone",
     * "Result": {}
     * }
     * @return JsonResponse
     */
    public function sendVerificationCode(Request $request)
    {
        Validator::make($request->all(), [
            'country_code' => 'required|exists:countries,phone_code',
            'phone' => 'required'
        ])->validate();

        if ($request->country_code[0] != '+') {
            $request->country_code = '+' . $request->country_code;
        }

        $user = User::where('phone', $request->phone)->whereHas('roles', function ($q) {
            $q->where('roles.name', 'player');
        })->first();

        if (!$user) {
            return Helper::apiNotFoundResponse(false, 'User not found', new stdClass());
        }

        $response = Helper::sendOtp($request->country_code . $request->phone);

        if (gettype($response) == 'string') {
            activity()->causedBy($user)->performedOn($user)->log($response);
            return Helper::apiErrorResponse(false, $response, new stdClass());
        }

        return Helper::apiSuccessResponse(true, "A One-Time password has been sent to your phone", new stdClass());
    }

    public function generateTokenAndDetails($request,$user){
        Helper::saveToken($request,$user->id);

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();

        $user->access_token = $tokenResult->accessToken;
        $user->token_type = 'Bearer';

        $user->notification_unread_count = UserNotification::where('to_user_id', $user->id)->whereHas('status', function ($q) {
            $q->where('name', 'unread');
        })->count();


        $user_groups = $this->getGroupIdArray($user);
        $total_messages = $this->getTotalMessages($user_groups);

        $read_messages = $this->getReadMessages($user,$user_groups);

        $user->unread_count = $total_messages->total_messages - $read_messages->read_messages;
        $user->rank = rand(2, 22);
        $color_code = [
            "primary" => "#dbff00",
            "secondary" => "#aa37ff"
        ];
        $user->color_codes = $color_code;
        return $user;
    }
}
