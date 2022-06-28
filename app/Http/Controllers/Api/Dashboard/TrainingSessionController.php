<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Helpers\Helper;
use App\Helpers\HumanOx;
use App\Http\Controllers\Controller;
use App\Match;
use App\MatchDetails;
use App\MatchStat;
use App\MatchStatType;
use App\TeamTrainer;
use App\User;
use App\UserSensor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use stdClass;

/**
 * @authenticated
 * @group Dashboard / Training Sessions
 * APIs For Training Sessions
 * User Auth Token is required in headers
 */
class TrainingSessionController extends Controller
{
    /**
     * GetTeamPlayers
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found",
     * "Result": [
     * {
     * "player_name": "Muhammad shahzaib.",
     * "team_name": "ManUtd U18",
     * "positions": [
            {
                "id": 3,
                "name": "Goal Keeper",
                "lines": 2,
                "pivot": {
                    "player_id": 1,
                    "position_id": 3
                },
                "line": {
                    "id": 2,
                    "name": "GoalKeepers"
                }
            }
        ],
     * "points": 0
     * },
     * {
     * "player_name": "Hasnain Ali",
     * "team_name": "ManUtd U18",
     * "positions": [
            {
                "id": 3,
                "name": "Goal Keeper",
                "lines": 2,
                "pivot": {
                    "player_id": 1,
                    "position_id": 3
                },
                "line": {
                    "id": 2,
                    "name": "GoalKeepers"
                }
            }
        ],
     * "points": 0
     * }
     * ]
     *}
     *
     */
    public function getTeamPlayers($team_id)
    {
//        $team_id = Auth::user()->teams_trainers[0]->id ?? 0;
        if(TeamTrainer::whereTrainerUserIdAndTeamId(Auth::user()->id,$team_id)->count() == 0){
            return Helper::apiNotFoundResponse(false, 'Records not found ', []);
        }

        $players = User::role('player')->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.profile_picture')
            ->whereHas('teams', function ($q) use ($team_id) {
                $q->where('teams.id', $team_id);
            })
            ->whereHas('user_sensors')
            ->with([
                'player' => function ($q1) {
                    $q1->select('id', 'players.user_id', 'players.position_id');
                }
            ])
            ->with([
                'player.positions' => function ($query)
                {
                    $query->select('positions.id', 'name', 'lines');
                },
                'player.positions.line' => function ($query)
                {
                    $query->select('lines.id', 'name');
                }
            ])
            ->with([
                'leaderboards' => function ($q3) {
                    $q3->select('leaderboards.id', 'leaderboards.user_id', 'leaderboards.total_score');
                }
            ])
            ->orderBy('created_at')
            ->get();


        if (count($players) == 0) {
            return Helper::apiNotFoundResponse(false, 'Records not found', []);
        }

        $results = $players->map(function ($item) {
            $obj = new stdClass();
            $obj->id = $item->id;
            $obj->player_name = $item->first_name . ' ' . $item->last_name;
            $obj->team_name = Auth::user()->teams_trainers[0]->team_name ?? '';
            $obj->position = $item->player->position->name ?? '';
            $obj->points = $item->leaderboards->total_score ?? 0;
            if (count($item->user_sensors) > 0) {
                $obj->imeis = $item->user_sensors->pluck('imei');
            } else {
                $obj->imeis = [];
            }
            return $obj;
        });

        return Helper::apiSuccessResponse(true, 'Records found', $results);
    }

