<?php
namespace App\Http\Controllers\Api;
use App\EventCategory;
use App\EventMatchType;
use App\EventRepetition;
use App\EventType;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
    @group Event Data
    
    APIs for Event Data
*/

class EventDataController extends Controller
{
    /**
        
        Get Event Data

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Success",
            "Result": {
                "event_categories": [
                    {
                        "id": 6,
                        "title": "event",
                        "engTitle": "event"
                    }
                ],
                "event_types": [
                    {
                        "id": 1,
                        "title": "Indoor"
                    }
                ],
                "event_repetitions": [
                    {
                        "id": 1,
                        "title": "Weekly"
                    }
                ],
                "event_match_types": [
                    {
                        "id": 1,
                        "title": "Home"
                    }
                ]
            }
        }
    */

    public function getEventData()
    {
        $data = [];

        $data['event_categories'] = EventCategory::select("id", "title")
        ->where("status", "active")
        ->orderBy("created_at", "desc")
        ->get();

        $data['event_types'] = EventType::select("id", "title")
        ->where("status", "active")
        ->orderBy("created_at", "desc")
        ->get();

        $data['event_repetitions'] = EventRepetition::select("id", "title")
        ->where("status", "active")
        ->orderBy("created_at", "desc")
        ->get();

        $data['event_match_types'] = EventMatchType::select("id", "title")
        ->where("status", "active")
        ->orderBy("created_at", "desc")
        ->get();

        return Helper::apiSuccessResponse(true, 'Success', $data);
    }
}