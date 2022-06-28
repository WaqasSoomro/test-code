<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\User;
use App\UserDevice;
use Matrix\Exception;
use stdClass;

/**
    * @group Dashboard / Device Token
    * API to save device token
*/

class DeviceTokenController extends Controller
{
    /**
     *  Set Device Token 
     *
     * @response {
     *"Response": true,
     *"StatusCode": 200,
     *"Message": "Success!",
     *"Result": {}
     *
     * }
     *
     *
     * @bodyParam  device_type string required options:web,ios,android
     * @bodyParam  device_token string required
     * @bodyParam  ip string required
     *
     * @return JsonResponse
     */

     public function setToken(Request $request )
     {
        $request->validate([
            'device_type' => 'required|in:web,ios,android',
            'device_token' => 'required',
            'ip' => 'required'
        ]);
        
        $user_device= UserDevice::where('user_id',auth()->user()->id)
        ->where('ip',$request->ip)
        ->where('device_type',$request->device_type)
        ->orderBy('created_at','desc')->first();

            $user_device->device_token = $request->device_token;
            $user_device->save(); 

        return Helper::apiSuccessResponse(true, 'Success!',new stdClass());

     }


     /**
      * Set OneSignal Token
      *
      * @response
      * {
     "Response": true,
     "StatusCode": 200,
     "Message": "Token Saved Successfully",
     "Result": {}
     }
      *
      * @bodyParam onesignal_token string required
      * @bodyParam ip string required
      * @bodyParam mac_id string required Mac Address
      */

     public function setOneSignalToken(Request $request)
     {
        $request->validate([
            "onesignal_token" =>"required|min:36|max:36",
            'ip' => 'required|exists:user_devices,ip',
            'mac_id' => 'required|exists:user_devices,mac_id'
        ]);

        try{
            $userDevice = UserDevice::whereUserIdAndOnesignalToken(\auth()->user()->id,$request->onesignal_token)->first();

            if ($userDevice) {
                return Helper::apiErrorResponse(false, "The Token Already Exists", new stdClass());
            }
            $userDevice = new UserDevice();

            $userDevice->device_type = "web";
            $userDevice->user_id = \auth()->user()->id;
            $userDevice->ip = $request->ip;
            $userDevice->onesignal_token = $request->onesignal_token;
            $userDevice->mac_id = $request->mac_id;

            if ($userDevice->save())
            {
                return Helper::apiSuccessResponse(true,"Token Saved Successfully", new stdClass());
            }

            return Helper::apiErrorResponse(false,"Couldn't Save Token", new stdClass());
        }
        catch (Exception $exception)
        {
            return Helper::apiErrorResponse(false,"Something Went Wrong",new stdClass());
        }

     }

}
