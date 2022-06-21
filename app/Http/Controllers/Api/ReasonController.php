<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\RequestReason;
use Exception;
use Illuminate\Http\Request;

class ReasonController extends Controller
{
    //
    public function equipment_reason()
    {

        try{



            $response = RequestReason::where("status","active")
                        ->where("is_public", "1")
                        ->select("id", "display_name as title")
                        ->get();

            return Helpers::get_http_response(true,$response, Helpers::HTTP_OK, 'success');
            //  return response()->json(['user' => $user], 200);

        }catch(Exception $ex) {

            return Helpers::get_http_response(false, [], Helpers::HTTP_CREATED, $ex->getMessage());

        }
    }
}
