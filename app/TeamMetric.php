<?php

namespace App;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TeamMetric extends Model
{
    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function addTeamMetric($request)
    {

        DB::beginTransaction();
        try{
            $record = TeamMetric::whereTeamId($request->team_id)->first();
            if(empty($record)){
                $record = new TeamMetric;
            }

            $record->team_id            = $request->team_id;
            $record->created_by         = auth()->user()->id;
            $record->lines               = json_encode($request->lines);
            $record->position           = json_encode($request->position);
            $record->player_id          = json_encode($request->player_id);
            $record->metric_type        = $request->metric_type;
            $record->kick_strength      = $request->kick_strength;
            $record->max_speed          = $request->max_speed;
            $record->leg_distribution   = $request->leg_distribution;
            $record->ball_kicks         = $request->ball_kicks;
            $record->total_distance     = $request->total_distance;
            $record->impact             = $request->impact;
            $record->save();

            $response = Helper::apiSuccessResponse(true, 'Team metric has been saved successfully', new \stdClass());
        }catch (\Exception $exception){
            DB::rollback();
            $response = Helper::apiErrorResponse(false, $exception->getMessage(), new \stdClass());
        }

        return $response;
    }
}
