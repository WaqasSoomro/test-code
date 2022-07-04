<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\Api\Dashboard\Clubs\Teams\Players\ChartResource;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Exception;
use stdClass;

class PlayerChart extends Model
{
    use SoftDeletes;

    private $playerModel, $stdClass;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->stdClass = new stdClass();

        $this->playerModel = new Player();
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function playerCharts($request, $teamId, $playerId)
    {
        try
        {
            $players = $this->playerModel->players($request, $teamId);
            $playersId = count($players) > 0 ? array_column($players, "user_id") : [];

            $playerChart = $this::select("player_charts.id")
            ->selectRaw("count(player_charts.id) as total_attempts, FORMAT(sum(player_charts.dribbling_distance), 2) as total_dribbling_distance, sum(player_charts.number_of_passes) as total_number_of_passes, sum(player_charts.number_of_shots) as total_number_of_shots, sum(player_charts.number_of_receivings) as total_number_of_receivings, sum(player_charts.number_of_ball_touches) as total_number_of_ball_touches, sum(player_charts.running_distance) as total_running_distance, sum(player_charts.number_of_sprints) as total_number_of_sprints, sum(player_charts.number_of_acceleration) as total_number_of_acceleration, FORMAT(sum(player_charts.low_tempo), 2) as total_low_tempo, FORMAT(sum(player_charts.mid_tempo), 2) as total_mid_tempo, FORMAT(sum(player_charts.high_tempo), 2) as total_high_tempo, FORMAT(max(player_charts.max_sprint_speed), 2) as max_sprint_speed, FORMAT(max(player_charts.max_acceleration), 2) as max_acceleration, FORMAT(sum(player_charts.max_dribbling_speed), 2) as max_dribbling_speed, FORMAT(max(player_charts.max_receiving_speed), 2) as max_receiving_speed, FORMAT(max(player_charts.max_speed_during_passing), 2) as max_speed_during_passing, FORMAT(max(player_charts.max_speed_during_shooting), 2) as max_speed_during_shooting, FORMAT(sum(player_charts.shot_power), 2) as total_shot_power")
            ->where("player_charts.player_id", $playerId)
            ->whereHas("player.user.teams", function ($query) use($teamId)
            {
                $query->where("player_team.team_id", $teamId);
            })
            ->groupBy("player_charts.player_id")
            ->orderBy("player_charts.created_at", "desc")
            ->first();

            $teamCharts = $this::select("player_charts.id")
            ->selectRaw("count(player_charts.id) as total_attempts, FORMAT(sum(player_charts.dribbling_distance) / count(player_charts.id), 2) as avg_dribbling_distance, FORMAT(sum(player_charts.number_of_passes) / count(player_charts.id), 2) as avg_number_of_passes, FORMAT(sum(player_charts.number_of_shots) / count(player_charts.id), 2) as avg_number_of_shots, FORMAT(sum(player_charts.number_of_receivings) / count(player_charts.id), 2) as avg_number_of_receivings, FORMAT(sum(player_charts.number_of_ball_touches) / count(player_charts.id), 2) as avg_number_of_ball_touches, FORMAT(sum(player_charts.running_distance) / count(player_charts.id), 2) as avg_running_distance, FORMAT(sum(player_charts.number_of_sprints) / count(player_charts.id), 2) as avg_number_of_sprints, FORMAT(sum(player_charts.number_of_acceleration) / count(player_charts.id), 2) as avg_number_of_acceleration, FORMAT(sum(player_charts.low_tempo) / count(player_charts.id), 2) as total_low_tempo, FORMAT(sum(player_charts.mid_tempo) / count(player_charts.id), 2) as total_mid_tempo, FORMAT(sum(player_charts.high_tempo) / count(player_charts.id), 2) as total_high_tempo, max(player_charts.max_sprint_speed) as max_sprint_speed, max(player_charts.max_acceleration) as max_acceleration, max(player_charts.max_dribbling_speed) as max_dribbling_speed, max(player_charts.max_receiving_speed) as max_receiving_speed, max(player_charts.max_speed_during_passing) as max_speed_during_passing, max(player_charts.max_speed_during_shooting) as max_speed_during_shooting, FORMAT(sum(player_charts.shot_power) / count(player_charts.id), 2) as avg_shot_power")
            ->leftJoin("player_team", "player_team.user_id", "=", "player_charts.player_id")
            ->where("player_team.team_id", $teamId)
            ->first();

            $charts = new stdClass();
            $charts->playerChart = $playerChart;
            $charts->teamCharts = $teamCharts;

            if ($playerChart && $playerChart->id && $teamCharts && $teamCharts->id)
            {
                $response = Helper::apiSuccessResponse(true, "Success", (new ChartResource($charts))->resolve());
            }
            else if ($playerChart && $playerChart->id)
            {
                $response = Helper::apiSuccessResponse(true, "Only player charts found", (new ChartResource($charts))->resolve());
            }
            else if ($teamCharts && $teamCharts->id)
            {
                $response = Helper::apiSuccessResponse(true, "Only team charts found", (new ChartResource($charts))->resolve());
            }
            else
            {
                $response = Helper::apiNotFoundResponse(false, "Not found", [
                   "playerChart" => new stdClass(), 
                   "teamCharts" => new stdClass() 
                ]);
            }
        }
        catch (Exception $ex)
        {
            $response = $response = Helper::apiErrorResponse(false, "Something wen't wrong", [
               "playerChart" => new stdClass(), 
               "teamCharts" => new stdClass() 
            ]);
        }

        return $response;
    }    
}