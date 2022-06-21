<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

class UserController extends Controller
{
    //


    public function user_info()
    {


        try{


            $user = auth()->user()->only(['id',"name","email","image","designation"]);





            $equipment_issue = [];

            $equipment_issue_tem = [
                "Laptop" => "Dell 7860",
                "Mouse" => "Wireless",
                "Laptop" => "Dell 7860",
                "Mobile" => "Yes",

            ];

            foreach($equipment_issue_tem as $k => $v){

                $tem = new \stdClass;
                $tem->type = $k;
                $tem->name = $v;

                $equipment_issue[] =$tem;

            }


            $tem = new \stdClass();
            $tem->id = 1;
            $tem->title = "employee";

            $user["user_type"] = [$tem];

            if($user["image"] == null){

                $user["image"] = url('/images/default-profile-image.png');
            }


            return Helpers::get_http_response(true,['user' => $user,"equipment_issue"=> $equipment_issue], Helpers::HTTP_OK, 'success');
            //  return response()->json(['user' => $user], 200);


        }catch(Exception $ex) {

            return Helpers::get_http_response(false, [], Helpers::HTTP_CREATED, $ex->getMessage());

        }


    }
}
