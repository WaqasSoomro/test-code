<?php

namespace App\Http\Controllers\Api;

use App\Contact;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass;
use Validator;

/**
 * @authenticated
 * @group  Contacts Management
 * APIs for managing contacts, adding other users in a list as a friend,follower,other player.
 * User Auth Token is required in headers
 */
class ContactController extends Controller
{

    /**
     *
     * Contact Api
     *
     * You can store contacts and you need his/her id from users table
     *
     * @queryParam  contact_user_id required , Contact id from the user's table
     *
     * @response {
     *               "Response": true,
     *               "StatusCode": 202,
     *               "Message": "Record has been saved",
     *               "Result": {
     *                       "user_id": 2,
     *                       "contact_user_id": "1",
     *                       "updated_at": "2020-06-29 08:12:16",
     *                       "created_at": "2020-06-29 08:12:16",
     *                       "id": 2
     *                   }
     *               }
     *
     * @response 401   {
     *                       "Response": false,
     *                       "StatusCode": 401,
     *                       "Message": "Contact UserID can't be added again. It's already defined in database.",
     *                       "Result": {}
     *                   }
     * @response 401 {
     *                       "Response": false,
     *                       "StatusCode": 401,
     *                       "Message": {
     *                           "contact_user_id": [
     *                               "The selected contact user id is invalid."
     *                           ]
     *                       },
     *                       "Result": {}
     *                   }
     * @response 401 {
     *                       "Response": false,
     *                       "StatusCode": 401,
     *                       "Message": "contact_user_id field is empty or not set",
     *                       "Result": {}
     *                   }
     *
     */


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Contact::$rules);
        if ($validator->fails()) {
            return Helper::apiUnAuthenticatedResponse(false, $validator->errors(), new stdClass());
        }

        $user_info = $request->user();
        $current_user_id = $user_info->id;
        $request->request->add(['user_id' => $current_user_id]); //add request

        /*
        * Finding that current user id & contact user id is already there in a row
        **/
        $contact_result = Contact::where('user_id', $current_user_id)->where('contact_user_id', $request->contact_user_id)->get();

        if (count($contact_result) > 0) {
            return Helper::apiUnAuthenticatedResponse(false, "Contact UserID can't be added again. It's already defined in database.", new stdClass());
        }

        $contact = Contact::find($request->id);

        if (!$contact) {
            $contact = new Contact();
        }

        $response = $contact->store($request);
        if ($response) {
            return Helper::apiSuccessResponse(true, 'Record has been saved', new stdClass());
        }
        return Helper::apiUnAuthenticatedResponse(false, 'Failed to save record', new stdClass());
    }


    public function show()
    {

        //return Auth::user();
        $contacts = Auth::user()->with('Contacts')->get()->toArray();//To get the output in array
        /*        ^               ^
         This will get the user | This will get all the Orders related to the user*/

        return Helper::apiSuccessResponse(true, 'Get all records from contacts', $contacts);
        // return response()->json($contacts);

    }
}
