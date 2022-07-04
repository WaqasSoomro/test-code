<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Club;
use App\Team;

class ClubController extends Controller
{   
       /**
     * Search Clubs
     * @queryParam keyword string required
     *
            * @response {
            "Response": true,
            "StatusCode": 200,
            "Message": "Records Found",
            "Result": [
                {
                    "id": 24,
                    "title": "A Club",
                    "image": null,
                    "team_count": 1
                },
                {
                    "id": 27,
                    "title": "A club with promo",
                    "image": null,
                    "team_count": 2
                },
                {
                    "id": 25,
                    "title": "Aasadhj",
                    "image": null,
                    "team_count": 1
                },
                {
                    "id": 7,
                    "title": "Ajax",
                    "image": null,
                    "team_count": 0
                },
                {
                    "id": 8,
                    "title": "AJAX",
                    "image": null,
                    "team_count": 4
                },
                {
                    "id": 53,
                    "title": "Alvaro Montero",
                    "image": null,
                    "team_count": 1
                },
                {
                    "id": 57,
                    "title": "asdfasdfasd",
                    "image": "media/clubs/4IR296oLcP4HUnRb3CnGQuSvyem0y4YEOsSJOcOh.jpg",
                    "team_count": 1
                },
                {
                    "id": 56,
                    "title": "asdfsadfasdfasd",
                    "image": "media/clubs/tbagivPOLkn3nCaS7tD4KfW1gG92bBQvaMa43LBL.png",
                    "team_count": 0
                }
            ]
        }
     *
     *
     */

    public function searchClubs (Request $request)
    {   //Search clubs via name 
        $request->validate([
            "club_name"=>"required|string"
        ]);
        
        $club_name = $request->club_name;
        $clubs = Club::Select('id','title','image')->whereRaw('LOWER(`title`) LIKE ?',$club_name.'%')->with(['teams' =>function($q) {
            $q->select('team_id');
        }])->orderBy('title','asc')->get();

        for($i=0;$i<count($clubs);$i++)
        {
           $clubs[$i]['team_count'] = $clubs[$i]['teams']->count();
        }
        
        for($i=0;$i<count($clubs);$i++)
        {
            unset($clubs[$i]['teams']);
        }
        

        if($clubs->isEmpty())
        {
            return Helper::apiErrorResponse(false, 'No Clubs were Found', new \stdClass());
        }

        return Helper::apiSuccessResponse(true, 'Records Found', $clubs);
    }
}
