<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\RequestEquipment;
use App\Models\RequestReason;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EquipmentController extends Controller
{
    //

    public function user_equipment_issue()
    {

        try{

            $response = [];

            $response_tem = [
                "Laptop" => "Dell 7860",
                "Mouse" => "Wireless",
                "Laptop" => "Dell 7860",
                "Mobile" => "Yes",

            ];

            foreach($response_tem as $k => $v){

                $tem = new \stdClass;
                $tem->type = $k;
                $tem->name = $v;

                $response[] =$tem;

            }



            return Helpers::get_http_response(true,$response, Helpers::HTTP_OK, 'success');
            //  return response()->json(['user' => $user], 200);

        }catch(Exception $ex) {

            return Helpers::get_http_response(false, [], Helpers::HTTP_CREATED, $ex->getMessage());

        }
    }

    public function equipment_category()
    {

        try{



            $response = EquipmentCategory::where("status","active")
                        ->where("is_public", "1")
                        ->select("id", "display_name as title")
                        ->get();

            return Helpers::get_http_response(true,$response, Helpers::HTTP_OK, 'success');
            //  return response()->json(['user' => $user], 200);

        }catch(Exception $ex) {

            return Helpers::get_http_response(false, [], Helpers::HTTP_CREATED, $ex->getMessage());

        }
    }
    public function equipment_data()
    {

        try{

            $response = new \stdClass;

            $response->equipment_category = EquipmentCategory::where("status","active")
                        ->where("is_public", "1")
                        ->select("id", "display_name as title")
                        ->get();

            $response->equipment_reason = RequestReason::where("status","active")
                        ->where("is_public", "1")
                        ->select("id", "display_name as title")
                        ->get();



            $response->priorities = [];

            $priorities_tem = [
                "high" => "High",
                "moderate" => "Moderate",
                "low" => "Low",

            ];

            foreach($priorities_tem as $k => $v){

                $tem = new \stdClass;
                $tem->id = $k;
                $tem->title = $v;

                $response->priorities[] =$tem;

            }


            return Helpers::get_http_response(true,$response, Helpers::HTTP_OK, 'success');
            //  return response()->json(['user' => $user], 200);

        }catch(Exception $ex) {

            return Helpers::get_http_response(false, [], Helpers::HTTP_CREATED, $ex->getMessage());

        }
    }

    public function equipment_request(Request $request)
    {

        try{


            $validator = Validator::make($request->all(), [
                'equipment_category_id' => 'required',
                'equipment_reason_id' => 'required',
                'priority' => 'required|in:high,moderate,low',
            ]);


            if ($validator->fails()) {

                return Helpers::get_http_response(false, [], Helpers::HTTP_UNAUTHORIZED, $validator->errors());
            }



            $re = new RequestEquipment();
            $re->equipment_category_id = $request->equipment_category_id;
            $re->reason_id = $request->equipment_reason_id;
            $re->priority = $request->priority;
            $re->user_id = auth()->user()->id;
            $re->status = "pending";
            $re->save();




            return Helpers::get_http_response(true,[], Helpers::HTTP_OK, 'success');
            //  return response()->json(['user' => $user], 200);

        }catch(Exception $ex) {

            return Helpers::get_http_response(false, [], Helpers::HTTP_CREATED, $ex->getMessage());

        }
    }


    public function equipment_request_history()
    {

        try{




           $response = RequestEquipment::
                        leftjoin("equipment_categories","request_equipment.equipment_category_id","equipment_categories.id")
                        ->where("user_id", auth()->user()->id)
                        ->select(
                            "request_equipment.id"
                            ,"equipment_categories.display_name as title"
                            ,"request_equipment.status"
                            ,"request_equipment.created_at as date"
                            )
                        ->get();

            return Helpers::get_http_response(true,$response, Helpers::HTTP_OK, 'success');
            //  return response()->json(['user' => $user], 200);

        }catch(Exception $ex) {

            return Helpers::get_http_response(false, [], Helpers::HTTP_CREATED, $ex->getMessage());

        }
    }



}
