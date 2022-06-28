<?php

namespace App\Http\Controllers\Api\TrainerApp\DataPreference;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\TeamMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Data Preference
 *
 * APIs For Data Preference
 */
class IndexController extends Controller
{

    /**
     * Save Team Metrics
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Team metric has been saved successfully",
    "Result": {}
    }
     *
     * @bodyParam team_id int required  required
     * @bodyParam line array required  required
     * @bodyParam position array required  required
     * @bodyParam player_id array required  required
     * @bodyParam metric_type string required  sensor/ai/cognitive
     * @bodyParam kick_strength integer required  required
     * @bodyParam max_speed integer required  required
     * @bodyParam leg_distribution string required  required
     * @bodyParam ball_kicks integer required  required
     * @bodyParam total_distance integer required  required
     * @bodyParam impact integer required  required
     * @return JsonResponse
     */

    public function saveTeamMetrics(Request $request){
        $validator = Validator::make($request->all(), [
            'team_id'           => 'required|exists:teams,id',
            'lines'             => 'required|array',
            'position'          => 'required|array',
            'player_id'         => 'required|array|exists:users,id',
            'metric_type'       => 'required|in:sensor,ai,cognitive',
            'kick_strength'     => 'required|numeric|min:1',
            'max_speed'         => 'required|numeric|min:1',
            'leg_distribution'  => 'required',
            'ball_kicks'        => 'required|numeric|min:1',
            'total_distance'    => 'required|numeric|min:1',
            'impact'            => 'required|numeric|min:1',
        ]);

        if($validator->fails()) {
            return Helper::apiErrorResponse(false, 'Error', $validator->messages()->toArray());
        }

        $team_metric = TeamMetric::where('team_id', $request->team_id)->first();
        if(empty($team_metric)){
            $team_metric = new TeamMetric();
        }

        return $team_metric->addTeamMetric($request);
    }

    /**
     * Get Team Metrics
     *
     * @response {
        "Response": false,
        "StatusCode": 500,
        "Message": "Team metric not found",
        "Result": {}
        }
     *
     * @response {
        "Response": true,
        "StatusCode": 200,
        "Message": "Team metric found",
        "Result": {
             * "id": 1,
            "team_id": 30,
            "created_by": 20,
            "lines": [
            "1"
            ],
            "position": [
            "CF",
            "MD"
            ],
            "player_id": [
            "2"
            ],
            "metric_type": "sensor",
            "kick_strength": "20",
            "max_speed": "7",
            "leg_distribution": "50% right - 50% left",
            "ball_kicks": "10",
            "total_distance": "5",
            "impact": "10",
            "created_at": "2021-07-02 12:25:57",
            "updated_at": "2021-07-02 12:27:48"
     *   }
        }
     *
     * @bodyParam team_id int required  required
     * @bodyParam metric_type string required  sensor/ai/cognitive
     * @return JsonResponse
     */

    public function getTeamMetrics(Request $request){

        $validator = Validator::make($request->all(), [
            'team_id'           => 'required|exists:teams,id',
            'metric_type'       => 'required|in:sensor,ai,cognitive',
        ]);

        if($validator->fails()) {
            return Helper::apiErrorResponse(false, 'Error', $validator->messages()->toArray());
        }

        $team_metric = TeamMetric::whereTeamIdAndMetricType($request->team_id,$request->metric_type)->first();

        if(!empty($team_metric)){
            $team_metric->lines     = json_decode($team_metric->lines);
            $team_metric->player_id = json_decode($team_metric->player_id);
            $team_metric->position  = json_decode($team_metric->position);
            return Helper::apiSuccessResponse(true, 'Team metric found',$team_metric);
        }else{
            return Helper::apiErrorResponse(false, 'Team metric not found',new \stdClass());
        }
    }
}