    /**
     * startTrainingSession
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Session started",
     * "Result": {}
     * }
     *
     * @bodyParam user_ids array required ids should be in array
     */
    public function startTrainingSession(Request $request)
    {
        Validator::make($request->all(), ['user_ids' => 'required'])->validate();

        $user_ids = [];
        if (gettype($request->user_ids) == 'string') {
            $user_ids = explode(',', $request->user_ids);
        } else {
            $user_ids = $request->user_ids;
        }

        $user_imeis = UserSensor::whereIn('user_id', $user_ids)->get();

        if (count($user_imeis) == 0) {
            return Helper::apiNotFoundResponse(false, 'imeis not found', new stdClass());
        }

        //getting auth token
        $auth = HumanOx::partnerLogin();

        if (gettype($auth) == 'integer') {
            return Helper::apiNotFoundResponse(false, 'Failed to get auth token', new stdClass());
        }

        $token = $auth->token;

        //getting matches
        $matches = [];
        foreach ($user_imeis as $ui) {
            $res = HumanOx::getMatch($ui->imei, $token);

            $match = Match::where('id', $res[0]->match_id)->first();
            if(!$match) {
                Match::create([
                    'id' => $res[0]->match_id,
                    'user_id' => $ui->user_id,
                    'init_ts' => now()
                ]);
            }

            if ($res != null && count($res) > 0) {
                foreach ($res as $key => $m) {
                    $m->user_id = $ui->user_id;
                    $matches[] = $m;
                }
            }
        }


        return Helper::apiSuccessResponse(true, 'Session started', $matches);
    }

    /**
     * endTrainingSession
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Session ended",
     * "Result": {}
     * }
     *
     * @bodyParam session array required In session key you need to give object containing(session_time, user_id), session_time format in timestamp
     */
    public function endTrainingSession(Request $request)
    {
        Validator::make($request->all(), [
            'matches.*.match_id' => 'required',
            'matches.*.user_id' => 'required',
            'matches.*.imei' => 'required'
        ])->validate();

        $user_ids[] = array();
        $matches = collect($request->all());

        if(count($matches) == 0){
            return Helper::apiNotFoundResponse(false, 'matches not found', new stdClass());
        }

        $user_ids = $matches->pluck('user_id');

        $user_imeis = UserSensor::whereIn('user_id', $user_ids)->get();

        if (count($user_imeis) == 0) {
            return Helper::apiNotFoundResponse(false, 'imeis not found', new stdClass());
        }

        //getting auth token
        $auth = HumanOx::partnerLogin();

        if (gettype($auth) == 'integer') {
            return Helper::apiNotFoundResponse(false, 'Failed to get auth token', new stdClass());
        }

        $token = $auth->token;


        //getting match_stats
        $stats = [];
        foreach ($matches as $match) {
            $match = (object)$match;
            $res = HumanOx::getMatchStats($match->match_id, $match->imei, $token);

            Match::where('id', $match->match_id)->update([
                'id' => $match->match_id,
                'end_ts' => now()
            ]);

            if ($res != null && count($res) > 0) {
                foreach ($res as $key => $stat) {

//                    $stat->user_id = $match->user_id;
//                    $stat->imei = $match->imei;
//                    $stats[] = $stat;

                    $stats_data[0]['match_id'] = $match->match_id;
                    $stats_data[0]['stat_type_id'] = 1;
                    $stats_data[0]['stat_value'] = $stat->distance;
                    $stats_data[0]['player_id'] = $match->user_id;
                    $stats_data[0]['imei'] = $match->imei;

                    $stats_data[1]['match_id'] = $match->match_id;
                    $stats_data[1]['stat_type_id'] = 15;
                    $stats_data[1]['stat_value'] = $stat->steps;
                    $stats_data[1]['player_id'] = $match->user_id;
                    $stats_data[1]['imei'] = $match->imei;

                    $stats_data[2]['match_id'] = $match->match_id;
                    $stats_data[2]['stat_type_id'] = 4;
                    $stats_data[2]['stat_value'] = $stat->walking;
                    $stats_data[2]['player_id'] = $match->user_id;
                    $stats_data[2]['imei'] = $match->imei;

                    $stats_data[3]['match_id'] = $match->match_id;
                    $stats_data[3]['stat_type_id'] = 17;
                    $stats_data[3]['stat_value'] = $stat->running;
                    $stats_data[3]['player_id'] = $match->user_id;
                    $stats_data[3]['imei'] = $match->imei;

                    $stats_data[4]['match_id'] = $match->match_id;
                    $stats_data[4]['stat_type_id'] = 6;
                    $stats_data[4]['stat_value'] = $stat->sprinting;
                    $stats_data[4]['player_id'] = $match->user_id;
                    $stats_data[4]['imei'] = $match->imei;

                    $stats_data[5]['match_id'] = $match->match_id;
                    $stats_data[5]['stat_type_id'] = 7;
                    $stats_data[5]['stat_value'] = $stat->maxspeed;
                    $stats_data[5]['player_id'] = $match->user_id;
                    $stats_data[5]['imei'] = $match->imei;

                    $stats_data[6]['match_id'] = $match->match_id;
                    $stats_data[6]['stat_type_id'] = 2;
                    $stats_data[6]['stat_value'] = $stat->avgspeed;
                    $stats_data[6]['player_id'] = $match->user_id;
                    $stats_data[6]['imei'] = $match->imei;

                    $stats_data[7]['match_id'] = $match->match_id;
                    $stats_data[7]['stat_type_id'] = 11;
                    $stats_data[7]['stat_value'] = $stat->max_hr;
                    $stats_data[7]['player_id'] = $match->user_id;
                    $stats_data[7]['imei'] = $match->imei;

                    $stats_data[8]['match_id'] = $match->match_id;
                    $stats_data[8]['stat_type_id'] = 3;
                    $stats_data[8]['stat_value'] = $stat->avg_hr;
                    $stats_data[8]['player_id'] = $match->user_id;
                    $stats_data[8]['imei'] = $match->imei;

                    $stats_data[9]['match_id'] = $match->match_id;
                    $stats_data[9]['stat_type_id'] = 14;
                    $stats_data[9]['stat_value'] = $stat->impacts;
                    $stats_data[9]['player_id'] = $match->user_id;
                    $stats_data[9]['imei'] = $match->imei;

                    $stats_data[10]['match_id'] = $match->match_id;
                    $stats_data[10]['stat_type_id'] = 14;
                    $stats_data[10]['stat_value'] = $stat->impacts;
                    $stats_data[10]['player_id'] = $match->user_id;
                    $stats_data[10]['imei'] = $match->imei;

                }
            }
        }

        MatchStat::insert($stats_data);

        return Helper::apiSuccessResponse(true, 'Session End', new stdClass());
    }

