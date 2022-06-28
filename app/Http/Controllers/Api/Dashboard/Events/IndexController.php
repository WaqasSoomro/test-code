<?php
namespace App\Http\Controllers\Api\Dashboard\Events;
use App\Http\Controllers\Controller;
use App\Event;
use App\Http\Requests\Api\Dashboard\Events\IndexRequest;
use App\Http\Requests\Api\Dashboard\Events\CreateRequest;
use App\Http\Requests\Api\Dashboard\Events\EditRequest;
use App\Http\Requests\Api\Dashboard\Events\DeleteRequest;
use App\Http\Requests\Api\Dashboard\Events\DeletePlayerRequest;
use Illuminate\Http\Request;

/**
    @group Dashboard V4 / Events
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

        $this->sortingColumn = 'created_at';

        $this->sortingType = 'desc';
        
        $this->status = ['active'];
    }

	/**
        Listing
    
        If event type is "training" then the object will something like this:

        {
            "id": 1,
            "category": {
                "id": 1,
                "title": "Training",
                "color": "#aa37ff",
                "status": "active"
            },
            "title": "U19 training",
            "start": "2021-04-13 19:00:00",
            "end": "2021-04-15 23:00:00",
            "repetition": {
                "id": 1,
                "title": "Weekly"
            },
            "location": "D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan",
            "latitude": "24.8048814",
            "longitude": "67.0643315",
            "team": {
                "id": 5,
                "name": "consequatur",
                "image": ""
            },
            "players": [
                {
                    "id": 448,
                    "name": "Shahzaib Imran",
                    "image": "media/users/5fa27263a93271604481635.jpeg",
                    "positions": [
                        {
                            "id": 1,
                            "name": "Left Back",
                            "line": {
                                "id": 1,
                                "name": "Defenders"
                            }
                        }
                    ]
                }
            ],
            "details": "this is the first training event",
            "eventType": {
                "id": 1,
                "title": "Indoor"
            }
        }

        Or if event type is "assignment" then the object will something like this:
        
        {
            "id": 2,
            "category": {
                "id": 2,
                "title": "Assignment",
                "color": "#aa37ff",
                "status": "active"
            },
            "title": "U19 assignment",
            "start": "2021-04-10 19:00:00",
            "end": "2021-04-12 23:00:00",
            "repetition": {
                "id": 1,
                "title": "Weekly"
            },
            "location": "D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan",
            "latitude": "24.8048814",
            "longitude": "67.0643315",
            "team": {
                "id": 5,
                "name": "consequatur",
                "image": ""
            },
            "players": [
                {
                    "id": 448,
                    "name": "Shahzaib Imran",
                    "image": "media/users/5fa27263a93271604481635.jpeg",
                    "positions": [
                        {
                            "id": 1,
                            "name": "Left Back",
                            "line": {
                                "id": 1,
                                "name": "Defenders"
                            }
                        }
                    ]
                }
            ],
            "details": "this is the first training event",
            "assignment": {
                "id": 1,
                "title": "First ever assignment JOGO",
                "image": "media/assignments/cwBvJaBFFYTSfLJRjb1lcMG0tpbtqHmQQjSE0pZs.jpeg"
            }
        }

        Or if event type is "match" then the object will something like this:

        {
            "id": 3,
            "category": {
                "id": 4,
                "title": "Match",
                "color": "#aa37ff",
                "status": "active"
            },
            "title": "U19 match",
            "start": "2021-04-10 19:00:00",
            "end": "2021-04-12 23:00:00",
            "repetition": {
                "id": 1,
                "title": "Weekly"
            },
            "location": "D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan",
            "latitude": "24.8048814",
            "longitude": "67.0643315",
            "team": {
                "id": 5,
                "name": "consequatur",
                "image": ""
            },
            "players": [
                {
                    "id": 448,
                    "name": "Shahzaib Imran",
                    "image": "media/users/5fa27263a93271604481635.jpeg",
                    "positions": [
                        {
                            "id": 1,
                            "name": "Left Back",
                            "line": {
                                "id": 1,
                                "name": "Defenders"
                            }
                        }
                    ]
                }
            ],
            "details": "this is the first training event",
            "opponentTeam": {
                "id": 6,
                "name": "Test",
                "image": ""
            },
            "opponentTeamPlayers": [
                {
                    "id": 44,
                    "name": "Jahanzeb Khan",
                    "image": "",
                    "positions": [
                        {
                            "id": 1,
                            "name": "Left Back",
                            "line": {
                                "id": 1,
                                "name": "Defenders"
                            }
                        }
                    ]
                }
            ],
            "playingArea": {
                "id": 1,
                "title": "Home"
            }
        }

        Or if event type is "event" then the object will something like this:
        
        {
            "id": 4,
            "category": {
                "id": 6,
                "title": "Event",
                "color": "#aa37ff",
                "status": "active"
            },
            "title": "U19 event",
            "start": "2021-04-10 19:00:00",
            "end": "2021-04-12 23:00:00",
            "repetition": {
                "id": 1,
                "title": "Weekly"
            },
            "location": "D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan",
            "latitude": "24.8048814",
            "longitude": "67.0643315",
            "team": {
                "id": 5,
                "name": "consequatur",
                "image": ""
            },
            "players": [
                {
                    "id": 448,
                    "name": "Shahzaib Imran",
                    "image": "media/users/5fa27263a93271604481635.jpeg",
                    "positions": [
                        {
                            "id": 1,
                            "name": "Left Back",
                            "line": {
                                "id": 1,
                                "name": "Defenders"
                            }
                        }
                    ]
                }
            ],
            "details": "this is the first training event"
        }

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "success",
            "Result": [
                {
                    "id": 1,
                    "category": {
                        "id": 1,
                        "title": "Training",
                        "color": "#aa37ff",
                        "status": "active"
                    },
                    "title": "U19 training",
                    "start": "2021-04-13 19:00:00",
                    "end": "2021-04-15 23:00:00",
                    "repetition": {
                        "id": 1,
                        "title": "Weekly"
                    },
                    "location": "D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan",
                    "latitude": "24.8048814",
                    "longitude": "67.0643315",
                    "team": {
                        "id": 5,
                        "name": "consequatur",
                        "image": ""
                    },
                    "players": [
                        {
                            "id": 448,
                            "name": "Shahzaib Imran",
                            "image": "media/users/5fa27263a93271604481635.jpeg",
                            "positions": [
                                {
                                    "id": 1,
                                    "name": "Left Back",
                                    "line": {
                                        "id": 1,
                                        "name": "Defenders"
                                    }
                                }
                            ]
                        }
                    ],
                    "details": "this is the first training event",
                    "eventType": {
                        "id": 1,
                        "title": "Weekly"
                    }
                }
            ]
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
        
        @queryParam clubId required integer min:1. Example: 1
        @queryParam months[0] required date array. Example: 01
        @queryParam years[0] required date array. Example: 1970
        @queryParam limit required integer. Example: 10
        @queryParam offset required integer. Example: 0
    */

	protected function index(IndexRequest $request)
	{
		$response = $this->eventModel->index($request, $this->eventColumns, $this->sortingColumn, $this->sortingType, $this->status);

		return $response;
	}

	/**
        Create

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Record has saved successfully",
            "Result": {}
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "type": [
                    "The type string must be string."
                ]
            }
        }

        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid category",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
        
        @bodyParam clubId integer required .Example: 1
        @bodyParam type string required options: training, assignment, match or event .Example: training
        @bodyParam categoryId integer required .Example: 1
        @bodyParam title string required min 1 chars max 100 chars .Example: U19 training
        @bodyParam start date required date format eg: 2021-04-10 19:00:00 .Example: 2021-04-10 19:00:00
        @bodyParam end date required date format eg: 2021-04-12 23:00:00 .Example: 2021-04-12 23:00:00
        @bodyParam repetitionId integer required .Example: 1
        @bodyParam location string required .Example: D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan
        @bodyParam latitude string required .Example: 24.8048814
        @bodyParam longitude string required .Example: 67.0643315
        @bodyParam teamId integer required .Example: 5
        @bodyParam linesId[0] array required integer .Example: 1
        @bodyParam positionsId[0] array required integer .Example: 10
        @bodyParam playersId[0] array required integer .Example: 448
        @bodyParam details string optional .Example: this is the first training event
        @bodyParam eventTypeId integer optional if type can "training" then this key will required .Example: 1
        @bodyParam assignmentId integer optional if type can "assignment" then this key will required .Example: 1
        @bodyParam opponentTeamId integer optional if type can "match" then this key will required .Example: 3
        @bodyParam opponentLinesId[0] array optional if type can "match" then this key will required .Example: 1
        @bodyParam opponentPositionsId[0] array optional if type can "match" then this key will required .Example: 10
        @bodyParam opponentPlayersId[0] array optional if type can "match" then this key will required .Example: 448
        @bodyParam playingAreaId integer optional if type can "match" then this key will required .Example: 1
    */

	protected function create(CreateRequest $request)
	{
        $response = $this->eventModel->create($request);

		return $response;
	}

	/**
     	Edit

     	If event type is "training" then the object will something like this:

		{
            "id": 1,
            "category": {
                "id": 1,
                "title": "Training",
                "color": "#aa37ff",
                "status": "active"
            },
            "title": "U19 training",
            "start": "2021-04-13 19:00:00",
            "end": "2021-04-15 23:00:00",
            "repetition": {
                "id": 1,
                "title": "Weekly"
            },
            "location": "D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan",
            "latitude": "24.8048814",
            "longitude": "67.0643315",
            "team": {
                "id": 5,
                "name": "consequatur",
                "image": ""
            },
            "players": [
                {
                    "id": 448,
                    "name": "Shahzaib Imran",
                    "image": "media/users/5fa27263a93271604481635.jpeg",
                    "positions": [
                        {
                            "id": 1,
                            "name": "Left Back",
                            "line": {
                                "id": 1,
                                "name": "Defenders"
                            }
                        }
                    ]
                }
            ],
            "details": "this is the first training event",
            "eventType": {
                "id": 1,
                "title": "Weekly"
            }
        }

        Or if event type is "assignment" then the object will something like this:
		
		{
            "id": 2,
            "category": {
                "id": 2,
                "title": "Assignment",
                "color": "#aa37ff",
                "status": "active"
            },
            "title": "U19 assignment",
            "start": "2021-04-10 19:00:00",
            "end": "2021-04-12 23:00:00",
            "repetition": {
                "id": 1,
                "title": "Weekly"
            },
            "location": "D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan",
            "latitude": "24.8048814",
            "longitude": "67.0643315",
            "team": {
                "id": 5,
                "name": "consequatur",
                "image": ""
            },
            "players": [
                {
                    "id": 448,
                    "name": "Shahzaib Imran",
                    "image": "media/users/5fa27263a93271604481635.jpeg",
                    "positions": [
                        {
                            "id": 1,
                            "name": "Left Back",
                            "line": {
                                "id": 1,
                                "name": "Defenders"
                            }
                        }
                    ]
                }
            ],
            "details": "this is the first training event",
            "assignment": {
                "id": 1,
                "title": "First ever assignment JOGO",
                "image": "media/assignments/cwBvJaBFFYTSfLJRjb1lcMG0tpbtqHmQQjSE0pZs.jpeg"
            }
        }

        Or if event type is "match" then the object will something like this:

		{
            "id": 3,
            "category": {
                "id": 4,
                "title": "Match",
                "color": "#aa37ff",
                "status": "active"
            },
            "title": "U19 match",
            "start": "2021-04-10 19:00:00",
            "end": "2021-04-12 23:00:00",
            "repetition": {
                "id": 1,
                "title": "Weekly"
            },
            "location": "D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan",
            "latitude": "24.8048814",
            "longitude": "67.0643315",
            "team": {
                "id": 5,
                "name": "consequatur",
                "image": ""
            },
            "players": [
                {
                    "id": 448,
                    "name": "Shahzaib Imran",
                    "image": "media/users/5fa27263a93271604481635.jpeg",
                    "positions": [
                        {
                            "id": 1,
                            "name": "Left Back",
                            "line": {
                                "id": 1,
                                "name": "Defenders"
                            }
                        }
                    ]
                }
            ],
            "details": "this is the first training event",
            "opponentTeam": {
                "id": 6,
                "name": "Test",
                "image": ""
            },
            "opponentTeamPlayers": [
                {
                    "id": 44,
                    "name": "Jahanzeb Khan",
                    "image": "",
                    "positions": [
                        {
                            "id": 1,
                            "name": "Left Back",
                            "line": {
                                "id": 1,
                                "name": "Defenders"
                            }
                        }
                    ]
                }
            ],
            "playingArea": {
                "id": 1,
                "title": "Home"
            }
        }

        Or if event type is "event" then the object will something like this:
		
		{
            "id": 4,
            "category": {
                "id": 6,
                "title": "Event",
                "color": "#aa37ff",
                "status": "active"
            },
            "title": "U19 event",
            "start": "2021-04-10 19:00:00",
            "end": "2021-04-12 23:00:00",
            "repetition": {
                "id": 1,
                "title": "Weekly"
            },
            "location": "D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan",
            "latitude": "24.8048814",
            "longitude": "67.0643315",
            "team": {
                "id": 5,
                "name": "consequatur",
                "image": ""
            },
            "players": [
                {
                    "id": 448,
                    "name": "Shahzaib Imran",
                    "image": "media/users/5fa27263a93271604481635.jpeg",
                    "positions": [
                        {
                            "id": 1,
                            "name": "Left Back",
                            "line": {
                                "id": 1,
                                "name": "Defenders"
                            }
                        }
                    ]
                }
            ],
            "details": "this is the first training event"
        }

     	@response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "success",
		    "Result": {
		        "id": 8,
		        "category": {
		            "id": 6,
		            "title": "Event",
                    "color": "#aa37ff",
		            "status": "active"
		        },
		        "title": "U19 event",
		        "start": "2021-04-07 19:00:00",
		        "end": "2021-04-08 23:00:00",
		        "repetition": {
                    "id": 1,
                    "title": "Weekly"
                },
		        "location": "D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan",
		        "latitude": "24.8048814",
		        "longitude": "67.0643315",
		        "team": {
		            "id": 5,
		            "name": "consequatur",
		            "image": ""
		        },
		        "players": [
                    {
                        "id": 448,
                        "name": "Shahzaib Imran",
                        "image": "media/users/5fa27263a93271604481635.jpeg",
                        "positions": [
                            {
                                "id": 1,
                                "name": "Left Back",
                                "line": {
                                    "id": 1,
                                    "name": "Defenders"
                                }
                            }
                        ]
                    }
                ],
		        "details": "this is the first training event"
		    }
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

        @queryParam start required date format eg: 2021-04-10 19:00:00 selected record date .Example: 2021-04-10 19:00:00
        @queryParam end required date format eg: 2021-04-12 23:00:00 selected record date .Example: 2021-04-12 23:00:00
        @queryParam groupId required string .Example: 1234567
        @queryParam clubId required string .Example: 1
    */

	protected function edit(EditRequest $request, $id)
	{
        $request->years = [date("Y", strtotime($request->start))];

		$response = $this->eventModel->details($request, $this->eventColumns, $this->sortingColumn, $this->sortingType, $this->status, $id);

        return $response;
	}

	/**
     	Update

     	@response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "Record has saved successfully",
		    "Result": {}
		}

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "type": [
                    "The type string must be string."
                ]
            }
        }

        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid category",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
		
        @bodyParam clubId integer required .Example: 1
		@bodyParam groupId string required .Example: 1234567
        @bodyParam actionType string required options: single, bulk, current_&_upcoming .Example: single
        @bodyParam type string required options: training, assignment, match or event .Example: training
        @bodyParam categoryId integer required .Example: 1
		@bodyParam title string required min 1 chars max 100 chars .Example: U19 training
		@bodyParam start date required date format eg: 2021-04-10 19:00:00 .Example: 2021-04-10 19:00:00
		@bodyParam end date required date format eg: 2021-04-12 23:00:00 .Example: 2021-04-12 23:00:00
        @bodyParam currentStartDate date required date format eg: 2021-04-12 23:00:00 .Example: 2021-04-12 23:00:00
        @bodyParam currentEndDate date required date format eg: 2021-04-12 23:00:00 .Example: 2021-04-12 23:00:00
		@bodyParam repetitionId integer required .Example: 1
		@bodyParam location string required .Example: D.H.A Phase 6 Defence Housing Authority, Karachi, Pakistan
		@bodyParam latitude string required .Example: 24.8048814
		@bodyParam longitude string required .Example: 67.0643315
		@bodyParam teamId integer required .Example: 5
        @bodyParam linesId[0] array required integer .Example: 1
		@bodyParam positionsId[0] array required integer .Example: 10
		@bodyParam playersId[0] array required integer .Example: 448
		@bodyParam details string optional .Example: this is the first training event
		@bodyParam eventTypeId integer optional if type can "training" then this key will required .Example: 1
		@bodyParam assignmentId integer optional if type can "assignment" then this key will required .Example: 1
		@bodyParam opponentTeamId integer optional if type can "match" then this key will required .Example: 3
        @bodyParam opponentLinesId[0] array optional if type can "match" then this key will required .Example: 1
        @bodyParam opponentPositionsId[0] array optional if type can "match" then this key will required .Example: 10
        @bodyParam opponentPlayersId[0] array optional if type can "match" then this key will required .Example: 448
		@bodyParam playingAreaId integer optional if type can "match" then this key will required .Example: 1
    */

	protected function update(CreateRequest $request, $id)
	{
		$response = $this->eventModel->create($request, $id);

        return $response;
	}

	/**
        Delete

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Record has deleted successfully",
            "Result": {}
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "type": [
                    "The type string must be string."
                ]
            }
        }

        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid id",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @queryParam actionType required string options: single, bulk. Example: single
        @queryParam start required date format eg: 2021-04-10 19:00:00 selected record date .Example: 2021-04-10 19:00:00
        @queryParam end required date format eg: 2021-04-10 19:00:00 selected record date .Example: 2021-04-10 19:00:00
        @queryParam groupId required string .Example: 1234567
        @queryParam clubId required string .Example: 1
    */

	protected function delete(DeleteRequest $request, $id)
	{
        $request->years = [date("Y", strtotime($request->start))];

		$response = $this->eventModel->remove($request, $id);

        return $response;
	}

    /**
        Delete event player

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Player has deleted successfully",
            "Result": {}
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "type": [
                    "The type string must be string."
                ]
            }
        }

        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid id",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @queryParam clubId required integer .Example: 1
        @queryParam eventId required integer .Example: 1
    */

    protected function deletePlayer(DeletePlayerRequest $request, $id)
    {
        $response = $this->eventModel->removePlayer($request, $id);

        return $response;
    }
}