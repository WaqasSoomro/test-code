<?php
namespace App\Http\Controllers\Api\Dashboard\General;
use App\Http\Controllers\Controller;
use App\Club;
use App\Country;
use App\Language;
use App\Position;
use App\Line;
use Illuminate\Http\Request;

/**
    @group Dashboard V4 / General
*/
        
class IndexController extends Controller
{
    private $clubsModel, $countriesModel, $languagesModel, $positionModel, $lineModel, $positionColumns, $lineColumns, $limit, $sockets, $sortingColumn, $sortingType, $status;

    public function __construct(Request $request)
    {
        $this->clubsModel = Club::class;

        $this->countriesModel = Country::class;
        
        $this->languagesModel = Language::class;

        $this->positionModel = Position::class;

        $this->lineModel = new Line();

        $this->positionsColumns = (new $this->positionModel)->generalColumns();

        $this->lineColumns = [
            'id',
            'name'
        ];

        $this->limit = $request->limit ?? 10;

        $this->offset = $request->offset ?? 0;

        $this->sortingColumn = 'created_at';

        $this->sortingType = 'desc';

        $this->status = ['active'];
    }

    /**
        Clubs listing
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found",
            "Result": [
                {
                    "id": 1,
                    "name": "CLub One"
                }
            ]
        }
        
        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "No records found",
            "Result": []
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": []
        }
    */

    protected function index(Request $request)
    {
        $response = (new $this->clubsModel)->viewClubs($request);

        return $response;
    }

    /**
        Countries listing
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found",
            "Result": [
                {
                    "id": 1,
                    "name": "Afghanistan",
                    "countryCode": "iso",
                    "phoneCode": 93
                }
            ]
        }
        
        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "No records found",
            "Result": []
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": []
        }
    */

    protected function countries(Request $request)
    {
        $response = (new $this->countriesModel)->viewCountries($request);

        return $response;
    }

    /**
        Country Codes listing
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found",
            "Result": [
                {
                    "id": 164,
                    "code": 92
                }
            ]
        }
        
        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "No records found",
            "Result": []
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": []
        }
    */

    protected function phoneCodes(Request $request)
    {
        $response = (new $this->countriesModel)->viewCountryCodes($request);

        return $response;
    }

    /**
        Languages listing
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found",
            "Result": [
                {
                    "id": 1,
                    "name": "English"
                }
            ]
        }
        
        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "No records found",
            "Result": []
        }
        
        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": []
        }
    */

    protected function languages(Request $request)
    {
        $response = (new $this->languagesModel)->viewLanguages($request);

        return $response;
    }

    /**
        Players positions listing

        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found successfully",
            "Result": [
                {
                    "id": 1,
                    "name": "Left Back"
                }
            ]
        }

        @response 500
        {
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": []
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "No records found",
            "Result": []
        }
    **/
        
    protected function positions(Request $request)
    {
        $response = (new $this->positionModel)->viewPositions($request, $this->positionsColumns, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType, $this->status);

        return $response;
    }

    /**
        Players lines listing

        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found successfully",
            "Result": [
                {
                    "id": 1,
                    "name": "Left Back"
                }
            ]
        }

        @response 500
        {
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": []
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "No records found",
            "Result": []
        }
    **/
        
    protected function lines(Request $request)
    {
        $response = $this->lineModel->viewLines($request, $this->lineColumns, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType);

        return $response;
    }
}