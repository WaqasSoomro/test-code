<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\User;
use App\PasswordReset;
use App\Activitylog;
use App\Helpers\Helper;
use stdClass;


/**
 * @group  Password Reset
 * APIs for resetting password.
 * User email is required
 */


class PasswordResetController extends Controller
{



    /**
     * Password Reset Create Api
     *
     * Paasword Reset will be sent in an email , email field is required
     *
     * @queryParam  email required Esmail of the user is required
     * @response  {
     *         "Response": true,
     *         "StatusCode": 200,
     *         "Message": "We have e-mailed your password reset link!",
     *              "Result": {
     *                       "id": 1,
     *                       "first_name": "Fahad",
     *                       "middle_name": "Ahmed",
     *                       "last_name": "Khan",
     *                       "email": "fahadahmedoptimist@gmail.com",
     *                       "phone": "012445435342324",
     *                       "gender": "Male",
     *                       "date_of_birth": "1994-08-23 00:00:01",
     *                       "address": "Flat no 123",
     *                        "language": null,
     *                       "profile_picture": null,
     *                       "active": 1,
     *                       "verification_code": null,
     *                       "email_verified_at": "2020-06-26 12:49:21",
     *                       "status_id": "1",
     *                        "created_at": "2020-06-26 12:45:42",
     *                       "updated_at": "2020-06-29 10:51:01",
     *                       "deleted_at": null
     *                   }
     *             }
     * @response 401 {
     *                    "Response": false,
     *                    "StatusCode": 401,
     *                    "Message": "We can't find a user with that e-mail address (fahadahmedoptimist@gmail.comsss)",
     *                    "Result": {}
     *                }
     * @response 422 {
     *       "Response": false,
     *       "StatusCode": 422,
     *       "Message": "Invalid Parameters",
     *       "Result": {
     *           "email": [
     *               "The email field is required."
     *           ]
     *       }
     *   }
     *
     */



