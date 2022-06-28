<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Match;
use App\MatchDetails;
use App\MatchStat;
use App\TrainingSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



/**
 * @group Player Apis
 *
 */

class GraphController extends Controller
{




    /**
     * getSessionDetailsGraph
     *
     * @queryParam  session_id required session id is required
     * @queryParam  stat_type required string ball_kicks,impacts,max_speed,ball_touches,distance,max_shot_speed,heart_rate,distance
     * @queryParam  filter
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Records found",
    "Result": {
    "labels": [
    10,
    20,
    30,
    40
    ],
    "data_1": [
    34,
    22,
    278,
    98
    ]
    }
    }
     *
     *
     */

    public function sessionDetailsGraph(Request $request){
        $this->validate($request,[
            'session_id'=>'required',
            'stat_type'=>'required|in:ball_kicks,impacts,max_speed,ball_touches,distance,max_shot_speed,heart_rate,distance,low_tempo,mid_tempo,high_tempo,sprints',
        ]);
        $state = $request->stat_type;
//        $session = TrainingSession::find($request->session_id);
        $session = Match::find($request->session_id);
        if(!$session){
            return  Helper::apiErrorResponse(false,'Not found',new \stdClass());
        }
        $match_id = $session->id;
//        $match = Match::find($match_id);
//        $total_minutes = Carbon::parse($match->start_time)->diffInMinutes(Carbon::parse($match->end_time));
        $match_stats =null;
        if($state==='ball_kicks'){
            $match_stats = MatchDetails::select(
                DB::raw('COUNT(*) AS stat, date(event_ts), hour(event_ts), floor(minute(event_ts) / 10) AS minutes, event_ts')
            )->where('event_type','BK');
        }if($state==='impacts'){
            $match_stats = MatchDetails::select(
                DB::raw('COUNT(*) AS stat, date(event_ts), hour(event_ts), floor(minute(event_ts) / 10) AS minutes')
            )->where('event_type','FK');
        }if($state==='max_speed'){
            $match_stats = MatchDetails::select(
                DB::raw('Max(speed) AS stat, date(event_ts), hour(event_ts), floor(minute(event_ts) / 10) AS minutes')
            );
        }if($state==='ball_touches'){
            $match_stats = MatchDetails::select(
                DB::raw('COUNT(*) AS stat, date(event_ts), hour(event_ts), floor(minute(event_ts) / 10) AS minutes')
            )->whereIn('event_type',['FK','BK']);
        }if($state==='distance'){
            $match_stats = MatchDetails::select(
                DB::raw('COUNT(steps) AS stat, date(event_ts), hour(event_ts), floor(minute(event_ts) / 10) AS minutes')
            );
        }if($state==='max_shot_speed'){
            $match_stats = MatchDetails::select(
                DB::raw('MAX(event_magnitude) AS stat, date(event_ts), hour(event_ts), floor(minute(event_ts) / 10) AS minutes')
            )->where('event_type','BK');
        }if($state==='heart_rate'){
            $match_stats = MatchDetails::select(
                DB::raw('AVG(hr) AS stat, date(event_ts), hour(event_ts), floor(minute(event_ts) / 10) AS minutes')
            );
        }

        if($match_stats){
            $match_stats = $match_stats->where('event_id',$match_id)->groupBy(DB::raw('minutes'))->orderBy('event_ts','ASC')
                ->get();
            $data = $match_stats->pluck('stat');
        }


        if($state ==='low_tempo'){
            $match_stats = MatchStat::where('match_id',$match_id)->where('stat_type_id',4)->get();
            $data = $match_stats->pluck('stat_value');
        }if($state ==='mid_tempo'){
            $match_stats = MatchStat::where('match_id',$match_id)->where('stat_type_id',17)->get();
            $data = $match_stats->pluck('stat_value');

        }if($state ==='high_tempo'){
            $match_stats = MatchStat::where('match_id',$match_id)->where('stat_type_id',6)->get();
            $data = $match_stats->pluck('stat_value');
        }if($state ==='sprints'){
            $match_stats = [0];
            $data =[0];
        }
        if(count($data) <= 0) {
            return Helper::apiErrorResponse(false, 'No Records found', new \stdClass());
        }
        $min=0;
        $labels=[];
        for ($i = 0; $i < count($match_stats); $i++){
            $labels[]=$min+=10;
        }

        $response =['labels'=>$labels,'data_1'=>$data];
        return Helper::apiSuccessResponse(true, 'Records found', $response);
    }



    /**
     * getTempoGraph
     *
     * @queryParam  player_id required player id is required
     * @queryParam  filter required all, week, month,3-month,6-month,year
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Records found successfully!",
    "Result": {
    "labels": [
    "2020-11-03",
    "2020-11-02",
    "2020-10-15"
    ],
    "data_1": [
    51.48579545454545,
    50.34626038781163,
    48.203342618384404
    ]
    }
    }
     *
     *
     */


    public function getTempoGraph(Request $request){
        $this->validate($request,['player_id'=>'required']);
        $user_data = MatchStat::selectRaw('date(matches.init_ts) AS date,AVG(stat_value) AS avg_stat')
            ->join('matches_stats_types', 'matches_stats_types.id', '=', 'matches_stats.stat_type_id')
            ->join('matches', 'matches.id', '=', 'matches_stats.match_id')
            ->where('matches_stats.player_id', $request->player_id)
            ->whereIn('matches_stats_types.name', ['SPEED_AVG'])
        ;
        $user_data = $this->filterGraph($user_data,'matches.init_ts',$request->filter);
        $user_data = $user_data->get();
//        if(count($user_data)){
            $labels= $user_data->pluck('date');
            $data= $user_data->pluck('avg_stat');
//        }
        $graph_data =[
            'labels'=>$labels,
            'data_1'=>$data,
        ];
        return Helper::apiSuccessResponse(true, 'Records found successfully!', $graph_data);
    }





