<?php
namespace App\Http\Controllers\Api\Dashboard\Clubs\Teams\Players;
use App\Http\Controllers\Controller;
use App\User;
use App\PlayerChart;
use App\Http\Requests\Api\Dashboard\Clubs\Teams\Players\IndexRequest;
use App\Http\Requests\Api\Dashboard\Clubs\Teams\Players\ChartRequest;
use Illuminate\Http\Request;

/**
	@group Dashboard V4 / Players
*/

class IndexController extends Controller
{
	private $userModel, $playerChart, $playersColumns, $limit, $offset, $sortingColumn, $sortingType, $status;

	public function __construct(Request $request)
	{
		$this->userModel = new User();

		$this->playerChart = new PlayerChart();

		$this->playersColumns = (new $this->userModel)->playersGeneralColumns();

		$this->limit = $request->limit ?? 10;

		$this->offset = $request->offset ?? 0;

		$this->sortingColumn = 'created_at';

		$this->sortingType = 'asc';

		$this->status = [1];
	}

	/**
     	Team players listing
     	
     	@response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "Records found successfully",
		    "Result": [
		        {
		            "id": 1,
		            "name": "Shahzaib Imran",
		            "image": "media/users/5fa27263a93271604481635.jpeg",
		            "positions": [
		                {
		                    "id": 1,
		                    "name": "Left Back"
		                },
		                "line": {
                            "id": 1,
                            "name": "Defenders"
                        }
		            ]
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
		    "Message": "no records found",
		    "Result": []
		}

		@queryParam teamId required integer. Example: 5
		@queryParam positionsId[0] required integer. Example: 4
		@queryParam positionsId[0] required integer. Example: 10
		@queryParam limit required integer. Example: 10
        @queryParam offset required integer. Example: 0
    */
		
	protected function listingByPositions(IndexRequest $request)
	{
		$response = $this->userModel->getPlayers($request, $this->playersColumns, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType, $this->status);

		return $response;
	}

	/**
     	Player charts
 		
 		@response 422{
		    "Response": false,
		    "StatusCode": 422,
		    "Message": "Invalid Parameters",
		    "Result": {
		        "teamId": [
		            "The team id must be a number."
		        ]
		    }
		}
	
		@response 500{
		    "Response": false,
		    "StatusCode": 500,
		    "Message": "Something wen't wrong",
		    "Result": {
		        "playerChart": {},
		        "teamCharts": {}
		    }
		}

     	@response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "Success",
		    "Result": {
		        "playerChart": {
		            "total_attempts": 2,
		            "total_dribbling_distance": "123.66",
		            "total_number_of_passes": "7",
		            "total_number_of_shots": "0",
		            "total_number_of_receivings": "8",
		            "total_number_of_ball_touches": "96",
		            "total_running_distance": "239",
		            "total_number_of_sprints": "0",
		            "total_number_of_acceleration": "0",
		            "total_low_tempo": "1.48",
		            "total_mid_tempo": "0.83",
		            "total_high_tempo": "0.00",
		            "max_sprint_speed": "19.43",
		            "max_acceleration": "0.00",
		            "max_dribbling_speed": "25.57",
		            "max_receiving_speed": "9.77",
		            "max_speed_during_passing": "30.00",
		            "max_speed_during_shooting": "0.00",
		            "total_shot_power": "0.00"
		        },
		        "teamCharts": {
		            "total_attempts": 3,
		            "avg_dribbling_distance": "67.89",
		            "avg_number_of_passes": "3.67",
		            "avg_number_of_shots": "0.00",
		            "avg_number_of_receivings": "2.67",
		            "avg_number_of_ball_touches": "43.67",
		            "avg_running_distance": "109.33",
		            "avg_number_of_sprints": "0.00",
		            "avg_number_of_acceleration": "0.00",
		            "total_low_tempo": "1.49",
		            "total_mid_tempo": "0.31",
		            "total_high_tempo": "0.00",
		            "max_sprint_speed": "30.00",
		            "max_acceleration": "0.00",
		            "max_dribbling_speed": "20.00",
		            "max_receiving_speed": "19.44",
		            "max_speed_during_passing": "30.00",
		            "max_speed_during_shooting": "0.00",
		            "avg_shot_power": "0.00"
		        }
		    }
		}

		@response 404{
		    "Response": false,
		    "StatusCode": 404,
		    "Message": "Not found",
		    "Result": {
		        "playerChart": {},
		        "teamCharts": {}
		    }
		}

		@queryParam teamId required integer. Example: 5
		@queryParam playerId required integer. Example: 5
    */
		
	protected function playerCharts(ChartRequest $request)
	{
		$response = $this->playerChart->playerCharts($request, $request->teamId, $request->playerId);

		return $response;
	}
}