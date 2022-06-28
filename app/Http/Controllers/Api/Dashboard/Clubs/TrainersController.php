<?php
namespace App\Http\Controllers\Api\Dashboard\Clubs;
use App\Http\Controllers\Controller;
use App\Club;
use App\User;
use App\Team;
use App\Http\Requests\Api\Dashboard\Clubs\Trainers\IndexRequest;
use App\Http\Requests\Api\Dashboard\Clubs\Trainers\ApproveJoiningRequest;
use App\Http\Requests\Api\Dashboard\Clubs\Trainers\CreateRequest;
use App\Http\Requests\Api\Dashboard\Clubs\Trainers\EditRequest;
use App\Http\Requests\Api\Dashboard\Clubs\Trainers\UpdateRequest;
use App\Http\Requests\Api\Dashboard\Clubs\Trainers\DeleteRequest;
use App\Http\Requests\Api\Dashboard\Clubs\Teams\Trainers\DeleteRequest as DeleteTeamRequest;
use Illuminate\Http\Request;

/**
    @group Dashboard V4 / Trainers
*/

class TrainersController extends Controller
{
    private $clubModel, $userModel, $teamModel, $limit, $offset, $sortingColumn, $sortingType, $status, $apiType, $columns, $relationalColumns, $trainerRequestType;

    public function __construct(Request $request)
    {
        $this->clubModel = new Club();

        $this->userModel = new User();

        $this->teamModel = new Team();

        $this->limit = $request->limit;

        $this->offset = $request->offset;

        $this->sortingColumn = 'created_at';

        $this->sortingType = 'desc';

        $this->status = ['active', 'inactive'];

        $this->apiType = 'trainerDashboard';

        $this->columns = ['id', 'title', 'owner_id'];

        $this->trainerRequestType = 'yes';

        $this->relationalColumns = [
            'trainers' => function ($query)
            {
                $query->select('club_id', 'trainer_user_id', 'users.id', 'first_name', 'last_name', 'email', 'profile_picture', 'last_seen', 'status_id')
                ->WithPivot('is_request_accepted')
                ->wherePivot('is_request_accepted', $this->trainerRequestType)
                ->orderBy('users.'.$this->sortingColumn, $this->sortingType)
                ->limit($this->limit)
                ->offset($this->offset);
            },
            'trainers.teams_trainers' => function ($query)
            {
                $query->select('teams.id', 'team_id', 'team_name', 'trainer_user_id')
                ->orderBy('teams.'.$this->sortingColumn, $this->sortingType);
            },
            'trainers.status' => function ($query)
            {
                $query->select('id', 'name');
            },
            'owner' => function ($query)
            {
                $query->select('id', 'first_name', 'last_name', 'email', 'profile_picture', 'last_seen');
            }
        ];

        $this->trainersType = 'yes';
    }

    /**
        Listing

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found",
            "Result": [
                {
                    "id": 12,
                    "firstName": "Shahzaib",
                    "lastName": "Imran",
                    "email": "shahzaib.imran@jogo.ai",
                    "image": "media/users/5f20d450524291595987024.jpeg",
                    "teams": "Team 1, JOGO, consequatur, consequatur",
                    "isOwner": "No",
                    "lastActive": "24-06-2021",
                    "status": "active"
                }
            ]
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

        @queryParam clubId required integer. Example: 1
        @queryParam limit required integer. Example: 10
        @queryParam offset required integer. Example: 0
    */

    protected function index(IndexRequest $request)
    {
        $response = $this->clubModel->viewTrainers($request, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType, $this->status, $this->columns, $this->relationalColumns, $this->trainersType);

        return $response;
    }

    /**
        Joining requests

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found",
            "Result": [
                {
                    "id": 526,
                    "firstName": "Shahzaib",
                    "lastName": "Trainer 002",
                    "email": "shahzaibtrainer002@yopmail.com",
                    "image": "media/users//DeCnDE1jjLKF5JiMQpXyt2rZuPIVc3CMgblWtsw3.jpg",
                    "teams": "JOGO, consequatur",
                    "isOwner": "No",
                    "lastActive": "22-06-2021"
                }
            ]
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

        @queryParam clubId required integer. Example: 1
        @queryParam limit required integer. Example: 10
        @queryParam offset required integer. Example: 0
    */

    protected function joiningRequests(IndexRequest $request)
    {
        $this->trainerRequestType = 'no';

        $response = $this->clubModel->viewTrainers($request, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType, $this->status, $this->columns, $this->relationalColumns, $this->trainersType);

        return $response;
    }

    /**
        Approve request

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found",
            "Result": {}
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
    
        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "No records found",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @queryParam clubId required integer. Example: 1
        @queryParam trainerId required integer. Example: 1
        @queryParam action required string options: yes, no. Example: yes
    */

