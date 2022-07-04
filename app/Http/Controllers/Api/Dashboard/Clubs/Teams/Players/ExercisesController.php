<?php
namespace App\Http\Controllers\Api\Dashboard\Clubs\Teams\Players;
use App\Http\Controllers\Controller;
use App\User;
use App\Http\Requests\Api\Dashboard\Clubs\Teams\Players\Exercises\ViewJSONFileContentRequest;
use Illuminate\Http\Request;

/**
	@group Dashboard V4 / Player Exercises
*/

class ExercisesController extends Controller
{
	private $userModel, $relations, $limit, $offset, $sortingColumn, $sortingType, $status;

	public function __construct(Request $request)
	{
		$this->limit = $request->limit ?? 10;

		$this->offset = $request->offset ?? 0;

		$this->status = [
			1
		];

		$this->userModel = new User();

		$this->relations = [
			'exercises' => function ($query) use($request)
			{
				$query->select('player_exercise.id', 'player_exercise.ai_json')
				->whereIn('exercises.is_active', $this->status)
				->where('player_exercise.id', $request->playerExerciseId)
                ->where('user_id', $request->playerId)
                ->where('exercise_id', $request->exerciseId);
			}
		];

		$this->sortingColumn = 'created_at';

		$this->sortingType = 'asc';
	}

	/**
     	View JSON file content
     	
     	@response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "File found",
		    "Result": "{\"exercise_name\":\"NonAIBallExercise\"}"
		}

		@response 500{
		    "Response": false,
		    "StatusCode": 500,
		    "Message": "Something wen't wrong",
		    "Result": []
		}
		
		@response 422{
            "Response": false,
            "StatusCode": 422,
            "Message": "Invalid Parameters",
            "Result": {
                "playerId": [
                    "The player id field is required."
                ]
            }
        }

		@response 404{
		    "Response": true,
		    "StatusCode": 404,
		    "Message": "File not found",
		    "Result": {}
		}

		@response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": []
        }
		
		@queryParam playerId required integer. Example: 1
		@queryParam exerciseId required integer. Example: 1
		@queryParam playerExerciseId required integer. Example: 1
    */
		
	protected function JSONFileContent(ViewJSONFileContentRequest $request)
	{
		$response = $this->userModel
		->playerExerciseJSONFileContent($request, $this->relations);

		return $response;
	}
}