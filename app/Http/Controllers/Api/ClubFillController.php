<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use App\Club;

/**
 * @group Dashboard V4 / Clubs
 */
class ClubFillController extends Controller
{
    /**
        Club Auto Fill

        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "success",
            "Result": []
        }
    **/
    

    public function clubFill(Request $request)
    {
        $file = public_path('clubs.csv');

        $clubArr = $this->csvToArray($file);
        
        for ($i = 0; $i < count($clubArr); $i ++)
        {
            $club = new Club();
            $club->title = $clubArr[$i]["clubName"]??null;
            $club->website = $clubArr[$i]["clubWebsite"]??null;
            $club->email = $clubArr[$i]["clubEmail"]??null;
            $club->city = $clubArr[$i]["clubCity"]??null;
            $club->clubID = $clubArr[$i]["clubID"]??null;
            $club->phone = $clubArr[$i]["clubPhone"]??null;
            $club->save();

        }
        if(!$clubArr)
        {
            return Helper::apiErrorResponse(false, 'file or data not found',new \stdClass());
        }
        return Helper::apiSuccessResponse(true, 'success',[]);

    }

    function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;
    
        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
            {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
    
        return $data;
    }
}
