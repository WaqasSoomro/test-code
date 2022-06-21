<?php


namespace App\Helpers;

use App\Http\Controllers\api\v1\AuthController;
use App\LanguageMlv;
use App\Models\LanguageMlv as ModelsLanguageMlv;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Mail;

class Helpers
{

    const HTTP_OK = Response::HTTP_OK;
    const HTTP_CREATED = Response::HTTP_CREATED;
    const HTTP_UNAUTHORIZED = Response::HTTP_UNAUTHORIZED;
    const HTTP_NOT_FOUND = Response::HTTP_NOT_FOUND;


    static function get_HTTP_OK(){

        return self::HTTP_OK;
    }

    static function get_http_response( $status = null, $data = null, $response,$message = null ){

        $message_tem = "";

        if(is_object($message)){

            foreach($message->getMessages() as $k => $v){


                $message_tem .= $k.":";
                foreach($v as $m){

                    $message_tem .= $m;
                }
            }

        }else{

            $message_tem = $message;
        }


        return response()->json([

            'response' => $status,
            'result' => $data,
            'message' => $message_tem

        ], $response);
    }

    static function get_response( $status = null, $data = null, $response,$message = null ){

        $message_tem = "";

        if(is_object($message)){

            foreach($message->getMessages() as $k => $v){


                $message_tem .= $k.":";
                foreach($v as $m){

                    $message_tem .= $m;
                }
            }

        }else{

            $message_tem = $message;
        }


        return response()->json([

            'Status' => 0,
            'message' => $message_tem

        ], $response);
    }

    static function get_user_token( $user, string $token_name = null ) {

        return $user->createToken($token_name)->accessToken;

    }

    static function HttpRequest($url,$type="GET",$data = [], $token = null ,$header=  ['Content-type' => 'application/json;charset=UTF-8']){


        if($token != null){

            $response = Http::withToken($token)
            ->withHeaders($header);
        }else{
            $response = Http::withHeaders($header);
        }


        switch($type){

            case "GET":
                $response = $response->GET($url);
            break;
            case "PUT":
                $response = $response->PUT($url,$data);
            break;
            case "POST":
                $response = $response->POST($url,$data);
            break;
            case "PATCH":
                $response = $response->PATCH($url,$data);
            break;
            case "DELETE":
                $response = $response->DELETE($url);
            break;
        }

        return json_decode($response, true);
    }

    static function add_custom_logs($logs_name,$message,$type= "info"){

        $register = new Logger($logs_name);
        $register->pushHandler(new StreamHandler(storage_path('logs/laravel-'.$logs_name.'-' . date('Y-m-d') . '.log')), Logger::INFO);
        $register->{$type}($message);
    }

    static function CurlRequest($url,$type="GET",$post_data,$curl_ssl = null,$headers = null){

        try{

            $curl = curl_init();
            $curl_option = [];
            $curl_option[CURLOPT_URL] = $url;

            if($curl_ssl != null){
                $curl_option[CURLOPT_SSL_VERIFYPEER] = $curl_ssl;
                $curl_option[CURLOPT_SSL_VERIFYHOST] = $curl_ssl;

            }

            if($type=="POST"){
                $curl_option[CURLOPT_POST] = true;
                $curl_option[CURLOPT_POSTFIELDS] = $post_data;
            }

            if($type=="PUT"){
                $curl_option[CURLOPT_CUSTOMREQUEST] = $type;
                $curl_option[CURLOPT_POSTFIELDS] = $post_data;
            }


            if($headers != null){

                $curl_option[CURLOPT_HTTPHEADER] = $headers;
                $curl_option[CURLINFO_HEADER_OUT] = true;
            }



            curl_setopt_array($curl,$curl_option);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            $error = '';

            if(!$response) {
                $error = curl_error($curl);
            }

            curl_close($curl);

            return ["response"=>$response,"error"=>$error];

        }catch(Exception $ex) {

            return ["response"=>[],"error"=>$ex->getMessage()];
        }

    }

    static function check_local()
    {
        if(
            strpos(URL::current(), '.localhost/')
        ){

            return true;

        }

        return false;

    }

    static function redirect_404_page()
    {
        return redirect(url('/404'));
    }

    static function get_language_key_value($key,$lang_id=1) {

        $lmlv = ModelsLanguageMlv::leftjoin("language_keys","language_keys.id","language_mlvs.language_key_id")
        ->where("language_mlvs.language_id",$lang_id)
        ->where("language_keys.key",$key)
        ->first();

        if(is_null($lmlv)){

            return "";
        }

        return $lmlv->value;

    }


}
