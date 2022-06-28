<?php

namespace App\Http\Controllers\Api\TrainerApp\Events;

use App\Event;
use App\EventCategory;
use App\Http\Controllers\Controller;
use App\Position;
use App\User;
use Illuminate\Http\Request;
use Psy\Util\Json;
use stdClass;

/**
    @group TrainerApp / Event
    
    API for trainerapp event
*/

class IndexController extends Controller
{

    private $eventModel, $eventColumns, $limit, $offset, $sortingColumn, $sortingType, $status;

    public function __construct(Request $request)
    {
        $this->eventModel = Event::class;

        $this->eventColumns = [
            'id',
            'created_by',
            'category_id',
            'event_id',
            'created_type',
            'group_id',
            'title',
            'from_date_time',
            'to_date_time',
            'valid_till',
            'repetition_id',
            'location',
            'latitude',
            'longitude',
            'team_id',
            'details',
            'event_type_id',
            'assignment_id',
            'opponent_team_id',
            'playing_area_id',
            'action_type',
            'deleted_dates',
            'status',
            'created_at'
        ];

        $this->limit = $request->limit;

        $this->offset = $request->offset;

        $this->sortingColumn = 'created_at';

        $this->sortingType = 'desc';

        $this->status = ['active'];
    }

    /**
        Events Listing

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "success",
            "Result": {
                "events": [
                    {
                        "id": 1,
                        "groupId": 1634219491,
                        "category": {
                            "id": 1,
                            "title": "training",
                            "color": "#aa37ff"
                        },
                        "title": "U19 training",
                        "start": "2021-11-01 04:00:00",
                        "end": "2021-11-02 16:00:00",
                        "isAttending": "yes",
                        "team": {
                            "id": 5,
                            "name": "consequatur",
                            "image": ""
                        }
                    }
                ],
                "time": {
                    "timeSt": "15:17",
                    "start": "2021-10-14 15:17:24"
                }
            }
        }

        @queryParam clubId required integer min:1. Example: 1
        @queryParam months[] required date array. Example: 01
        @queryParam years[] required date array. Example: 1970
        @queryParam limit required integer. Example: 10
        @queryParam offset required integer. Example: 0
    */

    public function index(Request $request)
    {
        $request->validate([
            'clubId' => 'required|numeric|min:1|exists:clubs,id',
            'months.*' => 'required|numeric|digits:2|between:1,12',
            'years.*' => 'required|numeric|digits:4|min:2021|max:'.date('Y', strtotime('+10 Years')).'',
            'limit' => 'required|numeric',
            'offset' => 'required|numeric'
        ]);

        $events = (new $this->eventModel)->index($request, $this->eventColumns, $this->sortingColumn, $this->sortingType, $this->status);

        return $events;
    }

    /**
        Event Listing By Date

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "success",
            "Result": {
                "markedDates": {
                    "2021-11-01": {
                        "events": [
                            {
                                "id": 1,
                                "groupId": 1634219491,
                                "category": {
                                    "id": 1,
                                    "title": "training",
                                    "color": "#aa37ff"
                                },
                                "title": "U19 training",
                                "start": "2021-11-01 04:00:00",
                                "end": "2021-11-02 16:00:00",
                                "repetition": {
                                    "id": 1,
                                    "title": "Weekly",
                                    "engTitle": "weekly"
                                },
                                "isAttending": "yes",
                                "team": {
                                    "id": 5,
                                    "name": "consequatur",
                                    "image": ""
                                }
                            }
                        ],
                        "dots": [
                            {
                                "id": 1,
                                "title": "training",
                                "color": "#aa37ff"
                            }
                        ]
                    }
                }
            }
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

        @queryParam months required date array. Example: [01]
        @queryParam years required date array. Example: [1970]
        @queryParam limit required integer. Example: 10
        @queryParam offset required integer. Example: 0
    */

    public function byDate(Request $request)
    {

        $request->validate([
            'clubId' => 'required|numeric|min:1|exists:clubs,id',
            'months.*' => 'required|numeric|digits:2|between:1,12',
            'years.*' => 'required|numeric|digits:4|min:2021|max:'.date('Y', strtotime('+10 Years')).'',
            'limit' => 'required|numeric',
            'offset' => 'required|numeric'
        ]);

        $response = (new $this->eventModel)->recordsByDate($request, $this->eventColumns, $this->sortingColumn, $this->sortingType, $this->status);

        return $response;
    }
}