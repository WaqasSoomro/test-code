<?php
namespace App\Http\Controllers\Api\TrainerApp\Events;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Events\DeleteRequest;
use App\{
    Club,
    Country,
    EventCategory,
    Event,
    Language
};
use App\Http\Requests\Api\TrainerApp\Events\{
    CategoryRequest,
    CreateEventRequest
};
use Illuminate\Http\Request;

/**
    @group TrainerApp / Event
    
    API for trainerapp event
*/

class EventController extends Controller
{
    private $apiType = "trainerApp";

    private $clubsModel, $countriesModel, $languagesModel, $positionModel, $positionColumns, $limit, $sockets, $sortingColumn, $sortingType, $status, $eventCategoryModel, $eventModel, $eventColumns, $offset;

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

        $this->clubsModel = Club::class;

        $this->countriesModel = Country::class;

        $this->languagesModel = Language::class;

        $this->eventCategoryModel = EventCategory::class;

        $this->categoriesColumns = (new $this->eventCategoryModel)->generalColumns();

        $this->limit = $request->limit ?? 10;

        $this->offset = $request->offset ?? 0;

        $this->sortingColumn = 'created_at';

        $this->sortingType = 'desc';

        $this->status = ['active'];
    }
    
    /**
        Create An Event

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
        @bodyParam positionsId[0] array required integer .Example: 10
        @bodyParam playersId[0] array required integer .Example: 448
        @bodyParam details string optional .Example: this is the first training event
        @bodyParam eventTypeId integer optional if type can "training" then this key will required .Example: 1
        @bodyParam assignmentId integer optional if type can "assignment" then this key will required .Example: 1
        @bodyParam opponentTeamId integer optional if type can "match" then this key will required .Example: 3
        @bodyParam opponentPositionsId[0] array optional if type can "match" then this key will required .Example: 10
        @bodyParam opponentPlayersId[0] array optional if type can "match" then this key will required .Example: 448
        @bodyParam playingAreaId integer optional if type can "match" then this key will required .Example: 1
    */

    public function create(CreateEventRequest $request)
    {
        $response = (new $this->eventModel)->create($request);

        return $response;
    }

    /** 
        EventDetails

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "success",
            "Result": {
                "id": 2,
                "category": {
                    "id": 1,
                    "title": "training",
                    "color": "#aa37ff"
                },
                "title": "U19 updated training",
                "start": "2021-11-14 04:00:00",
                "end": "2021-11-15 16:00:00",
                "repetition": {
                    "id": 1,
                    "title": "Weekly",
                    "engTitle": "weekly"
                },
                "timeSt": "15:25",
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
                    "image": "media/users/WZOV8qPlL5q0zArUSxfn5zsoGgl5YgTsh9TovGKx.jpg",
                    "positions": [],
                    "team_name": "consequatur",
                    "isAttending": "no"
                },
                "players": [
                    {
                        "id": 1,
                        "name": "Shahzaib Trainer 001",
                        "image": "media/users/DPdPJwRkxPkbqYKJimgEOkII3RuN70ntRoSETlho.png",
                        "positions": [],
                        "team_name": "consequatur",
                        "isAttending": "pending"
                    }
                ],
                "details": "this is the updated training event",
                "eventType": {
                    "id": 2,
                    "title": "Outdoor"
                }
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

    public function details(Request $request, $id)
    {
        $request->years = [date("Y", strtotime($request->start))];

        $response = (new $this->eventModel)->details($request, $this->eventColumns, $this->sortingColumn, $this->sortingType, $this->status, $id);

        return $response;
    }

    /**
        Update Event

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
        @bodyParam positionsId[0] array required integer .Example: 10
        @bodyParam playersId[0] array required integer .Example: 448
        @bodyParam details string optional .Example: this is the first training event
        @bodyParam eventTypeId integer optional if type can "training" then this key will required .Example: 1
        @bodyParam assignmentId integer optional if type can "assignment" then this key will required .Example: 1
        @bodyParam opponentTeamId integer optional if type can "match" then this key will required .Example: 3
        @bodyParam opponentPositionsId[0] array optional if type can "match" then this key will required .Example: 10
        @bodyParam opponentPlayersId[0] array optional if type can "match" then this key will required .Example: 448
        @bodyParam playingAreaId integer optional if type can "match" then this key will required .Example: 1
    */

    public function update(CreateEventRequest $request,$id)
    {
        $response = (new $this->eventModel)->create($request, $id);

        return $response;
    }

    /**
        Delete An Event
        
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

    public function delete(DeleteRequest $request, $id)
    {
        $request->years = [date("Y", strtotime($request->start))];
        
        $response = (new $this->eventModel)->remove($request, $id);

        return $response;
    }

    /**
        Events Categories
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found successfully",
            "Result": [
                {
                    "id": 4,
                    "title": "event",
                    "color": "#00ff9c",
                    "status": "active"
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

        @queryParam limit required integer. Example: 10
        @queryParam offset required integer. Example: 0
    */

    public function eventCategories(CategoryRequest $request)
    {
        $response = (new $this->eventCategoryModel)->viewCategories($request, $this->categoriesColumns, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType, $this->status);

        return $response;
    }
}