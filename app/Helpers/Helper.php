<?php

namespace App\Helpers;

use App\AssignmentExercise;
use App\Battle;
use App\BattleRound;
use App\ChatGroup;
use App\Contact;
use App\Country;
use App\Http\Resources\Api\App\GetTeamPlayerResourceListing;
use App\Http\Resources\Api\Dashboard\General\CountryCodesResource;
use App\PlayerAssignment;
use App\PlayerExercise;
use App\PlayerTeamRequest;
use App\Post;
use App\PricingPlan;
use App\Setting;
use App\Status;
use App\Team;
use App\TeamSubscription;
use App\User;
use App\UserBattle;
use App\UserDevice;
use App\UserNotification;
use App\ZohoFailedLead;
use App\ZohoLead;
use App\Assignment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use Mail;
use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Twilio\Rest\Client;
use function activity;
use App\Http\Resources\Api\Dashboard\Clubs\Teams\ListingResource as TeamsListingResource;

class Helper
{

    public static function sendNotification($data, $token, $env)
    {
        $status = Status::where('name', 'unread')->first();
        $notification_title = array("en" => "JOGO");
        $user_notification = UserNotification::create([
            'from_user_id' => $data['from_user_id'], 'to_user_id' => $data['to_user_id'], 'model_type' => $data['model_type'], 'model_type_id' => $data['model_type_id'],
            'click_action' => $data['click_action'], 'description' => $data['message'], 'status_id' => $status->id ?? 0
        ]);

        $data['notification_id'] = $user_notification->id ?? 0;

        $date = Carbon::now();

        // $header = [
        //     'Authorization: key=' . config('firebase.server_api_key'),
        //     'Content-Type: Application/json'
        // ];
        // $notification = [
        //     'title' => $notification_title,
        //     'icon' => '',
        //     'image' => '',
        //     'sound' => 'default',
        //     'date' => $date->diffForHumans(),
        //     'content_available' => true,
        //     "priority" => "high",
        //     'badge' => $data['badge_count']
        // ];
        $data['message'] = json_decode($data['message'], true);

        $content = array(
            "en" => $data['message']['en'],
            "nl" => $data['message']['nl']
        );

        if ($env == "android") {
            $app_id = env('ONESIGNAL_ANDROID_APP_ID');
            $server_id = env('ONESIGNAL_ANDROID_SERVER_ID');
        } else if ($env == "ios") {
            $app_id = env('ONESIGNAL_IOS_APP_ID');
            $server_id = env('ONESIGNAL_IOS_SERVER_ID');
        } else if ($env == "web") {
            $app_id = env('ONESIGNAL_WEB_APP_ID');
            $server_id = env('ONESIGNAL_WEB_SERVER_ID');
        }


        $fields = array(
            'app_id' => $app_id,
            'include_player_ids' => array($token),
            'contents' => $content,
            'small_icon' => '',
            'large_icon' => '',
            'priority' => 10,
            'data' => $data,
            'headings' => $notification_title,
            'ios_badgeCount' => $data['badge_count']
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://onesignal.com/api/v1/notifications",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json;charset=utf-8',
                'Authorization: Basic ' . $server_id)
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            activity()->log($err);
        } else {
            activity()->log($response);
        }
    }

    public static function uploadBase64File($base64_file, $destination_path)
    {
        $extension = explode('/', mime_content_type($base64_file))[1];

        $file_name = uniqid() . time() . ".$extension";
        @list($type, $base64_file) = explode(';', $base64_file);
        @list(, $base64_file) = explode(',', $base64_file);

        if ($base64_file != "") {
            // storing image in storage/app/public Folder

            $destination_path = $destination_path . '/' . $file_name;
            $destination_path = preg_replace('#/+#', '/', $destination_path);
            Storage::put($destination_path, base64_decode($base64_file));

            return $destination_path;
        }

        return "";
    }

    public static function generateOtp()
    {
        return mt_rand(100000, 999999);
    }

    public static function verifyOtp($to_phone, $otp_code)
    {
        // Your Account SID and Auth Token from twilio.com/console
        $sid = config('twilio.account_sid');
        $token = config('twilio.auth_token');
        $verify_sid = config('twilio.verify_sid');

        $client = new Client($sid, $token);

        try {
            $verification = $client->verify->v2->services($verify_sid)
                ->verificationChecks
                ->create($otp_code, array('to' => $to_phone));
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $verification->valid;
    }

    public static function sendOtp($to_phone)
    {
        // Your Account SID and Auth Token from twilio.com/console
        $sid = config('twilio.account_sid');
        $token = config('twilio.auth_token');
        $verify_sid = config('twilio.verify_sid');

        $client = new Client($sid, $token);

        try {
            $client->verify->v2->services($verify_sid)
                ->verifications
                ->create($to_phone, "sms", [
                    "appHash" => "cCXT3TjiUZs"
                ]);
        } catch (Exception $e) {
            return $e->getMessage();

        }

        return true;
    }

    public static function response($response, $status_code, $message, $result, $http_status_code = 200)
    {
        return response()->json([
            'Response' => $response,
            'StatusCode' => $status_code,
            'Message' => $message,
            'Result' => $result
        ], $http_status_code);
    }

    public static function apiSuccessResponse($response, $message, $result, $status_code = 200, $http_status_code = 200)
    {
        return response()->json([
            'Response' => $response,
            'StatusCode' => $status_code,
            'Message' => $message,
            'Result' => $result
        ], $http_status_code);
    }

    public static function apiNotFoundResponse($response, $message, $result, $status_code = 404, $http_status_code = 200)
    {
        return response()->json([
            'Response' => $response,
            'StatusCode' => $status_code,
            'Message' => $message,
            'Result' => $result
        ], $http_status_code);
    }

    public static function apiErrorResponse($response, $message, $result, $status_code = 500, $http_status_code = 200)
    {
        return response()->json([
            'Response' => $response,
            'StatusCode' => $status_code,
            'Message' => $message,
            'Result' => $result
        ], $http_status_code);
    }

    public static function apiInvalidParamResponse($response, $message, $result, $status_code = 422, $http_status_code = 200)
    {
        return response()->json([
            'Response' => $response,
            'StatusCode' => $status_code,
            'Message' => $message,
            'Result' => $result
        ], $http_status_code);
    }

    public static function apiUnAuthenticatedResponse($response, $message, $result, $status_code = 401, $http_status_code = 200)
    {
        return response()->json([
            'Response' => $response,
            'StatusCode' => $status_code,
            'Message' => $message,
            'Result' => $result
        ], $http_status_code);
    }

    public static function apiUnAuthorizedResponse($response, $message, $result, $status_code = 403, $http_status_code = 200)
    {
        return response()->json([
            'Response' => $response,
            'StatusCode' => $status_code,
            'Message' => $message,
            'Result' => $result
        ], $http_status_code);
    }

    public static function apiInvalidReqMethodResponse($response, $message, $result, $status_code = 405, $http_status_code = 200)
    {
        return response()->json([
            'Response' => $response,
            'StatusCode' => $status_code,
            'Message' => $message,
            'Result' => $result
        ], $http_status_code);
    }

    public static function paginate(Collection $results, $pageSize)
    {
        $page = Paginator::resolveCurrentPage('page');

        $total = $results->count();

        return self::paginator($results->forPage($page, $pageSize), $total, $pageSize, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);

    }

    /**
     * Create a new length-aware paginator instance.
     *
     * @param  \Illuminate\Support\Collection $items
     * @param  int $total
     * @param  int $perPage
     * @param  int $currentPage
     * @param  array $options
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected static function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items', 'total', 'perPage', 'currentPage', 'options'
        ));
    }


    function getIPAddress()
    {
        //whether ip is from the share internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } //whether ip is from the proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } //whether ip is from the remote address
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public static function generateAccessToken()
    {
        /*try
        {
            $data = [
                'refresh_token' => env('ZOHO_REFRESH_TOKEN'),
                'redirect_uri' => env('ZOHO_REDIRECT_URL'),
                'client_id' => env('ZOHO_CLIENT_ID'),
                'client_secret' => env('ZOHO_CLIENT_SECRET'),
                'grant_type' => 'refresh_token',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://accounts.zoho.eu/oauth/v2/token');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            $response = json_decode(curl_exec($ch), 1);

            if (!isset($response['access_token'])) {
                return [
                    'status' => 'error',
                    'msg' => $response['error']
                ];
            } else {
                return [
                    'status' => 'success',
                    'access_token' => $response['access_token']
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status' => 'error',
                'msg' => $ex->getMessage()
            ];
        }*/
    }

    public static function leadResponse($response,$type,$id,$new = true){
        /*if($response['status'] == 'error'){
            $newLead = new ZohoFailedLead;
            $newLead->error = isset($response['msg']) ? $response['msg'] : 'Something went wrong';
        } else {
            if ($new) {
                $newLead = new ZohoLead;
                $newLead->zoho_id = $response['id'];
            } else {
                $newLead = ZohoLead::whereTypeAndTypeId($type, $id)->first();
                if (empty($newLead)) {
                    $newLead = new ZohoFailedLead;
                    $newLead->error = 'Record not found';
                    $newLead->type = $type;
                    $newLead->type_id = $id;
                    $newLead->save();
                    return ['status' => 'error', 'id' => 'Record not found'];
                }
            }
        }
        $newLead->type = $type;
        $newLead->type_id = $id;
        $newLead->save();*/
    }

    public static function createOrganization($club){
        /*$getAccessToken = Helper::generateAccessToken();
        if(isset($getAccessToken['status']) && $getAccessToken['status'] == 'error'){
            return ['status' => 'error', 'msg' => $getAccessToken['msg']];
        }

        $access_token = $getAccessToken['access_token'];
        //$country = Country::whereId($club->country_id)->first()->name;

        $promoCode = "";

        if (count($club->trainers) > 0) {
            foreach ($club->trainers as $key => $value) {
                if (isset($value->coupon->code)) {
                    $promoCode = $value->coupon->code;

                    break;
                }
            }
        }

        $data = [
            'data' => [
                [
                    'Account_Name' => $club->title,
                    'Club_Website' => $club->website,
                    'Account_Type' => $club->type,
                    'Teams' => Helper::getTeamName(),
                    'Billing_City' => $club->city,
                    'Billing_Street' => $club->address,
                    //'Billing_Country' => $country,
                    'Ambassador_Code' => $promoCode
                ]
            ],
            'trigger' => [
                'approval',
                'workflow',
                'blueprint'
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.zohoapis.eu/crm/v2/accounts');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Zoho-oauthtoken ' . $access_token,
                'Content-Type: application/x-www-form-urlencoded'
            )
        );
        $response = json_decode(curl_exec($ch), 1);

        if (strtolower($response['data'][0]['code']) != "success") {
            return ['status' => 'error', 'msg' => $response['data'][0]['message']];
        } else {
            return ['status' => 'success', 'id' => $response['data'][0]['details']['id']];
        }*/
    }

    public static function updateOrganization($club,$id){
        /*$getAccessToken = Helper::generateAccessToken();
        if(isset($getAccessToken['status']) && $getAccessToken['status'] == 'error'){
            return ['status' => 'error', 'msg' => $getAccessToken['msg']];
        }

        $access_token = $getAccessToken['access_token'];
        $zohoLead = ZohoLead::whereTypeAndTypeId('club', $id)->first();
        if (empty($zohoLead)) {
            $newLead = new ZohoFailedLead;
            $newLead->error = 'Record not found';
            $newLead->type = 'club';
            $newLead->type_id = $id;
            $newLead->save();
            return ['status' => 'error', 'id' => 'Record not found'];
        }
        //$country = Country::whereId(164)->first()->name;

        $promoCode = "";

        if (count($club->trainers) > 0) {
            foreach ($club->trainers as $key => $value) {
                if (isset($value->coupon->code)) {
                    $promoCode = $value->coupon->code;

                    break;
                }
            }
        }

        $data = [
            'data' => [
                [
                    'id' => $zohoLead->zoho_id,
                    'Account_Name' => $club->title,
                    'Club_Website' => $club->website,
                    'Account_Type' => $club->type,
                    'Teams' => Helper::getTeamName(),
                    'Billing_City' => $club->city,
                    'Billing_Street' => $club->address,
                    //'Billing_Country' => $country,
                    'Ambassador_Code' => $promoCode
                ]
            ],
            'trigger' => [
                'approval',
                'workflow',
                'blueprint'
            ]
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.zohoapis.eu/crm/v2/accounts/' . $zohoLead->zoho_id);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Zoho-oauthtoken ' . $access_token,
                'Content-Type: application/x-www-form-urlencoded'
            )
        );
        $response = json_decode(curl_exec($ch), 1);

        if (strtolower($response['data'][0]['code']) != "success") {
            return ['status' => 'error', 'msg' => $response['data'][0]['message']];
        } else {
            return ['status' => 'success', 'id' => $response['data'][0]['details']['id']];
        }*/
    }

    public static function createContact($contact, $organization_name)
    {

        /*$getAccessToken = Helper::generateAccessToken();
        if(isset($getAccessToken['status']) && $getAccessToken['status'] == 'error'){
            return ['status' => 'error', 'msg' => $getAccessToken['msg']];
        }

        $access_token = $getAccessToken['access_token'];

        $assignments = $contact->assignments->sortByDesc('created_at');

        $lastAssignment = "";

        if (count($assignments) > 0) {
            $lastAssignment = date('Y-m-d', strtotime($assignments[0]->created_at));
        }

        $data = [
            'data' => [
                [
                    'First_Name' => $contact->first_name,
                    'Last_Name' => $contact->last_name,
                    'Email' => $contact->email,
                    'Account_Name' => $organization_name,
                    'Last_Login_1' => date('Y-m-d', strtotime($contact->last_seen)),
                    'Last_Assignment_1' => $lastAssignment
                ]
            ],
            'trigger' => [
                'approval',
                'workflow',
                'blueprint'
            ]
        ];

        //dd($data['data'][0]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.zohoapis.eu/crm/v2/contacts');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Zoho-oauthtoken ' . $access_token,
                'Content-Type: application/x-www-form-urlencoded'
            )
        );
        $response = json_decode(curl_exec($ch), 1);

        if (strtolower($response['data'][0]['code']) != "success") {
            return ['status' => 'error', 'msg' => $response['data'][0]['message']];
        } else {
            return ['status' => 'success', 'id' => $response['data'][0]['details']['id']];
        }*/
    }

    public static function updateContact($contact, $id = 0)
    {

        /*$getAccessToken = Helper::generateAccessToken();
        if(isset($getAccessToken['status']) && $getAccessToken['status'] == 'error'){
            return ['status' => 'error', 'msg' => $getAccessToken['msg']];
        }

        $access_token = $getAccessToken['access_token'];
        $zohoLead = ZohoLead::whereTypeAndTypeId('trainer', $id)->first();
        if (empty($zohoLead)) {
            $newLead = new ZohoFailedLead;
            $newLead->error = 'Record not found';
            $newLead->type = 'trainer';
            $newLead->type_id = $id;
            $newLead->save();
            return ['status' => 'error', 'id' => 'Record not found'];
        }

        $assignments = $contact->assignments->sortByDesc('created_at');

        $lastAssignment = "";

        if (count($assignments) > 0) {
            $lastAssignment = date('Y-m-d', strtotime($assignments[0]->created_at));
        }

        $data = [
            'data' => [
                [
                    'id' => $zohoLead->zoho_id,
                    'First_Name' => $contact->first_name,
                    'Last_Name' => $contact->last_name,
                    'Email' => $contact->email,
                    'Phone' => $contact->phone,
                    'Last_Login_1' => date('Y-m-d', strtotime($contact->last_seen)),
                    'Last_Assignment_1' => $lastAssignment
                ]
            ],
            'trigger' => [
                'approval',
                'workflow',
                'blueprint'
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.zohoapis.eu/crm/v2/contacts/' . $zohoLead->zoho_id);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Zoho-oauthtoken ' . $access_token,
                'Content-Type: application/x-www-form-urlencoded'
            )
        );
        $response = json_decode(curl_exec($ch), 1);
        if (strtolower($response['data'][0]['code']) != "success") {
            return ['status' => 'error', 'msg' => $response['data'][0]['message']];
        } else {
            return ['status' => 'success', 'id' => $response['data'][0]['details']['id']];
        }*/
    }

    public static function getTeamName(){
        /*$teamName = '';
        $club = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->first();
        if (!$club) {
            return Helper::apiErrorResponse(false, 'Club not found', new \stdClass());
        }
        $club_id = $club->club_id ?? 0;
        $teams = Team::whereHas('clubs', function ($q) use ($club_id) {
            return $q->where('club_id', $club_id);
        })->get();

        foreach ($teams as $team) {
            $teamName .= $team->team_name . ",";
        }

        return $teamName == '' ? $teamName : substr($teamName,0,strlen($teamName) - 1);*/
    }

    public static function getPermissions($roleName)
    {
        $role = Role::with('permissions')->whereName($roleName)->first();
        $rolePermissions = [];
        foreach ($role->permissions as $permission) {
            $rolePermissions[] = $permission['name'];
        }
        return $rolePermissions;
    }

    public static function createDateRange($request, $fromDate, $toDate, $step = '+1 Day', $outputFormat = 'Y-m-d', $runTill = '')
    {
        $dates = [];

        $actualFromDate = strtotime($fromDate);

        $actualToDate = strtotime($toDate);

        $fromDate = strtotime($fromDate);

        $toDate = strtotime($toDate);

        if (!empty($runTill)) {
            $toDate = strtotime($runTill);
        }

        $dataDate = date("d", $fromDate);

        while ($fromDate <= $toDate)
        {
            if ($step == '+30 Days')
            {
                if ($request->years && in_array(date("Y", $fromDate), $request->years))
                {
                    $dates[] = [
                        'date' => checkdate(date("m", $fromDate), $dataDate, date("Y", $fromDate)) ? date("Y-m", $fromDate) . "-" . $dataDate : date("Y-m-t", $fromDate),
                        'day' => date('l', $fromDate)
                    ];

                    $fromDate = strtotime("+28 Days", $fromDate);
                }
                else
                {
                    $dates[] = [
                        'date' => checkdate(date("m", $fromDate), $dataDate, date("Y", $fromDate)) ? date("Y-m", $fromDate) . "-" . $dataDate : date("Y-m-t", $fromDate),
                        'day' => date('l', $fromDate)
                    ];

                    $fromDate = strtotime("+1 Day", $fromDate);
                }
            }
            else
            {
                $dates[] = [
                    'date' => date($outputFormat, $fromDate),
                    'day' => date('l', $fromDate)
                ];

                $fromDate = strtotime($step, $fromDate);
            }
        }
        
        return $dates;
    }

    public static function getAllSettings()
    {
        $all_settings = Setting::get();
        $settings = [];
        foreach ($all_settings as $setting) {
            $settings[$setting->key] = unserialize($setting->value);
        }
        return $settings;
    }

    public static function settings($service, $key)
    {
        $settings = Helper::getAllSettings();
        return $settings[$service][$key];
    }

    public static function sendCustomMessage($toNumber, $customMessage)
    {
        $sid = config('twilio.account_sid');
        $token = config('twilio.auth_token');
        $client = new Client($sid, $token);

        try {
            $client->messages->create($toNumber, [
                'from' => '+18449843471',
                'body' => $customMessage,
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return true;
    }

    public static function sendMail($template, $subject, $mailData, $data, $activityLog = 'enableLog')
    {
        try {
            Mail::send($template, $mailData, function ($mail) use ($subject, $data) {
                $mail->to($data->email, $data->first_name . ' ' . $data->last_name)
                    ->subject($subject);
            });

            $response = 'success';
        } catch (Exception $ex) {
            if ($activityLog == 'enableLog') {
                //activity()->causedBy($data)->performedOn($data)->log($ex->getMessage());
            }

            $response = 'error';
        }

        return $response;
    }

    public static function checkTeamUpgradation($selected_club_id = 0)
    {
        if ($selected_club_id == 0) {
            $club = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->first();
            $club_id = $club->club_id ?? 0;
        } else {
            $club_id = $selected_club_id;
        }

        $teams = Team::whereHas('clubs', function ($q) use ($club_id) {
            return $q->where('club_id', $club_id);
        })->get();

        $plan = 'freemium';
        foreach ($teams as $team) {
            $team_detail = TeamSubscription::whereTeamIdAndStatus($team->id, '1')->first();
            if (!empty($team_detail)) {
                $plan_detail = PricingPlan::whereId($team_detail->plan_id)->first();
                $role = Role::whereId($plan_detail->role_id)->first();
                $plan = $role->name;
                break;
            }
        }
        return $plan;
    }

    public static function getTeamPermissions($team_id)
    {
        $plan = 'freemium';
        $team_detail = TeamSubscription::whereTeamIdAndStatus($team_id, '1')->first();
        if (!empty($team_detail)) {
            $plan_detail = PricingPlan::whereId($team_detail->plan_id)->first();
            $role = Role::whereId($plan_detail->role_id)->first();
            $plan = $role->name;
        }

        return Helper::getPermissions($plan);
    }

    public static function checkUpdate($update_version, $current_version)
    {
        $update_version = explode('.', $update_version);
        $current_version = explode('.', $current_version);
        $data = [
            'update' => 0,
            'force_update' => 0
        ];
        if ($update_version[0] > $current_version[0]) {
            $data = [
                'update' => 1,
                'force_update' => 1
            ];
        } elseif ($update_version[0] == $current_version[0]) {
            if ($update_version[1] > $current_version[1]) {
                $data = [
                    'update' => 1,
                    'force_update' => 1
                ];
            } elseif ($update_version[1] == $current_version[1]) {
                if ($update_version[2] > $current_version[2]) {
                    $data = [
                        'update' => 1,
                        'force_update' => 1
                    ];
                }
            }
        }
        return $data;
    }

    public static function acceptTeamRequest($request)
    {
        $team_request = PlayerTeamRequest::find($request->request_id);
        if (!$team_request) {
            return Helper::apiErrorResponse(false, 'Invalid ID', new \stdClass());
        }
        $player = User::find($team_request->player_user_id);
        $player->teams()->sync([$team_request->team_id], false);
        //add user to team group
        $group = ChatGroup::where('team_id', $team_request->team_id)->first();
        if ($group) {
            $group->members()->syncWithoutDetaching([$player->id]);
        }
        $player = User::where('id', $player->id)
            ->first();
        $push_notify = [];
        $push_notify['message']['en'] = " ";
        $push_notify['message']['nl'] = " ";
        $push_notify['from_user_id'] = auth()->user()->id;
        $push_notify['to_user_id'] = $player->id;
        $push_notify['model_type'] = 'team/added';
        $push_notify['model_type_id'] = $request->request_id;
        $push_notify['click_action'] = 'TeamAdded';
        $push_notify['message']['en'] = auth()->user()->first_name . ' ' . auth()->user()->last_name . ' has added you to the team ';
        $push_notify['message']['nl'] = auth()->user()->first_name . ' ' . auth()->user()->last_name . ' heeft een evenement aangemaakt';
        $push_notify['message'] = json_encode($push_notify['message']);
        $push_notify['badge_count'] = ($player->badge_count ?? "") + 1;

        $devices = $player->user_devices;

        $tokens = [];

        foreach ($devices as $key => $value) {
            if ($value->device_token) {
                array_push($tokens, $value->device_token);
            }
        }

        if (count($tokens) > 0) {
            foreach ($devices as $device) {
                Helper::sendNotification($push_notify, $device->onesignal_token, $device->device_type);
            }

            User::where('id', $push_notify['to_user_id'])
                ->update([
                    'badge_count' => $push_notify['badge_count']
                ]);
        }
        $team_request->delete();
    }

    public static function getPostObject($ex)
    {
        $obj = new \stdClass();
        $obj->id = $ex->id;
        $obj->author_id = $ex->author_id;
        $obj->exercise_id = $ex->exercise_id;
        $obj->level_id = $ex->level_id;
        $obj->post_title = $ex->post_title;
        $obj->post_desc = $ex->post_desc;
        $obj->thumbnail = $ex->thumbnail;
        $obj->post_attachment = $ex->post_attachment;
        $obj->status_id = $ex->status_id;
        $obj->created_at = $ex->created_at;
        $obj->updated_at = $ex->updated_at;
        $obj->deleted_at = $ex->deleted_at;
        $obj->author = $ex->author;
        $obj->comments = count($ex->comments);
        $obj->likes = count($ex->likes);
        $obj->user_likes_count = $ex->user_likes_count;
        if (count($ex->likes) > 0) {
            $check_contact_ids = array();
            foreach ($ex->likes as $like) {
                $check_contact_ids[] = $like->contact_id;
            }

            if (in_array(Auth::user()->id, $check_contact_ids)) {
                $obj->i_liked = true;
            } else {
                $obj->i_liked = false;
            }
        } else {
            $obj->i_liked = false;
        }

        if (Auth::user()->id == $ex->author_id) {
            $user_privacy_settings = User::with('user_privacy_settings:name,description')->find($ex->author_id)->user_privacy_settings;
            $obj->user_privacy_settings = $user_privacy_settings[0]->pivot->access_modifier_id;
        }

        return $obj;
    }

    public static function getExerciseObject($ex, $player_id, $conversation_ids,$extraInfo)
    {
        $obj = new \stdClass();
        $obj->id = $ex->id;
        $obj->title = $ex->title;
        $obj->completion_time = $ex->completion_time;
        $obj->thumbnail = $ex->thumbnail ?? "";
        $obj->video_file = $ex->video_file ?? "";
        $obj->created_at = $ex->created_at;
        $obj->level_id = $ex->level_id;
        $obj->posts = (new Post())->getCheckPosts($ex,$player_id,$conversation_ids,$extraInfo);

        if(!$extraInfo)
        {
            return $obj;
        }

        $obj->total_comments = isset($obj->posts) ? $obj->posts->comments->count() : 0;
        unset($obj->posts);
        return $obj;
    }

    public static function getTimeDataRoundForMatch($request){
        if ($request->date) {
            $date = date('Y-m-d', strtotime($request->date));
        } else {
            $date = date('Y-m-d');
        }

        if ($request->time) {
            $time = date('H:i', strtotime($request->time));
        } else {
            $time = date('H:i');
        }

        $rounds = $request->exercise_id ? count($request->exercise_id) : 0;

        if(!$rounds) {
            if ($request->type == 'best_of_three') {
                $rounds = 3;
            } elseif ($request->type == 'best_of_five') {
                $rounds = 5;
            } elseif ($request->type == 'best_of_seven') {
                $rounds = 7;
            } else {
                $rounds = 1;
            }
            $exercises = Exercise::where('badge', 'ai')->pluck('id', 'id')->toArray();
            $request->exercise_id = array_rand($exercises, $rounds);

        }

        return ['data' => $date, 'time' => $time, 'rounds' => $rounds, 'request' => $request];
    }

    public static function createMatchNotification($request, $date, $time, $rounds)
    {
        if (!isset($request->user_ids) || count($request->user_ids) <= 0) {
            $fu = User::role('player')->where('online_status', 1)->where('id', '!=', Auth::user()->id)->limit(30)->pluck('id', 'id')->toArray();
            if ($fu && count($fu) > 0) {
                $request->request->add(["user_ids" => [array_rand($fu)]]);
            } else {
                return self::apiErrorResponse(true, 'We can not find any player at this time', []);
            }
        }

        $battle = new Battle();

        $battle->user_id = Auth::user()->id;
//        $battle->exercise_id = $request->exercise_id;
        $battle->type = $request->type;
        $battle->date = $date;
        $battle->time = $time;
        $battle->rounds = $rounds;
        $battle->title = $request->title;
        $battle->save();

        $ub = new UserBattle();
        $ub->user_id = Auth::user()->id;
        $ub->battle_id = $battle->id;
        $ub->save();
        foreach ($request->exercise_id as $index => $ex) {
            $rounds_exercises = new BattleRound();
            $rounds_exercises->battle_id = $battle->id;
            $rounds_exercises->exercise_id = $ex;
            $rounds_exercises->round = $index + 1;
            $rounds_exercises->save();
        }

        foreach ($request->user_ids as $user_id) {
            $ub = new UserBattle();
            $ub->user_id = $user_id;
            $ub->battle_id = $battle->id;
            $ub->save();

            $msg['en'] = 'You have an invitation for jogo battle by ' . Auth::user()->first_name;
            $msg['nl'] = 'Je hebt een uitnodiging voor jogo battle door ' . Auth::user()->first_name;
            Helper::processNotification(Auth::user()->id,$user_id,'battle/invite',$battle->id,'BattleInvite',$msg,$ub->user->badge_count,$ub->user->user_devices);

        }
        $battle = Battle::with(['user', 'rounds_exercises.exercise', 'players'])->find($battle->id);
        return Helper::apiSuccessResponse(true, 'Success', $battle ?? []);
    }

    public static function getUserNotificationObject($item)
    {
        $obj['id'] = $item->id;
        $obj['profile_picture'] = $item->from_user->profile_picture ?? "";
        $obj['description'] = $item->description;
        $obj['click_action'] = $item->click_action;
        $obj['model_type'] = $item->model_type;
        $obj['model_type_id'] = $item->model_type_id;
        $obj['role'] = $item->from_user->roles[0]->name ?? "";
        $obj['status'] = $item->status->name ?? "";
        $obj['created_at'] = $item->posted_at ?? "";
        if ($item->locale == 'nl') {
            $obj['created_at'] = str_replace('ago', 'geleden', $obj['created_at']);
        }

        return $obj;
    }

    public static function getTeamRequestObject($team_request)
    {
        $obj = new \stdClass();
        $obj->id = $team_request->id;
        $obj->player_id = $team_request->player->id;
        $obj->player_name = $team_request->player->first_name . ' ' . $team_request->player->last_name;
        $obj->profile_picture = $team_request->player->profile_picture;
        $obj->positions = $team_request->player->positions ?? [];
        $obj->team = $team_request->team->team_name ?? "";
        $obj->applied_team = $team_request->team->team_name ?? "";
        return $obj;
    }

    public static function completeExercise($pl_ex){
        $pl_ex_count = PlayerExercise::where('assignment_id', $pl_ex->assignment_id)
            ->where('user_id', $pl_ex->user_id)
            ->where('status_id', 3)
            ->distinct('assignment_id', 'user_id', 'exercise_id')
            ->count();

        $asign_ex_count = AssignmentExercise::where('assignment_id', $pl_ex->assignment_id)->count();

        if ($pl_ex_count >= $asign_ex_count) {
            $pl_as = PlayerAssignment::where('assignment_id', $pl_ex->assignment_id)
                ->where('player_user_id', $pl_ex->user_id)->first();

            $status = Status::where('name', 'completed')->first();

            if ($pl_as) {
                $pl_as->status_id = $status->id;
                $pl_as->save();
            }
        }

        return $pl_ex;
    }

    public static function getTeamPlayers($team,$request,$following = false){
        $players = $team->players()->paginate($request->limit ?? 5);
        $players_data = $players->values()->all();
        $players_meta = $players->toArray();
        $request->merge([
            "followings_ids" => $following ? Contact::where('user_id', Auth::user()->id)->pluck('contact_user_id')->toArray() : null
        ]);
        $players_data = GetTeamPlayerResourceListing::collection($players_data)->toArray($request);
        $team->players = $players_data;
        $data["data"] = $team;
        $data["meta"] = [
            "current_page" => $players_meta["current_page"],
            "next_page" => (int)(substr($players_meta["next_page_url"], -1)),
        ];

        return $data;
    }

    public function processNotification($from,$to,$model_type,$model_id,$click_action,$msg,$badge_count,$devices){
        $data = [];
        $data['from_user_id'] = $from;
        $data['to_user_id'] = $to;
        $data['model_type'] = $model_type;
        $data['model_type_id'] = $model_id;
        $data['click_action'] = $click_action;
        $data['message']['en'] = $msg['en'];
        $data['message']['nl'] = $msg['nl'];
        $data['message'] = json_encode($data['message']);
        $data['badge_count'] = $badge_count + 1;

        foreach ($devices as $device) {
            Helper::sendNotification($data, $device->onesignal_token, $device->device_type);
        }
    }

    public static function getResourceRecords($data,$request,JsonResource $resource){
        $records = $resource::collection($data)->toArray($request);

        if (count($records) > 0) {
            return ['status' => true, 'records' => $records];
        }
        else
        {
            return ['status' => false, 'msg' => 'No records found'];
        }
    }

    public static function getStatus($name){
        $status = Status::where('name', $name)
            ->first();

        return $status;
    }

    public static function postQuery(){
        $posts = Post::with(['author:id,first_name,last_name,profile_picture'])
            ->with(['comments' => function ($q1) {
                $q1->select('id', 'comment', 'created_at', 'contact_id', 'post_id');
                $q1->with('contact:id,first_name,last_name,profile_picture');
            }])
            ->with(['likes' => function ($q1) {
                $q1->select('id', 'contact_id', 'post_id');
                $q1->with('contact:id,first_name,last_name,profile_picture');
            }])
            ->withCount(['likes as user_likes_count' => function ($q) {
                $q->where('contact_id', Auth::user()->id);
            }]);

        return $posts;
    }

    public static function saveToken($request,$user_id){
        if ($request->device_type == 'ios') {
            $match_these['udid'] = $request->udid;
        } else {
            $match_these['imei'] = $request->imei;
        }

        $user_device = UserDevice::where($match_these)->first();

        if (!$user_device) {
            $user_device = new UserDevice();
        }

        $user_device->user_id = $user_id;
        $user_device->ip = $_SERVER['REMOTE_ADDR'];

        if ($request->device_type == 'ios') {
            $user_device->udid = $request->udid;
        } else {
            $user_device->imei = $request->imei;
        }

        $user_device->device_type = $request->device_type;
        $user_device->device_token = $request->device_token ?? null;
        $user_device->onesignal_token = $request->onesignal_token ?? null;

        $user_device->save();
    }

    public static function exerciseAttemptResponse($exercises_responses){
        $ex = [];
        $ex_id = [];
        $current_ex_id = 0;
        $ex_attempts = 0;
        for ($i = 0; $i < count($exercises_responses); $i++) {
            $current_ex_id = $exercises_responses[$i]->id;
            for ($j = 0; $j < count($exercises_responses); $j++) {
                if ($current_ex_id == $exercises_responses[$j]->id) {
                    $ex_attempts++;
                }
            }
            if (!in_array($exercises_responses[$i]->id, $ex_id)) {
                $ex_id[] = $exercises_responses[$i]->id;
                $exercises_responses[$i]->attempts = $ex_attempts;
                $ex[] = $exercises_responses[$i];
            }
            $ex_attempts = 0;
        }

        return $ex;
    }

    public static function updatePasswordRequest(){
        return [
            'currentPassword' => 'required|string|min:8|max:55',
            'newPassword' => 'required|string|min:8|max:55',
            'confirmPassword' => 'required|string|min:8|max:55|same:newPassword',
            'deviceType' => 'required|in:web|exists:user_devices,device_type,user_id,'.auth()->user()->id,
            'deviceToken' => 'nullable|exists:user_devices,device_token,user_id,'.auth()->user()->id,
            'ip' => 'required|exists:user_devices,ip,user_id,'.auth()->user()->id,
            'macId' => 'required|exists:user_devices,mac_id,user_id,'.auth()->user()->id
        ];
    }

    public static function playerListingResource(JsonResource $obj,$request){
        $resource = [
            "id"=> $obj->id,
            "first_name"=> $obj->first_name,
            "middle_name"=> $obj->middle_name,
            "last_name"=> $obj->last_name,
            "profile_picture"=>$obj->profile_picture,
            "current_player_id"=> auth()->user()->id,
            "follow_status"=> auth()->user()->id != $obj->id ?
                in_array($obj->id,$request->followings_ids) ? true : false
                : null
        ];

        return $resource;
    }

    public static function getAssignmentObject($ex){
        $obj = new \stdClass();
        $obj->id = $ex->id;
        $obj->title = $ex->title;
        $obj->completion_time = $ex->completion_time;
        $obj->thumbnail = $ex->thumbnail;
        $obj->video_file = $ex->video_file;
        $obj->created_at = $ex->created_at;
        return ['obj' => $obj];
    }

    public static function checkPost($ex,$player_id){
        $check_post = Post::select('id', 'level_id', 'exercise_id', 'post_title', 'created_at')
            ->where('exercise_id', $ex->id)
            ->where('level_id', $ex->level_id)
            ->where('author_id', $player_id)
            ->with('comments')->first();

        return ['status' => $check_post ? true : false, 'post' => $check_post];
    }

    public static function trainerResource($obj,$request,$is_owner = true){
        $resource = [
            'id' => $obj->id,
            'firstName' => $obj->first_name,
            'lastName' => $obj->last_name,
            'email' => $obj->email,
            'countryCode' => $obj->country_code ? (new CountryCodesResource($obj->country_code))->resolve() : new \stdClass(),
            'phoneNo' => $obj->phone,
            'teams' => count($obj->teams_trainers) > 0 ? TeamsListingResource::collection($obj->teams_trainers)->toArray($request) : [],
        ];

        if($is_owner){
            $resource['isOwner'] = $request->owner_id == $obj->id ? true : false;
        }

        return $resource;
    }
}