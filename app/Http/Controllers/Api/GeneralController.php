<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\LanguageMlv;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    //

    function get_localization_data() {

        $lang = "en";

        if($lang == null){
            return Helpers::get_http_response( false, [], Helpers::HTTP_CREATED, "invalid_parameters" );

        }

        $l = [];



        if($lang == "0"){
            $l = Language::where('status',1)->get();

        }else{
            $l = Language::where('status',1)->where('code',$lang)->get();
        }

        if(count($l) == 0){

            return Helpers::get_http_response( false, [], Helpers::HTTP_CREATED, "invalid_parameters" );

        }

        $return_data = new \stdClass;


        foreach($l as $l_v){


            $return_data->{$l_v->code} = new \stdClass;

            $lmlv = LanguageMlv::leftjoin('language_keys','language_mlvs.language_key_id','language_keys.id')
            ->where('language_id',$l_v->id)
            ->where('language_mlvs.status',1)
            ->where('language_keys.status',1)
            ->select(
                'language_mlvs.value',
                'language_keys.key'
            )
            ->get();

            foreach($lmlv  as $lmlv_v){
                $return_data->{$l_v->code}->{$lmlv_v->key} = $lmlv_v->value;

            }

        }


        return Helpers::get_http_response( true, ["localization"=>$return_data], Helpers::HTTP_CREATED, "success" );




    }

    function get_setting_data() {


        $return_data = [];

        return Helpers::get_http_response( true, ["setting"=>$return_data], Helpers::HTTP_CREATED, "success" );




    }


}
