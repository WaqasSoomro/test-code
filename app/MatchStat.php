<?php

namespace App;

use App\Traits\GraphData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MatchStat extends Model
{
    use GraphData;

    protected $table = 'matches_stats';
    public $timestamps = true;
    protected $fillable = ['match_id', 'stat_type_id', 'stat_value', 'player_id', 'imei'];

    protected $casts = [
      'init_ts' => 'Y-m-d'
    ];

    /**
     * Get the match that owns the MatchStat.
     */
    public function match()
    {
        return $this->belongsTo('App\Match','match_id','id');
    }

    /**
     * Get the match that owns the MatchStat.
     */
    public function match_stat_types()
    {
        return $this->belongsTo('App\MatchStatType','stat_type_id');
    }


    /**
     * Get the user that owns the MatchStat.
     */
    public function user()
    {
        return $this->belongsTo('App\User','player_id','id');
    }

    public static $get_single_stat_record_rules = [
        'stat_type_id' => 'required',
        'user_id' => 'required|exists:users,id',
        //'period' => 'in:daily,weekly,monthly,yearly'
    ];




    private static function transformDataForChart($graph_data, $graph_type = 'linear')
    {
        if(count($graph_data) == 0) return [];

        $results = [];

        $labels = [];
        $data_1 = [];
        $data_2 = [];

        if($graph_type == 'bar_chart'){
            foreach ($graph_data as $item){
                array_push($labels, $item->stat_type);
                if($item->name=='Player'){
                    //for data 1
                    array_push($data_1, $item->avg_stat);
                    array_push($data_2, 0);
                }
                else{
                    //for data 2
                    array_push($data_1, 0);
                    array_push($data_2, $item->avg_stat);
                }

                $labels = array_unique($labels);
            }
        }
        else{
            foreach ($graph_data as $item){
                array_push($labels, Carbon::parse(strtotime($item->start_date))->format('Y-m-d'));
                if($item->name=='Player'){
                    array_push($data_1, $item->avg_stat);
                    array_push($data_2, 0);
                }else{
                    array_push($data_1, 0);
                    array_push($data_2, $item->avg_stat);
                }
            }
        }


        $results['labels'] = $labels;
        $results['data_1'] = $data_1;
        $results['data_2'] = $data_2;

        return $results;
    }


    public static function topRecords($request){
        return self::getTopRecords($request);
    }


    public static function getGraphData1($user_id, $team_id, $stat_type_id, $duration='all',$filters=[], $graph_type = 'linear',$compare_with='team'){
        if($graph_type == 'bar_chart'){
            $data = [];
            foreach ($stat_type_id as $id){
                $data[] = self::GraphData1($user_id,$team_id , $id, $duration,$filters,$compare_with);
            }
            $data = array_flatten($data);
        }
        else{
            $data = self::GraphData1($user_id,$team_id , $stat_type_id, $duration,$filters,$compare_with);
        }


        return self::transformDataForChart($data, $graph_type);
    }



    public static function speedZoneGraph($user_id, $team_id, $stat_type_id, $duration='all',$filters=[], $compare_with='team'){
        $data = [];
        foreach ($stat_type_id as $id){
            $data[] = self::GraphData1($user_id,$team_id , $id, $duration,$filters,$compare_with);
        }
        $graph_data = array_flatten($data);
        if(count($graph_data) == 0) return [];
        $speed_walking_1=[0];
        $speed_sprinting_1=[0];
        $speed_running_1=[0];
        $speed_walking_2=[0];
        $speed_sprinting_2=[0];
        $speed_running_2=[0];
        $labels=[];
        foreach ($graph_data as $item){
            array_push($labels, $item->stat_type);
            if($item->name=='Player'){
                //for data 1
                switch ($item->stat_type){
                    case "SPEED_WALKING"    : $speed_walking_1[]=  $item->avg_stat;
                                                break;
                    case "SPEED_SPRINTING"  : $speed_sprinting_1[]= $item->avg_stat;
                                                break;
                    case "SPEED_RUNNING"    : $speed_running_1[]= $item->avg_stat;
                                                break;
                    default : "";
                }
            }
            else{
                //for data 2
                switch ($item->stat_type){
                    case "SPEED_WALKING"    : $speed_walking_2[]=  $item->avg_stat;
                                                break;
                    case "SPEED_SPRINTING"  : $speed_sprinting_2[]= $item->avg_stat;
                                                break;
                    case "SPEED_RUNNING"    : $speed_running_2[]= $item->avg_stat;
                                                break;
                    default : "";
                }
            }
            $labels = array_unique($labels);
        }

        $data_1=[max($speed_walking_1),max($speed_sprinting_1),max($speed_running_1)];
        $data_2=[max($speed_walking_2),max($speed_sprinting_2),max($speed_running_2)];
        $graph_data =[
            'labels'=>$labels,
            'data_1'=>$data_1,
            'data_2'=>$data_2
        ];
        return $graph_data;
    }
}