    public function filterGraph($data,$date_col,$filter){

        if($filter==='week'){
            $data = $data ->whereRaw(DB::raw("$date_col > DATE_SUB(CURDATE(), INTERVAL 7 DAY)"))
                ->groupBy(DB::raw("DATE($date_col)"));
        }if($filter==='month'){
            $data = $data ->whereRaw(DB::raw("$date_col > DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"))
                ->groupBy(DB::raw("WEEK($date_col)"));
        }if($filter==='3-month'){
            $data = $data ->whereRaw(DB::raw("$date_col > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)"))
                ->groupBy(DB::raw("WEEK($date_col)"));
        }if($filter==='6-month'){
            $data = $data ->whereRaw(DB::raw("$date_col > DATE_SUB(CURDATE(), INTERVAL 6 MONTH)"))
                ->groupBy(DB::raw("WEEK($date_col)"));
        }if($filter==='year'){
            $data = $data ->whereRaw(DB::raw("$date_col > DATE_SUB(CURDATE(), INTERVAL 1 YEAR)"))
                ->groupBy(DB::raw("MONTH($date_col)"));
        }else{
            $data = $data ->groupBy(DB::raw("YEAR($date_col)"));
        }
//        if($filter==='daily'){
//            $data = $data ->whereRaw(DB::raw("$date_col > DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"))
//                ->groupBy(DB::raw("DATE($date_col)"));
//        }elseif ($filter==='week'){
//            $data = $data ->whereRaw(DB::raw("$date_col > DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"))
//                ->groupBy(DB::raw("WEEK($date_col)"));
//        }elseif ($filter==='month'){
//            $data = $data ->whereRaw(DB::raw("$date_col > DATE_SUB(CURDATE(), INTERVAL 12 MONTH)"))
//                ->groupBy(DB::raw("MONTH($date_col)"));
//        }elseif ($filter==='month'){
//            $data = $data ->whereRaw(DB::raw("$date_col > DATE_SUB(CURDATE(), INTERVAL 12 MONTH)"))
//                ->groupBy(DB::raw("MONTH($date_col)"));
//        }elseif ($filter==='yearly'){
//            $data= $data->groupBy(DB::raw("YEAR($date_col)"));
//        }else{
//            $data= $data->limit(12)->groupBy(DB::raw("DATE($date_col)"));
//        }
        return $data;
    }

    /**
     * getShotsGraph
     *
     * @queryParam  player_id required player id is required
     * @queryParam  filter required all, week, month,3-month,6-month,year
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Records found successfully!",
    "Result": {
    "labels": [
    "2020-11-03",
    "2020-11-02",
    "2020-10-15"
    ],
    "data_1": [
    205,
    97,
    257
    ]
    }
    }
     *
     *
     */

    public function getShotsGraph(Request $request){
        $this->validate($request,['player_id'=>'required']);
        $match_stats = MatchDetails::select(
            DB::raw('MAX(event_magnitude) AS stat, date(event_ts) as date')
        )->where('event_type','BK')->where('user_id',$request->player_id);
        $match_stats = $this->filterGraph($match_stats,'event_ts',$request->filter)->get();
        $labels= $match_stats->pluck('date');
        $data= $match_stats->pluck('stat');
        $graph_data =[
            'labels'=>$labels,
            'data_1'=>$data,
        ];
        return Helper::apiSuccessResponse(true, 'Records found successfully!', $graph_data);
    }




    /**
     * getLegDistributionGraph
     *
     * @queryParam  player_id required player id is required
     * @queryParam  filter required all, week, month,3-month,6-month,year
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Records found successfully!",
    "Result": {
    "labels": [
    "2020-11-03",
    "2020-11-02",
    "2020-10-15"
    ],
    "data_1": [
    "0.0500",
    "99.9500",
    "99.9500"
    ],
    "data_2": [
    "0.0500",
    "99.9500",
    "99.9500"
    ]
    }
    }
     *
     *
     */


    public function getLegDistributionGraph(Request $request){
        $this->validate($request,['player_id'=>'required']);
        $match_details = DB::query()->selectRaw("date, CAST(left_foot/total*100 AS INT) AS left_percentage, CAST(right_foot/total*100 AS INT) AS right_percentage")
            ->fromSub(function($query) use($request){
                $query->selectRaw("
                    COUNT(CASE WHEN foot='R' THEN 1 END) AS right_foot,
                    COUNT(CASE WHEN foot='L' THEN 1 END) AS left_foot,
                    COUNT(CASE WHEN  foot IN ('L','R') THEN 1 END) AS total,
                    date(event_ts) as date
                    FROM match_details  WHERE user_id = ({$request->player_id})
                ");
            },"f");

        $match_details = $this->filterGraph($match_details,'date',$request->filter)->get();
        $graph_data =[
            'labels'=>$match_details->pluck('date'),
            'data_1'=>$match_details->pluck('left_percentage'),
            'data_2'=>$match_details->pluck('right_percentage'),
        ];
        return Helper::apiSuccessResponse(true, 'Records found successfully!', $graph_data);
    }

    //
}
