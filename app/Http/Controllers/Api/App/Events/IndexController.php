<?php
namespace App\Http\Controllers\Api\App\Events;
use App\Http\Controllers\Controller;
use App\Event;
use App\Http\Requests\Api\App\Events\IndexRequest;
use App\Http\Requests\Api\App\Events\DetailsRequest;
use App\Http\Requests\Api\App\Events\AttendingEventRequest;
use Illuminate\Http\Request;

/**
	@group App / Events
*/

class IndexController extends Controller
{
	private $eventModel, $eventColumns, $sortingColumn, $sortingType, $status;

    public function __construct()
    {
        $this->eventModel = new Event();

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
            'action_type',
            'deleted_dates',
            'team_id',
            'status',
            'created_at'
        ];

        $this->sortingColumn = 'created_at';

        $this->sortingType = 'desc';
        
        $this->status = ['active'];
    }

	/**
     	Listing

		@response{
		"Response": true,
		"StatusCode": 200,
		"Message": "success",
		"Result": {
			"events": [
				{
					"id": 113,
					"groupId": 1632477003,
					"category": {
						"id": 1,
						"title": "training",
						"color": "#aa37ff"
					},
					"title": "U19 training",
					"start": "2021-09-24 19:00:00",
					"end": "2021-09-25 23:00:00",
					"isAttending": "pending",
					"team": {
						"id": 4,
						"name": "test team",
						"image": "https://lh3.googleusercontent.com/KNyKMfQqqVcLYAROYJ6KPW7nqmyMMcuc7npdzuzYI9KXhnZDJ3Wkfqy_apcQTDgq2QlNp9LzqQly06N5qsNxUOLT"
					}
				}
			],
			"time": {
				"timeSt": "10:08",
				"start": "2021-09-24 10:08:58"
			}
		}
		@response 500{
		    "Response": false,
		    "StatusCode": 500,
		    "Message": "Something wen't wrong",
		    "Result": []
		}

		@response 404{
		    "Response": false,
		    "StatusCode": 404,
		    "Message": "No records found",
		    "Result": []
		}

		@queryParam months[0] required date array. Example: 01
        @queryParam years[0] required date array. Example: 1970
		@queryParam limit required integer. Example: 10
		@queryParam offset required integer. Example: 0
    */

	public function index(IndexRequest $request)
	{
		$events = $this->eventModel->index($request, $this->eventColumns, $this->sortingColumn, $this->sortingType, $this->status);

        return $events;
	}

	/**
     	Details

	    @response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "success",
		    "Result": {
		        "id": 1,
		        "category": {
		            "id": 1,
		            "title": "Training",
		            "color": "#00ff9c"
		        },
		        "title": "Case 9",
		        "start": "2021-07-26 01:30:00",
		        "end": "2021-07-26 03:30:00",
		        "repetition": {
					"id": 1,
					"title": "Weekly"
		        },
		        "timeSt": "07:15",
		        "location": "D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan",
		        "latitude": "24.8048814",
		        "longitude": "67.0643315",
		        "team": {
		            "id": 5,
		            "name": "consequatur",
		            "image": ""
		        },
		        "trainer": {
		            "id": 513,
		            "name": "Shahzaib Trainer 001",
		            "image": "",
		            "positions": [],
		            "isAttending": "no"
		        },
		        "players": [
		            {
		                "id": 129,
		                "name": "Finn Dwinger",
		                "image": "media/users/5fa06e1308e0c1604349459.jpeg",
		                "positions": [
		                    {
		                        "id": 1,
		                        "name": "Left Back"
		                    }
		                ],
		                "isAttending": "no"
		            },
		            {
		                "id": 1,
		                "name": "Shahzaib Imran",
		                "image": "media/users/DPdPJwRkxPkbqYKJimgEOkII3RuN70ntRoSETlho.png",
		                "positions": [],
		                "isAttending": "no"
		            }
		        ],
		        "details": "Case 9",
		        "eventType": {
					"id": 1,
					"title": "Indoor"
		        }
		    }
		}

		@response 500{
		    "Response": false,
		    "StatusCode": 500,
		    "Message": "Something wen't wrong",
		    "Result": []
		}

		@response 404{
		    "Response": false,
		    "StatusCode": 404,
		    "Message": "No records found",
		    "Result": []
		}

		@queryParam start required date format eg: 2021-04-10 19:00:00 selected record date .Example: 2021-04-10 19:00:00
        @queryParam end required date format eg: 2021-04-12 23:00:00 selected record date .Example: 2021-04-12 23:00:00
        @queryParam groupId required string .Example: 1234567
    */
        
	public function details(DetailsRequest $request, $id)
	{
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

        $request->years = [date("Y", strtotime($request->start))];

		$event = $this->eventModel->details($request, $this->eventColumns, $this->sortingColumn, $this->sortingType, $this->status, $id);

        return $event;
	}

	/**
     	Is attending

     	@response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "Record has saved successfully",
		    "Result": {}
		}

		@response 500{
		    "Response": false,
		    "StatusCode": 500,
		    "Message": "Something wen't wrong",
		    "Result": {}
		}

		@response 404{
		    "Response": false,
		    "StatusCode": 404,
		    "Message": "Invalid Id",
		    "Result": {}
		}

		@queryParam eventId required integer. Example: 1
		@queryParam isAttending required string options can or no. Example: yes
    */

	public function isAttending(AttendingEventRequest $request)
	{
		$eventModel = new Event();

		$apiType = 'app';

		$event = $eventModel->isAttending($request, $apiType);

		return $event;
	}

	/**
     	listing by date

     	@response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "success",
		    "Result": {
		        "markedDates": {
		            "2021-06-11": {
		                "events": [
		                    {
		                        "id": 3,
		                        "groupId": 1623668694,
		                        "category": {
		                            "id": 1,
		                            "title": "training",
		                            "color": "#aa37ff"
		                        },
		                        "title": "U19 training 2",
		                        "start": "2021-06-11 00:00:00",
		                        "end": "2021-06-11 23:00:00",
		                        "repetition": {
									"id": 1,
									"title": "Monthly"
		                        },
		                        "isAttending": "pending",
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

		@response 500{
		    "Response": false,
		    "StatusCode": 500,
		    "Message": "Something wen't wrong",
		    "Result": []
		}

		@response 404{
		    "Response": false,
		    "StatusCode": 404,
		    "Message": "No records found",
		    "Result": []
		}

		@queryParam months[0] required date array. Example: 01
        @queryParam years[0] required date array. Example: 1970
		@queryParam limit required integer. Example: 10
		@queryParam offset required integer. Example: 0
    */

	public function byDate(IndexRequest $request)
	{
		$response = $this->eventModel->recordsByDate($request, $this->eventColumns, $this->sortingColumn, $this->sortingType, $this->status);

		return $response;
	}
}