    protected function approveRequest(ApproveJoiningRequest $request)
    {
        $response = $this->userModel->approveTrainerRequest($request);

        return $response;
    }

    /**
        Create

        @response[
            {
                "Response": true,
                "StatusCode": 200,
                "Message": "You've created trainers successfully",
                "Result": []
            }
        ]

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "firstName": [
                    "The first string must be string."
                ]
            }
        }
    
        @response 404[
            {
                "Response": false,
                "StatusCode": 404,
                "Message": "Teams not found",
                "Result": []
            }
        ]

        @response 500[
            {
                "Response": false,
                "StatusCode": 500,
                "Message": "Something wen't wrong",
                "Result": []
            }
        ]

        @bodyParam clubId integer required min:1 .Example: 1
        @bodyParam firstNames[0] array required min:3 max:25 .Example: Shahzaib
        @bodyParam lastNames[0] array required min:3 max:25 .Example: Trainer 001
        @bodyParam emails[0] array required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
        @bodyParam countryCodes[0] array min:1 max:3 required .Example: 92
        @bodyParam phoneNos[0] array required min:4 max:12 .Example: 1234567890
        @bodyParam assignedTeams[0] array required min:1 .Example: 1
    */

    protected function create(CreateRequest $request)
    {
        $response = $this->userModel->createTrainers($request);

        return $response;
    }

    /**
        Edit

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Success",
            "Result": {
                "id": 540,
                "firstName": "Shahzaib",
                "lastName": "Trainer 005",
                "email": "shahzaibtrainer005@yopmail.com",
                "countryCode": {
                    "id": 164,
                    "code": 92
                },
                "phoneNo": "3482302454",
                "teams": [
                    {
                        "id": 5,
                        "name": "consequatur"
                    }
                ],
                "isOwner": false
            }
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "id": [
                    "The id must be number."
                ]
            }
        }
        
        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "No record found",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @queryParam clubId required integer min:1. Example: 1
    */

    protected function edit(EditRequest $request, $id)
    {
        $this->relationalColumns = [
            'trainers' => function ($query) use($id)
            {
                $query->select('club_id', 'trainer_user_id', 'users.id', 'first_name', 'last_name', 'email', 'country_code_id', 'phone')
                ->WithPivot('is_request_accepted')
                ->wherePivot('is_request_accepted', $this->trainerRequestType)
                ->where('trainer_user_id', $id);
            },
            'trainers.teams_trainers' => function ($query)
            {
                $query->select('teams.id', 'team_id', 'team_name', 'trainer_user_id')
                ->orderBy('teams.'.$this->sortingColumn, $this->sortingType);
            }
        ];

        $response = $this->clubModel->viewTrainer($request, $this->status, $this->columns, $this->relationalColumns, $this->trainersType, $id);

        return $response;
    }

    /**
        Update

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "You've updated trainer successfully",
            "Result": {}
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "firstName": [
                    "The first string must be string."
                ]
            }
        }
    
        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid trainer id",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
        
        @bodyParam clubId integer required min:3 .Example: 1
        @bodyParam firstNames[0] array required min:3 max:25 .Example: Shahzaib
        @bodyParam lastNames[0] array required min:3 max:25 .Example: Trainer 001
        @bodyParam emails[0] array required min:8 max:254 .Example: shahzaibtrainer001@yopmail.com
        @bodyParam countryCodes[0] array min:1 max:3 required .Example: 92
        @bodyParam phoneNos[0] array required min:4 max:12 .Example: 1234567890
        @bodyParam assignedTeams[0] array required min:1 .Example: 1
        @bodyParam role[0] string required options:trainer, owner .Example: trainer
    */

    protected function update(UpdateRequest $request, $id)
    {
        $response = $this->userModel->createTrainers($request, $id);

        return $response;
    }

    /**
        Delete

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "You've successfully deleted your trainer",
            "Result": {}
        }
        
        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "firstName": [
                    "The id must be number."
                ]
            }
        }

        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid trainer id",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @queryParam clubId required integer .Example: 1
    */

    protected function delete(DeleteRequest $request, $id)
    {
        $response = $this->userModel->remove($request, $id);

        return $response;
    }

    /**
        Delete team trainer

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "You've successfully deleted trainer from your team",
            "Result": {}
        }

        @response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "firstName": [
                    "The id must be number."
                ]
            }
        }

        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid trainer id",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @queryParam clubId required integer .Example: 1
    */

    protected function deleteTeam(DeleteTeamRequest $request, $teamId, $trainerId)
    {
        $response = $this->teamModel->removeTrainer($request, $request->clubId, $teamId, $trainerId);

        return $response;
    }
}