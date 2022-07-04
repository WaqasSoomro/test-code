<?php
namespace App\Http\Controllers\Api\App\PlayersSensorsSessions;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\PlayersSensorsSessions;
use App\Http\Requests\Api\App\PlayersSensorsSessions\IndexRequest;
use App\Http\Requests\Api\App\PlayersSensorsSessions\CreateRequest;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Client;


/**
    @group App / Player Sensor Sessions
*/

class IndexController extends Controller
{
    private $playersSensorsSessions;

    private $nodeAppModules = '';

    public function __construct()
    {
        $this->playersSensorsSessions = new PlayersSensorsSessions();

        $this->nodeAppModules = env('NODE_APP_MODULES', '');
    }

    /**
        Listing API

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Success",
            "Result": {
                "total_records": 4,
                "records": [
                    {
                        "id": 4,
                        "file": "media/players_sensors_sessions/Af2Pao4zHUiIG1wpOyf",
                        "created_at": "2022-04-19 10:46:05"
                    }
                ]
            }
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "limit": [
                    "The limit field is required."
                ]
            }
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @queryParam limit required integer. Example: 10
        @queryParam offset required integer. Example: 0
    */

    protected function index(IndexRequest $request)
    {
        $playersSensorsSessions = $this->playersSensorsSessions->playersSensorsSessionsListing($request);

        return $playersSensorsSessions;
    }

    /**
        Create API

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Success",
            "Result": {
                "player_id": 1,
                "file": "media/players_sensors_sessions/fSLQLbXDBv9DezTkY2rBmc2HL1tbIibXCHY9yStw.json",
                "updated_at": "2022-04-19 10:19:07",
                "created_at": "2022-04-19 10:19:07",
                "id": 3
            }
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "file": [
                    "The file field is required."
                ]
            }
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @bodyParam file file required file types: json
    */

    protected function create(CreateRequest $request)
    {
        $createPlayersSensorsSessions = $this->playersSensorsSessions->create($request);

        [$apiResponse, $responseBody] = $createPlayersSensorsSessions;
 
        try {
            $headers = [
                'Authorization' => $request->bearerToken(),
                'Content-Type' => 'application/json'
            ];
            
            $httpClient = new Client([
                'base_uri' => $this->nodeAppModules,
                'timeout' => 10.0
            ]);

            $payload = [
                "id" => $responseBody->id,
                "player_id" => $responseBody->player_id,
                "loggingInfo" => isset($responseBody->loggingInfo)
                    ? $responseBody->loggingInfo
                    : null
            ];

            $request = new GuzzleRequest(
                'POST',
                '/api/player-sessions/',
                $headers,
                json_encode($payload)
            );
            $response = $httpClient->send($request);
        } catch (\Exception $exc) {
        }

        return $apiResponse;
    }

    protected function getAggregated(Request $request) {
        try {
            $params = $request->all();
            
            $headers = [
                'Authorization' => $request->bearerToken(),
            ];
            
            
            $path = $this->nodeAppModules . '/api/player-sessions/?';
            
            $paramsToConcat = '';
            
            foreach($params as $paramKey => $paramValue) {
                $paramsToConcat .= "$paramKey=$paramValue&";
            }
            
            $path .= $paramsToConcat;

            return redirect($path);

            $httpClient = new Client([
                'base_uri' => $this->nodeAppModules,
                'timeout' => 15.0
            ]);

            $request = new GuzzleRequest(
                'GET',
                $path,
                $headers
            );

            $response = $httpClient->send($request);

            return $response;
        } catch (Exception $exc) {
            return '{}';
        }
    }
}