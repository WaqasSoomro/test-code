<?php

namespace App\Traits;

use App\MatchDetails;
use App\MatchStat;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait GraphData
{

    public static function matchDetailsTopRecordsQueries($select,$event_type=null,$team_id=null){
        $records =  MatchDetails::selectRaw($select)
            ->join('users', 'match_details.user_id', '=', 'users.id')
            ->join('player_team', 'match_details.user_id', '=', 'player_team.user_id')
            ->join('players', 'match_details.user_id', '=', 'players.user_id');
        if(isset($event_type)){
          $records = $records->where('event_type', '=', $event_type);
        }
        if(isset($team_id)){
            $records = $records->where('player_team.team_id',$team_id);
        }
        return $records;
    }


    public static function matchStatsTopRecordsQuery($select, $state_type,$team_id=null){
        $speed = MatchStat::selectRaw($select)
            ->join('matches_stats_types', 'matches_stats_types.id', '=', 'matches_stats.stat_type_id')
            -> join('users', 'matches_stats.player_id', '=', 'users.id')
            ->join('players', 'matches_stats.player_id', '=', 'players.user_id')
            ->join('player_team', 'players.user_id', '=', 'player_team.user_id')
            ->where('matches_stats_types.name', '=', $state_type);
        if(isset($team_id)){
            $speed = $speed->where('player_team.team_id',$team_id);
        }
        return $speed;
    }



    public static function filterTopMatchDetails($records,$filters=null,$duration=null){
        if(is_array($duration) && isset($duration['from'])){
            $records = $records->whereDate('match_details.created_at','>=',$duration['from'])
                ->whereDate('match_details.created_at','<=',$duration['to']);
        }
        if(isset($filters)){
            if(isset($filters['position'])){
                $records = $records->where('players.position_id',$filters['position']);
            }if(isset($filters['height'])){
                $records = $records->where('players.height','<=',$filters['height']);
            }if(isset($filters['weight'])){
                $records = $records->where('players.weight','<=',$filters['weight']);
            }if(isset($filters['age_group'])){
                $records= $records->whereRaw('DATEDIFF(CURRENT_DATE, STR_TO_DATE(users.`date_of_birth`, \'%Y-%m-%d\'))/365 <='.$filters['age_group']);
            }
        }
        return $records;
    }

    public static function filterTopMatchStats($records,$filters=null,$duration=null){
        if(is_array($duration) && isset($duration['from'])){
            $records = $records->whereDate('matches_stats.created_at','>=',$duration['from'])
                ->whereDate('matches_stats.created_at','<=',$duration['to']);
        }
        if(isset($filters)){
            if(isset($filters['position'])){
                $records = $records->where('players.position_id',$filters['position']);
            }if(isset($filters['height'])){
                $records = $records->where('players.height','<=',$filters['height']);
            }if(isset($filters['weight'])){
                $records = $records->where('players.weight','<=',$filters['weight']);
            }if(isset($filters['age_group'])){
                $records= $records->whereRaw('DATEDIFF(CURRENT_DATE, STR_TO_DATE(users.`date_of_birth`, \'%Y-%m-%d\'))/365 <='.$filters['age_group']);
            }
        }
        return $records;
    }
    public static function getTopRecords($request){
        $user = User::find($request->player_id);
        $players=[
            $request->player_id,
            isset($request->filter['player2'])?$request->filter['player2']:null
        ];
        $team_id = $user->teams[0]->id ?? null;
        if(isset($request->filter) && isset($request->filter['player2'])){
            $team_id = null;
        }
        //kick strength
        $kick_strength = self::matchDetailsTopRecordsQueries('MAX(`match_details`.`event_magnitude`) AS \'MAX_STRENGTH\' ,
	                    MIN(`match_details`.`event_magnitude`) AS \'MIN_STRENGTH\',
	                    AVG(`match_details`.`event_magnitude`) AS \'AVG_STRENGTH\'','BK');
        $kick_strength = self::filterTopMatchDetails($kick_strength,$request->filter,$team_id);
        $ks = $kick_strength->first();

        //maximum speed
        $maximum_speed= self::matchStatsTopRecordsQuery('MAX(stat_value) AS max, MIN(stat_value) AS min , AVG(stat_value) AS avg','SPEED_MAX');
        $maximum_speed = self::filterTopMatchStats($maximum_speed,$request->filter,$team_id);

        $mx = $maximum_speed->first();


        //leg distribution
        $leg_distribution = self::matchDetailsTopRecordsQueries("COUNT(CASE WHEN foot='R' THEN 1 END) AS Foot_Right,
                            COUNT(CASE WHEN foot='L' THEN 1 END) AS Foot_Left");
        if(!isset($request->filter['player2'])){
            $leg_distribution = $leg_distribution->where('player_team.team_id',$team_id);
        }

        if(isset($request->from) && isset($request->to)){
            $leg_distribution = $leg_distribution->whereDate('match_details.created_at','>=',$request->from)
                ->whereDate('match_details.created_at','<=',$request->to);
        }
          $leg_distribution = $leg_distribution ->first();
        $response = [
            'top_records' => [
                'kick_strength' => [
                    'value' =>  $kick_strength->whereIn('match_details.user_id',$players)->max('match_details.event_magnitude'),
                    'min' => $ks->MIN_STRENGTH,
                    'max' => $ks->MAX_STRENGTH,
                    'avg'=> $ks->AVG_STRENGTH ? number_format($ks->AVG_STRENGTH, 2) : '0',
                ],
                'max_speed' => [
                    'value' =>  $maximum_speed->whereIn('matches_stats.player_id',$players)->max('matches_stats.stat_value'),
                    'min' => $mx->min,
                    'max' => $mx->max,
                    'avg'=>$mx->avg,
                    'avg'=>$mx->avg ? number_format($mx->avg, 2) : '0',
                ],
            'leg_distribution'=>[
                'foot_left'=>$leg_distribution->Foot_Left,
                'foot_right'=>$leg_distribution->Foot_Right,
                'avg'=>($leg_distribution->Foot_Left+$leg_distribution->Foot_Right)/2
            ]
            ],
        ];
        return $response;
    }



    public static function getSessionMetrics($request){
        $user = User::find($request->player_id);
        $players=[
            $request->player_id,
            isset($request->filter['player2'])?$request->filter['player2']:null
        ];
        $team_id = $user->teams[0]->id ?? null;
        if(isset($request->filter) && isset($request->filter['player2'])){
            $team_id = null;
        }
        //ball kicks

        $ball_kicks = self::matchDetailsTopRecordsQueries('MAX(`match_details`.`event_magnitude`) AS \'max\' ,
	                    MIN(`match_details`.`event_magnitude`) AS \'min\',
	                    AVG(`match_details`.`event_magnitude`) AS \'avg\'','BK');
        $ball_kicks = self::filterTopMatchDetails($ball_kicks,$request->filter,$team_id);

        $bc = $ball_kicks->first();



        $total_distance= self::matchStatsTopRecordsQuery('MAX(stat_value) AS max, MIN(stat_value) AS min , AVG(stat_value) AS avg','TOTAL_DISTANCE',$team_id);
        $total_distance = self::filterTopMatchStats($total_distance,$request->filter,$team_id);

        $td = $total_distance->first();



        $received_impacts = self::matchDetailsTopRecordsQueries('MAX(`match_details`.`event_magnitude`) AS \'max\' ,
	                    MIN(`match_details`.`event_magnitude`) AS \'min\',
	                    AVG(`match_details`.`event_magnitude`) AS \'avg\'','FK');
        $received_impacts = self::filterTopMatchDetails($received_impacts,$request->filter,$team_id);
        $r_impacts = $received_impacts->first();

        $response = [
            'session_metrics' => [
                'ball_kick' => [
                    'value' => $ball_kicks->whereIn('match_details.user_id',$players)->max('match_details.event_magnitude'),
                    'min' => $bc->min,
                    'max' => $bc->max,
                    'avg'=>$bc->avg ? number_format($bc->avg, 2) : 0
                ],
                'total_distance' => [
                    'value' => $total_distance->whereIn('matches_stats.player_id',$players)->max('stat_value'),
                    'min' => $td->min,
                    'max' => $td->max,
                    'avg'=>$td->avg ? number_format($td->avg, 2) : 0
                ],
                'received_impacts' => [
                    'value' =>  $received_impacts->whereIn('match_details.user_id',$players)->max('match_details.event_magnitude'),
                    'min' => $r_impacts->min,
                    'max' => $r_impacts->max,
                    'avg'=>$r_impacts->avg ? number_format($r_impacts->avg, 2) : 0,
                ],
            ]
        ];
        return $response;
    }


    static function GraphData1($user_id,$user2_id, $stat_type_id, $duration = null,$filters=[],$compare_with='team')
    {
        $results = null;
        if (!is_array($stat_type_id)) {
            $stat_type_id = [$stat_type_id];
        }
        $data_set_1 = self::userQuery($user_id,$stat_type_id);
        $data_set_1= self::filterUser($data_set_1,$duration,$filters);
        if($compare_with==='team'){
            $data_set_2= self::teamQuery($user2_id,$stat_type_id);
            $data_set_2 = self::filterTeam($data_set_2,$duration,$filters);
        }elseif ($compare_with==='player'){
            $data_set_2 = self::userQuery($user2_id,$stat_type_id,'Player2');
            $data_set_2= self::filterUser($data_set_2,$duration,$filters);
        }
        $results = $data_set_2->union($data_set_1)->get();
        if (!isset($results) || count($results) == 0) {
            return [];
        }
        $graphData = $results->sortBy('matches.init_ts AS start_date')->values()->all();
        return $graphData;
    }



    public static  function userQuery($user_id,$stat_type_id,$data_set_title = 'Player'){
        $user_data = MatchStat::selectRaw('
                   matches_stats_types.name AS stat_type, matches.init_ts AS start_date ,
                   YEAR(matches.init_ts)	AS YEAR ,MONTH(matches.init_ts)	AS MONTH,WEEK(matches.init_ts) AS WEEK ,DAY(matches.init_ts) AS DAY ,
                   AVG(stat_value) AS avg_stat ,matches_stats.player_id AS Id,"'.$data_set_title.'" AS name,
                    DATEDIFF(CURRENT_DATE, STR_TO_DATE(users.`date_of_birth`, \'%Y-%m-%d\'))/365 AS age'
        )->join('matches', 'matches.id', '=', 'matches_stats.match_id')
            ->join('matches_stats_types', 'matches_stats_types.id', '=', 'matches_stats.stat_type_id')
            ->join('players', 'matches_stats.player_id', '=', 'players.user_id')
            ->join('users', 'players.user_id', '=', 'users.id')
            ->where('matches_stats.player_id', $user_id)
            ->whereIn('matches_stats.stat_type_id', $stat_type_id);
        return $user_data;

    }
    public static function teamQuery($team_id,$stat_type_id,$data_set_title = 'Team'){
        $team_data = MatchStat::selectRaw('
                    matches_stats_types.name AS stat_type, matches.init_ts AS start_date , YEAR(matches.init_ts) AS YEAR ,MONTH(matches.init_ts)	AS MONTH,WEEK(matches.init_ts) AS WEEK ,DAY(matches.init_ts) AS DAY ,
                     AVG(stat_value) AS avg_stat , player_team.team_id AS Id,"'.$data_set_title.'" AS name, "0" AS age'
        )
            ->join('matches', 'matches.id', '=', 'matches_stats.match_id')
            ->join('matches_stats_types', 'matches_stats_types.id', '=', 'matches_stats.stat_type_id')
            ->join('player_team', 'player_team.user_id', '=', 'matches_stats.player_id')
            ->where('player_team.team_id', $team_id)
            ->whereIn('matches_stats.stat_type_id', $stat_type_id);

        return $team_data;
    }




    public static function filterUser($data_set_1, $duration,$filters=[]){
        if(is_array($duration) && isset($duration['from'])){
            $data_set_1 = $data_set_1->whereDate('matches.init_ts','>=',$duration['from'])
                ->whereDate('matches.init_ts','<=',$duration['to']);
        }
        if(isset($filters)){
            if(isset($filters['position'])){
                $data_set_1 = $data_set_1->where('players.position_id',$filters['position']);
            }if(isset($filters['height'])){
                $data_set_1 = $data_set_1->where('players.height','<=',$filters['height']);
            }if(isset($filters['weight'])){
                $data_set_1 = $data_set_1->where('players.weight','<=',$filters['weight']);
            }if(isset($filters['age_group'])){
                $data_set_1= $data_set_1->whereRaw('DATEDIFF(CURRENT_DATE, STR_TO_DATE(users.`date_of_birth`, \'%Y-%m-%d\'))/365 <='.$filters['age_group']);
            }
        }
        if($duration==='day'){
            $data_set_1= $data_set_1->whereRaw(DB::raw('CAST(matches.init_ts AS DATE ) = CAST(NOW() AS DATE )'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_id,WEEK(matches.init_ts),DAY(matches.init_ts)'));
        }elseif ($duration==='week'){
            $data_set_1= $data_set_1->whereRaw(DB::raw('WEEK(matches.init_ts) = WEEK(NOW())'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_id,WEEK(matches.init_ts)'));
        }elseif ($duration==='month'){
            $data_set_1= $data_set_1->whereRaw(DB::raw('MONTH(matches.init_ts) = MONTH(NOW())'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_id'));;
        }elseif ($duration==='3-months'){
            $data_set_1= $data_set_1->whereRaw(DB::raw('matches.init_ts > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_id, WEEK(matches.init_ts)'));
        }elseif ($duration==='6-months'){
            $data_set_1= $data_set_1->whereRaw(DB::raw('matches.init_ts > DATE_SUB(CURDATE(), INTERVAL 6 MONTH)'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_id, WEEK(matches.init_ts)'));
        }elseif ($duration==='year'){
            $data_set_1= $data_set_1->whereRaw(DB::raw('YEAR(matches.init_ts) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_id'));
        }elseif ($duration==='all'){
            $data_set_1= $data_set_1->whereRaw(DB::raw('YEAR(matches.init_ts) < YEAR(NOW())'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),player_id'));
        }else{
            $data_set_1=$data_set_1->
            groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_id,WEEK(matches.init_ts),DAY(matches.init_ts)'));
        }
        return $data_set_1;
    }

    public static function filterTeam($data_set_2,$duration,$filters=[]){
        if(is_array($duration) && isset($duration['from'])){
            $data_set_2 = $data_set_2->whereDate('matches.init_ts','>=',$duration['from'])
                ->whereDate('matches.init_ts','<=',$duration['to']);
        }
        if($duration==='day'){
            $data_set_2=$data_set_2->whereRaw(DB::raw('CAST(matches.init_ts AS DATE ) = CAST(NOW() AS DATE )'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_team.team_id,WEEK(matches.init_ts),DAY(matches.init_ts)'));
        }elseif ($duration==='week'){
            $data_set_2=$data_set_2->whereRaw(DB::raw('WEEK(matches.init_ts) = WEEK(NOW())'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_team.team_id,WEEK(matches.init_ts)'));
        }elseif ($duration==='month'){
            $data_set_2=$data_set_2->whereRaw(DB::raw('MONTH(matches.init_ts) = MONTH(NOW())'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_team.team_id'));;
        }elseif ($duration==='3-months'){
            $data_set_2=$data_set_2->whereRaw(DB::raw('matches.init_ts > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_team.team_id, WEEK(matches.init_ts)'));
        }elseif ($duration==='6-months'){
            $data_set_2=$data_set_2->whereRaw(DB::raw('matches.init_ts > DATE_SUB(CURDATE(), INTERVAL 6 MONTH)'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_team.team_id, WEEK(matches.init_ts)'));
        }elseif ($duration==='year'){
            $data_set_2 = $data_set_2->whereRaw(DB::raw('YEAR(matches.init_ts) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_team.team_id'));
        }elseif ($duration==='all'){
            $data_set_2 = $data_set_2->whereRaw(DB::raw('YEAR(matches.init_ts) < YEAR(NOW())'))
                ->groupBy(DB::raw('YEAR(matches.init_ts),player_team.team_id'));;
        }else{
            $data_set_2=$data_set_2->groupBy(DB::raw('YEAR(matches.init_ts),MONTH(matches.init_ts),player_team.team_id,WEEK(matches.init_ts),DAY(matches.init_ts)'));
        }
        return $data_set_2;
    }

}