    /**
     * Update Graph data
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Graph updated",
     * "Result": {}
     * }
     *
     * @bodyParam user_ids array required ids should be in array
     */
    public function updateGraph(Request $request)
    {
        Validator::make($request->all(), [
            'matches.*.match_id' => 'required',
            'matches.*.user_id' => 'required',
            'matches.*.imei' => 'required'
        ])->validate();

        //getting auth token
        $auth = HumanOx::partnerLogin();

        if (gettype($auth) == 'integer') {
            return Helper::apiNotFoundResponse(false, 'Failed to get auth token', new stdClass());
        }

        $token = $auth->token;

        $matches = $request->all();

        foreach ($matches as $match) {

            $match_data = HumanOx::getMatchData($match['match_id'], $match['imei'], $token);

            if (gettype($match_data) == 'integer') continue;
            else if ($match_data == []) continue;
            else if ($match_data == null) continue;

            foreach ($match_data as $md) {
                MatchDetails::create([
                    'event_id' => $match['match_id'],
                    'user_id' => $match['user_id'],
                    'event_ts' => $md->event_ts,
                    'geo_lon' => $md->geo_lon,
                    'geo_lat' => $md->geo_lat,
                    'event_type' => $md->event_type,
                    'event_magnitude' => $md->event_magnitude,
                    'speed' => $md->speed,
                    'hr' => $md->hr,
                    // 'period' => $md->period ,
                    'steps' => $md->steps,
                    //'temperature' => $md->temperature
                ]);
            }

        }

        return Helper::apiSuccessResponse(true, 'graph updated', new stdClass());
    }
}
