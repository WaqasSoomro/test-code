<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\SensorModule;
use stdClass;
use Illuminate\Support\Facades\{
    DB,
    Storage,
};
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Client;

/**
 * @group Sensor Module
 */

class SensorController extends Controller
{
    private $nodeAppModules = '';
    /**
     * Post Sensor Detail
     * 
     * @response {
     *  "Response": true,
     *  "StatusCode": 200,
     *  "Message": "Successful",
     *  "Result": {}
     *}
     * @bodyParam version_number string required 
     * @bodyParam file file required 
     */

    public function __construct () {
        $this->nodeAppModules = env('NODE_APP_MODULES', '');
    }

    public function postSensorDetail(Request $request)
    {
        Validator::make($request->all(), [
            'version_number' => 'required',
            'file' => 'required|mimes:zip'
        ])->validate();
    
        $dfu_file = "";
        if ($request->hasFile('file')) 
        {
            //$file = Storage::putFile(SensorModule::$media, $request->file);
            $data = $request->file('file');
            $dfu_file = SensorModule::$media . $data->getClientOriginalName();
            $fileData = file_get_contents($data);
            // fileData = json_decode(file_get_contents($data), true);$
            Storage::put($dfu_file, $fileData);
            $file = explode("sensor_module/", $dfu_file);
        }
        
        if($dfu_file == "")
        {
            return Helper::apiNotFoundResponse(false, 'Failed to upload file', new stdClass());
        }

        $sensor_module = new SensorModule();
        $sensor_module->version_number = $request->version_number;
        $sensor_module->file = $file[1] ?? null;
        $sensor_module->save();

        if(!$sensor_module->save())
        {
            return Helper::apiErrorResponse(false, 'Unsuccessful', new stdClass());
        }
        else
        {
            return Helper::apiSuccessResponse(true, 'Successful', new stdClass());
        }
        

    }

   /**
    * Get File
    * 
    * @response {
    * "Response": true,
    * "StatusCode": 200,
    * "Message": "Download Link Provided",
    * "Result": "https://jogobucket-1.s3.eu-west-2.amazonaws.com/sensor_module/test.zip?X-Amz-Content-Sha256=UNSIGNED-PAYLOAD&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIAW2RPSZR2OFELRRU2%2F20211118%2Feu-west-2%2Fs3%2Faws4_request&X-Amz-Date=20211118T075123Z&X-Amz-SignedHeaders=host&X-Amz-Expires=600&X-Amz-Signature=a4dc83cd559bbee10c06ef5f0a6a06f8fdec23254cb7afc193453e6a2326b6ab"
    *  }
    *
    * @queryParam version_number required string
    */
    public function getFile(Request $request)
    {   
        Validator::make($request->all(), [
        'version_number' => 'required'
        ])->validate();
        
        $record = SensorModule::orderBy('created_at','desc')->first();
        $res = version_compare($request->version_number,$record->version_number);
        if($res == -1)
        {
            
            $url = Storage::temporaryUrl(
                SensorModule::$media . $record->file, now()->addMinutes(10)
            );
            return Helper::apiSuccessResponse(true, 'Download Link Provided', $url);
        }
        else
        {
            return Helper::apiSuccessResponse(true, 'Already upto date', new stdClass());
        }

    }

    public function sensorLoggingInfo(Request $request) {
        try {
            $name = $request->all()["name"];
        
            $headers = [
                'Authorization' => $request->bearerToken(),
                'Content-Type' => 'application/json'
            ];
            
            $httpClient = new Client([
                'base_uri' => $this->nodeAppModules,
                'timeout' => 10.0
            ]);

            $useUri = '/api/sensor-logging-info/?name=' . $name;

            $request = new GuzzleRequest(
                'GET',
                $useUri,
                $headers
            );

            $response = $httpClient->send($request);

            return $response;
        } catch (Exception $exc) {
            return Helper::apiErrorResponse(false, 'Logging info could not be retreived', new stdClass());
        }
    }

}