 	public function create(Request $request)
    {

        $request->validate([
            'email' => 'required|string|email',
        ]);
        $user = User::where('email', $request->email)->first();
     	$current_date_time = Carbon::now()->toDateTimeString();

        if (!$user){


			/**
			*   Activity logs is used to record your actions & activities
			**/

			//$activitylog_input['user_id'] = trim($user->id);
			$activitylog_input['activity'] = 'We can not find a user with that e-mail address'. $request->email;
			$activitylog_input['created_at'] = $current_date_time;
			$activitylog_input['updated_at'] = $current_date_time;
			//$activity_log_user = Activitylog::create($activitylog_input);



			$response['Response'] = false;
			$response['StatusCode'] = 404;
			$response['Message'] = 'We can not find a user with that e-mail address.';
			$response['Result'] =  array(
				"user_email" => $request->email,
				"Detailed Message" => "We can't find a user with that e-mail address.",
			);

			$message = "We can't find a user with that e-mail address (".$request->email.")";
            return Helper::apiUnAuthenticatedResponse(false,$message, new stdClass());
			//return response()->json($response, 404);

        }



        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Str::random(60)
             ]
        );
        if ($user && $passwordReset){

            $user->notify(
                new PasswordResetRequest($passwordReset->token)
            );


            /**
			*   Activity logs is used to record your actions & activities
			**/

			$activitylog_input['user_id'] = trim($user->id);
			$activitylog_input['activity'] = 'We have e-mailed your password reset link!';
			$activitylog_input['created_at'] = $current_date_time;
			$activitylog_input['updated_at'] = $current_date_time;
			//$activity_log_user = Activitylog::create($activitylog_input);



            $response['Response'] = true;
			$response['StatusCode'] = 202;
			$response['Message'] = 'We have e-mailed your password reset link!';
			$response['Result'] =  array(
				"user_email" => $request->email,
				"Detailed Message" => "We have e-mailed your password reset link!",
			);

            return Helper::apiSuccessResponse(true, "We have e-mailed your password reset link!",  $user);


        }



    }

    /**
     * Password Token Find Api
     *
     * Paasword Token Api will be sent in an email ,  where user will verify  an email
     *
     * @urlParam  token required The token of the reset password where user gets it in an email .
     *
     * @response {
     *       "Response": true,
     *       "StatusCode": 200,
     *       "Message": "Token is found and verified from fahadahmedoptimist@gmail.com",
     *       "Result": {}
     *   }
     * @response 401   {
     *       "Response": false,
     *       "StatusCode": 401,
     *       "Message": "The password reset token is invalid",
     *       "Result": {}
     *   }
     * @response 401   {
     *       "Response": false,
     *       "StatusCode": 401,
     *       "Message": "TThe password reset token is invalid because it exceeds the time limit to approves",
     *       "Result": {}
     *   }
     */

    public function find($token)
	{

		$current_date_time = Carbon::now()->toDateTimeString();
		$passwordReset = PasswordReset::where('token',$token)->first();

		if (!$passwordReset) {

			/**
			*   Activity logs is used to record your actions & activities
			**/

			//$activitylog_input['user_id'] = trim($user->id);
			$activitylog_input['activity'] = 'The password reset token is invalid';
			$activitylog_input['created_at'] = $current_date_time;
			$activitylog_input['updated_at'] = $current_date_time;
			//$activity_log_user = Activitylog::create($activitylog_input);


			$response['Response'] = true;
			$response['StatusCode'] = 404;
			$response['Message'] = 'The password reset token is invalid';
			$response['Result'] =  array(

				"Detailed Message" => "The password reset token is invalid",
			);

            $message = "The password reset token is invalid";
            return Helper::apiUnAuthenticatedResponse(false,$message, new stdClass());
			//return response()->json($response, 404);

		}

		if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast() )
		{

			$passwordReset->delete();

			/**
			*   Activity logs is used to record your actions & activities
			**/

			//$activitylog_input['user_id'] = trim($user->id);
			$activitylog_input['activity'] = 'The password reset token is invalid because it exceeds the time limit to approve';
			$activitylog_input['created_at'] = $current_date_time;
			$activitylog_input['updated_at'] = $current_date_time;
			//$activity_log_user = Activitylog::create($activitylog_input);


			$response['Response'] = true;
			$response['StatusCode'] = 404;
			$response['Message'] = 'The password reset token is invalid because it exceeds the time limit to approve';
			$response['Result'] =  array(
				"user_email" => $passwordReset->email,
				"Detailed Message" => 'The password reset token is invalid because it exceeds the time limit to approve',
			);

            $message = 'The password reset token is invalid because it exceeds the time limit to approve';
            return Helper::apiUnAuthenticatedResponse(false,$message, new stdClass());
			//return response()->json($response, 404);

		}


		/**
		*   Activity logs is used to record your actions & activities
		**/

		//$activitylog_input['user_id'] = trim($user->id);
		$activitylog_input['activity'] = 'Token is found and verified from '.$passwordReset->email;
		$activitylog_input['created_at'] = $current_date_time;
		$activitylog_input['updated_at'] = $current_date_time;
		//$activity_log_user = Activitylog::create($activitylog_input);


		$response['Response'] = true;
		$response['StatusCode'] = 202;
		$response['Message'] = 'Token is found and verified from '.$passwordReset->email;
		$response['Result'] =  array(
			"user_email" => $passwordReset->email,
			"Detailed Message" => $passwordReset,
		);

        $message =  'Token is found and verified from '.$passwordReset->email;
        return Helper::apiSuccessResponse(true, $message,  new stdClass());

	}




	/**
     * Reset password
     *
     * @queryParam email required Email is required
     * @queryParam password required Password is required
     * @queryParam password_confirmation required This field is required for matching
     * @queryParam token required Token is required for changing the password
     *
     * @response {
     *       "Response": true,
     *       "StatusCode": 200,
     *       "Message": "Password is changed successfully",
     *       "Result": {}
     *   }
     * @response 422 {
     *           "Response": false,
     *           "StatusCode": 422,
     *           "Message": "Invalid Parameters",
     *           "Result": {
     *               "email": [
     *                   "The email field is required."
     *               ],
     *               "password": [
     *                   "The password field is required."
     *               ],
     *               "token": [
     *                   "The token field is required."
     *               ]
     *           }
     *       }
     * @response 422 {
     *           "Response": false,
     *           "StatusCode": 422,
     *           "Message": "Invalid Parameters",
     *           "Result": {
     *               "token": [
     *                   "The token field is required."
     *               ]
     *           }
     *       }
     * @response 422 {
     *       "Response": false,
     *       "StatusCode": 422,
     *       "Message": "Invalid Parameters",
     *       "Result": {
     *           "password": [
     *               "The password confirmation does not match."
     *           ]
     *       }
     *   }
     * @response 401   {
     *       "Response": false,
     *       "StatusCode": 401,
     *       "Message": "This password reset token is invalid. Unable to reset!",
     *       "Result": {}
     *   }
     */
    public function reset(Request $request)
    {
    	$current_date_time = Carbon::now()->toDateTimeString();

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);
        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();

        if (!$passwordReset){


			/**
			*   Activity logs is used to record your actions & activities
			**/

			//$activitylog_input['user_id'] = trim($user->id);

			$activitylog_input['activity'] = 'This password reset token is invalid. Unable to reset!';
			$activitylog_input['created_at'] = $current_date_time;
			$activitylog_input['updated_at'] = $current_date_time;
			//$activity_log_user = Activitylog::create($activitylog_input);


			$response['Response'] = false;
			$response['StatusCode'] = 404;
			$response['Message'] = 'This password reset token is invalid. Unable to reset!';
			$response['Result'] =  array(
				"Detailed Message" => "This password reset token is invalid. Unable to reset!",
			);

            $message = 'This password reset token is invalid. Unable to reset!';
            return Helper::apiUnAuthenticatedResponse(false,$message, new stdClass());


        }



        $user = User::where('email', $passwordReset->email)->first();
        if (!$user){

        	/**
			*   Activity logs is used to record your actions & activities
			**/

			//$activitylog_input['user_id'] = trim($user->id);

			$activitylog_input['activity'] = 'We cant find a user with that e-mail address.';
			$activitylog_input['created_at'] = $current_date_time;
			$activitylog_input['updated_at'] = $current_date_time;
			//$activity_log_user = Activitylog::create($activitylog_input);


			$response['Response'] = false;
			$response['StatusCode'] = 404;
			$response['Message'] = ' We cant find a user with that e-mail address.';
			$response['Result'] =  array(
				"Detailed Message" => "We can't find a user with that e-mail address.",
			);

            $message = 'We cant find a user with that e-mail address.';
            return Helper::apiUnAuthenticatedResponse(false,$message, new stdClass());

        }

        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->delete();
        $user->notify(new PasswordResetSuccess($passwordReset));



		/**
		*   Activity logs is used to record your actions & activities
		**/

		$activitylog_input['user_id'] = trim($user->id);
		$activitylog_input['activity'] ='Password is changed successfully';
		$activitylog_input['created_at'] = $current_date_time;
		$activitylog_input['updated_at'] = $current_date_time;
		//$activity_log_user = Activitylog::create($activitylog_input);


		$response['Response'] = true;
		$response['StatusCode'] = 202;
		$response['Message'] ='Password is changed successfully';
		$response['Result'] =  array(
			"Detailed Message" => "Password is changed successfully",
			"user" => $user
		);

        $message =  'Password is changed successfully';
        return Helper::apiSuccessResponse(true, $message, new stdClass());

    }






}
