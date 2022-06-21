<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use stdClass;
use Exception;

class PassportAuthController extends Controller
{
    //


    /**
     * Registration Req
     */
    // public function register(Request $request)
    // {
    //     $this->validate($request, [
    //         'name' => 'required|min:4',
    //         'email' => 'required|email',
    //         'password' => 'required|min:8',
    //     ]);

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => bcrypt($request->password)
    //     ]);

    //     $token = $user->createToken(config('app.mailgun_password'))->accessToken;

    //     return response()->json(['token' => $token], 200);
    // }

    /**
     * Login Req
     */
    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($data)) {


            $token = Helpers::get_user_token(auth()->user(),config('app.PASSPORT_AUTH_KEY'));
            // $token = auth()->user()->createToken(config('app.PASSPORT_AUTH_KEY'))->accessToken;
            // $token = auth()->user()->createToken('Laravel8PassportAuth');

            // dd($token['token']);
            // $token = $token['token'];

            return Helpers::get_http_response(true,['token' => $token], Helpers::HTTP_OK, 'success');

            // return response()->json(['token' => $token], 200);
        } else {

            return Helpers::get_http_response(false, [], 401, 'login_credential_failed');
            // return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function forgot_password(Request $request) {

        try{


            $validator = Validator::make($request->all(), [
                'email' => 'required',
            ]);


            if ($validator->fails()) {

                return Helpers::get_http_response(false, [], Helpers::HTTP_UNAUTHORIZED, $validator->errors());
            }

            $user = User::where("email",$request->email)
                    ->first();

            if(is_null($user)){
                return Helpers::get_http_response(false, [], Helpers::HTTP_CREATED,"Email not found!");

            }

            return Helpers::get_http_response(true,[], Helpers::HTTP_OK, 'success');
            //  return response()->json(['user' => $user], 200);

        }catch(Exception $ex) {

            return Helpers::get_http_response(false, [], Helpers::HTTP_CREATED, $ex->getMessage());

        }

    }

    public function logout(Request $request) {
        $request->user()->token()->revoke();



        return Helpers::get_http_response(true,[], Helpers::HTTP_OK, 'success');

    }
}
