<?php
namespace App\Http\Resources\Api\Dashboard\Clubs\Teams\Players;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            "playerChart" => [
                "total_attempts" => $this->playerChart->total_attempts ?? 0,
                "total_dribbling_distance" => $this->playerChart->total_dribbling_distance ?? number_format(0, 2),
                "total_number_of_passes" => $this->playerChart->total_number_of_passes ?? 0,
                "total_number_of_shots" => $this->playerChart->total_number_of_shots ?? 0,
                "total_number_of_receivings" => $this->playerChart->total_number_of_receivings ?? 0,
                "total_number_of_ball_touches" => $this->playerChart->total_number_of_ball_touches ?? 0,
                "total_running_distance" => $this->playerChart->total_running_distance ?? 0,
                "total_number_of_sprints" => $this->playerChart->total_number_of_sprints ?? 0,
                "total_number_of_acceleration" => $this->playerChart->total_number_of_acceleration ?? 0,
                "total_low_tempo" => $this->playerChart->total_low_tempo ?? number_format(0, 2),
                "total_mid_tempo" => $this->playerChart->total_mid_tempo ?? number_format(0, 2),
                "total_high_tempo" => $this->playerChart->total_high_tempo ?? number_format(0, 2),
                "max_sprint_speed" => $this->playerChart->max_sprint_speed ?? number_format(0, 2),
                "max_acceleration" => $this->playerChart->max_acceleration ?? number_format(0, 2),
                "max_dribbling_speed" => $this->playerChart->max_dribbling_speed ?? number_format(0, 2),
                "max_receiving_speed" => $this->playerChart->max_receiving_speed ?? number_format(0, 2),
                "max_speed_during_passing" => $this->playerChart->max_speed_during_passing ?? number_format(0, 2),
                "max_speed_during_shooting" => $this->playerChart->max_speed_during_shooting ?? number_format(0, 2),
                "total_shot_power" => $this->playerChart->total_shot_power ?? number_format(0, 2)
            ],
            "teamCharts" => [
                "total_attempts" => $this->teamCharts->total_attempts ?? 0,
                "avg_dribbling_distance" => $this->teamCharts->avg_dribbling_distance ?? number_format(0, 2),
                "avg_number_of_passes" => $this->teamCharts->avg_number_of_passes ?? number_format(0, 2),
                "avg_number_of_shots" => $this->teamCharts->avg_number_of_shots ?? number_format(0, 2),
                "avg_number_of_receivings" => $this->teamCharts->avg_number_of_receivings ?? number_format(0, 2),
                "avg_number_of_ball_touches" => $this->teamCharts->avg_number_of_ball_touches ?? number_format(0, 2),
                "avg_running_distance" => $this->teamCharts->avg_running_distance ?? number_format(0, 2),
                "avg_number_of_sprints" => $this->teamCharts->avg_number_of_sprints ?? number_format(0, 2),
                "avg_number_of_acceleration" => $this->teamCharts->avg_number_of_acceleration ?? number_format(0, 2),
                "total_low_tempo" => $this->teamCharts->total_low_tempo ?? number_format(0, 2),
                "total_mid_tempo" => $this->teamCharts->total_mid_tempo ?? number_format(0, 2),
                "total_high_tempo" => $this->teamCharts->total_high_tempo ?? number_format(0, 2),
                "max_sprint_speed" => $this->teamCharts->max_sprint_speed ?? number_format(0, 2),
                "max_acceleration" => $this->teamCharts->max_acceleration ?? number_format(0, 2),
                "max_dribbling_speed" => $this->teamCharts->max_dribbling_speed ?? number_format(0, 2),
                "max_receiving_speed" => $this->teamCharts->max_receiving_speed ?? number_format(0, 2),
                "max_speed_during_passing" => $this->teamCharts->max_speed_during_passing ?? number_format(0, 2),
                "max_speed_during_shooting" => $this->teamCharts->max_speed_during_shooting ?? number_format(0, 2),
                "avg_shot_power" => $this->teamCharts->avg_shot_power ?? number_format(0, 2)
            ]
        ];
    }
}