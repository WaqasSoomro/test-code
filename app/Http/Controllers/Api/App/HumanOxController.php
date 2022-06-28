<?php
//
//namespace App\Http\Controllers\Api\App;
//
//use App\Helpers\Helper;
//use App\Helpers\HumanOx;
//use App\Http\Controllers\Controller;
//use App\Match;
//use App\MatchDetails;
//use App\MatchStat;
//use App\MatchStatType;
//use App\PlayerTeam;
//use App\TrainingSession;
//use App\User;
//use App\UserSensor;
//use Carbon\Carbon;
//use Illuminate\Http\JsonResponse;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Storage;
//use Illuminate\Support\Facades\Validator;
//use stdClass;
//
///**
// * @authenticated
// * @group HumanOx
// *
// * HumanOx Apis
// */
//class HumanOxController extends Controller
//{
//    /**
//     * User Match Stats
//     *
//     * @response {
//     * "Response": true,
//     * "StatusCode": 200,
//     * "Message": "Records found successfully!",
//     * "Result": [
//     * {
//     * "id": 1,
//     * "name": "TOTAL_DISTANCE",
//     * "value_min": 0,
//     * "value_max": 42,
//     * "description": "Total distance in kilometers",
//     * "image": null,
//     * "max_stat_value": 8110.8701171875
//     * },
//     * {
//     * "id": 2,
//     * "name": "SPEED_AVG",
//     * "value_min": 0,
//     * "value_max": 30,
//     * "description": "Average speed in km/h",
//     * "image": null,
//     * "max_stat_value": 17.68000030517578
//     * },
//     * {
//     * "id": 3,
//     * "name": "HR_AVG",
//     * "value_min": 40,
//     * "value_max": 200,
//     * "description": "Average Heartrate",
//     * "image": null,
//     * "max_stat_value": 148.6300048828125
//     * },
//     * {
//     * "id": 4,
//     * "name": "SPEED_WALKING",
//     * "value_min": 0,
//     * "value_max": 100,
//     * "description": "Walking or standing percentage",
//     * "image": null,
//     * "max_stat_value": 100
//     * },
//     * {
//     * "id": 6,
//     * "name": "SPEED_SPRINTING",
//     * "value_min": 0,
//     * "value_max": 100,
//     * "description": "Sprinting percentage",
//     * "image": null,
//     * "max_stat_value": 44.560001373291016
//     * },
//     * {
//     * "id": 7,
//     * "name": "SPEED_MAX",
//     * "value_min": 0,
//     * "value_max": 42,
//     * "description": "Max Speed",
//     * "image": null,
//     * "max_stat_value": 100
//     * },
//     * {
//     * "id": 8,
//     * "name": "BALL_KICKS",
//     * "value_min": 0,
//     * "value_max": 0,
//     * "description": "Ball kicks",
//     * "image": null,
//     * "max_stat_value": 589
//     * },
//     * {
//     * "id": 11,
//     * "name": "HR_MAX",
//     * "value_min": 0,
//     * "value_max": 200,
//     * "description": "Maximum Heartrate",
//     * "image": null,
//     * "max_stat_value": 185
//     * },
//     * {
//     * "id": 14,
//     * "name": "RECEIVED_IMPACTS",
//     * "value_min": 0,
//     * "value_max": 0,
//     * "description": "Received Impacts",
//     * "image": null,
//     * "max_stat_value": 829
//     * },
//     * {
//     * "id": 15,
//     * "name": "NUMBER_STEPS",
//     * "value_min": 0,
//     * "value_max": 0,
//     * "description": "Number of Steps",
//     * "image": null,
//     * "max_stat_value": 6255
//     * },
//     * {
//     * "id": 17,
//     * "name": "SPEED_RUNNING",
//     * "value_min": 0,
//     * "value_max": 0,
//     * "description": "Running percentage",
//     * "image": null,
//     * "max_stat_value": 74.41999816894531
//     * }
//     * ]
//     * }
//     *
//     */
//    public function get_match_stat_types()
//    {
//        $stats = MatchStatType::selectRaw('
//                    id, name ,display_name ,value_min,value_max,image,description'
//        )->whereHas('matches_stats', function ($q) {
//            $q->where('player_id', Auth::user()->id);
//        })->orWhere('image', '!=', NULL)->get();
//        if (count($stats) == 0) {
//            $stats = MatchStatType::where('image', '!=', NULL)->selectRaw('
//                    id, name ,display_name ,value_min,value_max,image,description'
//            )->get();
//            return Helper::apiSuccessResponse(true, 'Records found',$stats);
//        }
//        return Helper::apiSuccessResponse(true, 'Records found successfully!', $stats);
//    }
//
//    /**
//     * User Match Details
//     *
//     * @response {
//     * "Response": true,
//     * "StatusCode": 200,
//     * "Message": "Records found successfully!",
//     * "Result": {
//     * "labels": [
//     * "2020-10-06 14:40:18",
//     * "2020-08-26 00:12:44",
//     * "2020-08-25 21:28:57",
//     * "2020-08-21 14:27:47",
//     * "2020-08-20 14:33:28",
//     * "2020-08-12 00:16:37",
//     * "2020-08-11 22:59:44",
//     * "2020-08-10 20:33:45",
//     * "2020-08-09 02:10:58",
//     * "2020-08-07 03:31:56",
//     * "2020-08-06 03:54:18",
//     * "2020-08-05 02:21:57",
//     * "2020-07-16 13:51:33",
//     * "2020-07-15 23:38:13",
//     * "2020-07-14 00:38:01",
//     * "2020-07-13 14:45:51",
//     * "2020-07-07 01:29:46",
//     * "2020-07-06 17:32:34",
//     * "2020-07-02 02:02:05",
//     * "2020-06-25 16:16:41",
//     * "2020-06-24 16:28:01",
//     * "2017-08-26 20:42:53"
//     * ],
//     * "data_1": [
//     * 6.5,
//     * 0.004285714189921107,
//     * 0.0016666666294137638,
//     * 0.0009090908887711438,
//     * 0.0014285713966403688,
//     * 0.0012499999720603228,
//     * 0.004482758520492192,
//     * 1.5099999904632568,
//     * 0.029565217621300533,
//     * 0.06470588364583604,
//     * 0.09719999939203262,
//     * 197.89585651702635,
//     * 0.04599999934434891,
//     * 0.029999999329447746,
//     * 0.0949999988079071,
//     * 0.03454545394263484,
//     * 1.7544444698012538,
//     * 0.019999999552965164,
//     * 0.058499999437481166,
//     * 0.005999999865889549,
//     * 0.0941666664245228,
//     * 0.009999999776482582
//     * ],
//     * "data_2": [
//     * 6.5,
//     * 0.004285714189921107,
//     * 0.0016666666294137638,
//     * 0.0009090908887711438,
//     * 0.0014285713966403688,
//     * 0.0012499999720603228,
//     * 0.004482758520492192,
//     * 1.5099999904632568,
//     * 0.029565217621300533,
//     * 0.06470588364583604,
//     * 0.09719999939203262,
//     * 197.89585651702635,
//     * 0.04599999934434891,
//     * 0.029999999329447746,
//     * 0.0949999988079071,
//     * 0.03454545394263484,
//     * 1.7544444698012538,
//     * 0.019999999552965164,
//     * 0.058499999437481166,
//     * 0.005999999865889549,
//     * 0.0941666664245228,
//     * 0.009999999776482582
//     * ]
//     * }
//     * }
//     *
//     * @urlParam stat_type_id required
//     * @urlParam user_id required
//     * @urlParam duration optional duration can be in options(day,week,month,year,3-months,6-months,all) by default is all
//     *
//     * @return JsonResponse
//     */
//     public function get_single_stat_record(Request $request)
//    {
//        Validator::make($request->all(), MatchStat::$get_single_stat_record_rules)->validate();
//
//        $user = User::find($request->user_id);
//
//        $team_id = $user->teams[0]->id ?? 0;
//
//        if ($team_id == 0) {
//            return Helper::apiNotFoundResponse(false, 'This user does not belong to any team', []);
//        }
//
//        $duration = $request->duration;
//        if($request->from!=null){
//            $from = isset($request->from)?$request->from:'1970-01-01';
//            $to = isset($request->to)?$request->to:Carbon::today()->addDay();
//            $duration= ['from'=>$from,'to'=>$to];
//        }
//        if($request->graph_type == 'bar_chart'){
//            $stat_type_ids = explode(',', $request->stat_type_id);
//
//            $stat_types = MatchStatType::whereIn('id', $stat_type_ids)->get();
//            $graph_data = MatchStat::getGraphData1($request->user_id,$team_id,$stat_types->pluck('id'),$duration,[],'bar_chart');
//
////            $graph_data = MatchStat::getGraphData($request->user_id, $stat_types->pluck('id'), $team_id,$request->duration, $request->graph_type);
//
//            if(count($graph_data) == 0){
//                return Helper::apiNotFoundResponse(false, 'Records not found', []);
//            }
//        }
//        else{
//
//            $graph_data = MatchStat::getGraphData1($request->user_id,$team_id,$request->stat_type_id,$duration,[],'linear');
//
////            $graph_data = MatchStat::getGraphData($request->user_id, $request->stat_type_id, $team_id, $request->duration);
//
//            if (count($graph_data) == 0) {
//                return Helper::apiNotFoundResponse(false, 'Records not found', []);
//            }
//        }
//
//        return Helper::apiSuccessResponse(true, 'Records found successfully!', $graph_data);
//    }
//
//
//    /**
//     * Mount Sensor
//     *
//     * @response {
//     * "Response": true,
//     * "StatusCode": 200,
//     * "Message": "Sensor Mounted",
//     * "Result": {}
//     * }
//     *
//     * @bodyParam imei string required
//     *
//     * @return JsonResponse
//     */
//    public function mountSensor(Request $request)
//    {
//        Validator::make($request->all(), ['imei' => 'required'])->validate();
//
//        $exists = Auth::user()->humanox_username && Auth::user()->humanox_pin;
//
//        if (!$exists) {
//            //login first with jogo master acc
//            $sensor = Auth::user()->user_sensors;
//            $imei = '';
//
//            if(count($sensor) > 0){
//                $imei = $sensor[0]->imei;
//            }
//
//            //creating account
//            $args = [
//                'imei' => $imei,
//                'email' => "test" . Auth::user()->id . "@jogo.com",
//                'name' => "Jogo",
//                'fullname' => "Jogo"
//            ];
//            HumanOx::createAccount($args);
//
//            Auth::user()->humanox_username = Auth::user()->phone;
//            Auth::user()->humanox_pin = 'abc@123';
//            Auth::user()->save();
//
//            //authenticating
//            $args = [
//                'login' => 'demo@humanox.com',
//                'pin' => 's0ccer'
//            ];
//
//            $response = HumanOx::authenticate($args);
//            Auth::user()->humanox_auth_token = $response->token;
//            Auth::user()->save();
//        }
//
//        UserSensor::updateOrCreate(
//            ['user_id' => Auth::user()->id, 'imei' => $request->imei],
//            ['user_id' => Auth::user()->id, 'imei' => $request->imei]
//        );
//
//        //mounting imei into humanox
//        $args = [];
//        HumanOx::mountSensor();
//
//        return Helper::apiSuccessResponse(true, 'Sensor Mounted', new stdClass());
//
//    }
//
//    /**
//     * Disconnect Sensor
//     *
//     * @response {
//     * "Response": true,
//     * "StatusCode": 200,
//     * "Message": "Sensor disconnected",
//     * "Result": {}
//     * }
//     *
//     * @bodyParam imei string required
//     *
//     * @return JsonResponse
//     */
//    public function disconnectSensor(Request $request)
//    {
//        Validator::make($request->all(), ['imei' => 'required'])->validate();
//
//        $sensor = UserSensor::where('user_id', Auth::user()->id)->where('imei', $request->imei)->first();
//
//        if(!$sensor){
//            return Helper::apiNotFoundResponse(false, 'Record not found', new stdClass());
//        }
//
//        $sensor->delete();
//
//        return Helper::apiSuccessResponse(true, 'Sensor Disconnected', new stdClass());
//
//    }
//
//
//    /**
//     * Start Training Session
//     *
//     * @response {
//     * "Response": true,
//     * "StatusCode": 200,
//     * "Message": "Session started",
//     * "Result": {
//     * "match_id": 1036
//     * }
//     * }
//     *
//     *
//     * @return JsonResponse
//     */
//    public function startTrainingSession()
//    {
//        $sensors = Auth::user()->user_sensors;
//
//        if(count($sensors) == 0){
//            return Helper::apiNotFoundResponse(false, 'Imei not found', new stdClass());
//        }
//
//        //getting auth token
//        $auth = HumanOx::partnerLogin();
//
//        if (gettype($auth) == 'integer') {
//            return Helper::apiNotFoundResponse(false, 'Failed to get auth token', new stdClass());
//        }
//
//        $token = $auth->token;
////        $match_id = HumanOx::quickStart($sensors[0]->imei);
////        if($match_id == 0 || $match_id == null){
////            return Helper::apiNotFoundResponse(false, 'Please turn on your shinguard', new stdClass());
////        }
//
//        // Get Match
//        $res = HumanOx::getMatch($sensors[0]->imei, $token);
//        if (!isset($res[0]) || !isset($res[0]->match_id)) {
//            return Helper::apiNotFoundResponse(false, 'Please turn on your shinguard', new stdClass());
//        } else {
//            $c_match = count($res);
//            $c_match--;
//        }
//
//        //will implement match_id from get match response
//        $match = Match::find($res[$c_match]->match_id);
//        if(!$match) {
//            $match = Match::create([
//                'id' => $res[$c_match]->match_id,
//                'init_ts' => now(),
//                'user_id' => Auth::user()->id
//            ]);
//        }
//
//        if (!$match) {
//            return Helper::apiErrorResponse(false, 'Failed to start session', new stdClass());
//        }
//
//        $obj = new stdClass();
//        $obj->match_id = $match->id ?? 0;
//
//        return Helper::apiSuccessResponse(true, 'Session started', $obj);
//    }
//
//    /**
//     * End Training Session
//     *
//     * @response {
//     * "Response": true,
//     * "StatusCode": 200,
//     * "Message": "Session ended",
//     * "Result": {}
//     * }
//     *
//     * @bodyParam match_id string required you will receive match_id in start session response
//     * @bodyParam session_time string required session_time in timestamps eg: 2020-10-10 01:00:00
//     *
//     * @return JsonResponse
//     */
//    public function endTrainingSession(Request $request)
//    {
//        Validator::make($request->all(), [
//            'session_time' => 'required',
//            'match_id' => 'required'
//        ])->validate();
//
//        $match = Match::where('id', $request->match_id)->first();
//        if(!$match){
//            return Helper::apiNotFoundResponse(false, 'Match Id not found', new stdClass());
//        }
//
//        $sensors = Auth::user()->user_sensors;
//
//        if(count($sensors) == 0){
//            return Helper::apiNotFoundResponse(false, 'Imei not found', new stdClass());
//        }
//
//        //getting auth token
//        $auth = HumanOx::partnerLogin();
//
//        if (gettype($auth) == 'integer') {
//            return Helper::apiNotFoundResponse(false, 'Failed to get auth token', new stdClass());
//        }
//
//        $token = $auth->token;
//
//        $res = HumanOx::getMatchStats($match->id, $sensors[0]->imei, $token);
//
////        $match_id = HumanOx::quickStop($sensors[0]->imei);
//
////        if($match_id == 0 || $match_id == null){
////            return Helper::apiNotFoundResponse(false, 'Please turn on your shinguard', new stdClass());
////        }
//
////        foreach ($h_stats as $key => $value) {
////            MatchStat::create([
////                'match_id' => $value->match_id,
////                'stat_type_id' => $value->stat_type_id,
////                'stat_value' => $value->stat_value,
////                'player_id' => Auth::user()->id
////            ]);
////        }
//
////        Match::where('id', $request->match_id)->update([
////            'end_ts' => now(),
////            'total_ts' => $request->session_time
////        ]);
//
//        Match::where('user_id', Auth::user()->id)->whereNull('end_ts')->update([
//            'end_ts' => now(),
//            'total_ts' => $request->session_time
//        ]);
//        $stats_data = [];
//        if ($res != null && count($res) > 0) {
//            foreach ($res as $key => $stat) {
//
////                    $stat->user_id = $match->user_id;
////                    $stat->imei = $match->imei;
////                    $stats[] = $stat;
//
//                $stats_data[0]['match_id'] = $match->id;
//                $stats_data[0]['stat_type_id'] = 1;
//                $stats_data[0]['stat_value'] = $stat->distance;
//                $stats_data[0]['player_id'] = $match->user_id;
//                $stats_data[0]['imei'] = $sensors[0]->imei;
//
//                $stats_data[1]['match_id'] = $match->id;
//                $stats_data[1]['stat_type_id'] = 15;
//                $stats_data[1]['stat_value'] = $stat->steps;
//                $stats_data[1]['player_id'] = $match->user_id;
//                $stats_data[1]['imei'] = $sensors[0]->imei;
//
//                $stats_data[2]['match_id'] = $match->id;
//                $stats_data[2]['stat_type_id'] = 4;
//                $stats_data[2]['stat_value'] = $stat->walking;
//                $stats_data[2]['player_id'] = $match->user_id;
//                $stats_data[2]['imei'] = $sensors[0]->imei;
//
//                $stats_data[3]['match_id'] = $match->id;
//                $stats_data[3]['stat_type_id'] = 17;
//                $stats_data[3]['stat_value'] = $stat->running;
//                $stats_data[3]['player_id'] = $match->user_id;
//                $stats_data[3]['imei'] = $sensors[0]->imei;
//
//                $stats_data[4]['match_id'] = $match->id;
//                $stats_data[4]['stat_type_id'] = 6;
//                $stats_data[4]['stat_value'] = $stat->sprinting;
//                $stats_data[4]['player_id'] = $match->user_id;
//                $stats_data[4]['imei'] = $sensors[0]->imei;
//
//                $stats_data[5]['match_id'] = $match->id;
//                $stats_data[5]['stat_type_id'] = 7;
//                $stats_data[5]['stat_value'] = $stat->maxspeed;
//                $stats_data[5]['player_id'] = $match->user_id;
//                $stats_data[5]['imei'] = $sensors[0]->imei;
//
//                $stats_data[6]['match_id'] = $match->id;
//                $stats_data[6]['stat_type_id'] = 2;
//                $stats_data[6]['stat_value'] = $stat->avgspeed;
//                $stats_data[6]['player_id'] = $match->user_id;
//                $stats_data[6]['imei'] = $sensors[0]->imei;
//
//                $stats_data[7]['match_id'] = $match->id;
//                $stats_data[7]['stat_type_id'] = 11;
//                $stats_data[7]['stat_value'] = $stat->max_hr;
//                $stats_data[7]['player_id'] = $match->user_id;
//                $stats_data[7]['imei'] = $sensors[0]->imei;
//
//                $stats_data[8]['match_id'] = $match->id;
//                $stats_data[8]['stat_type_id'] = 3;
//                $stats_data[8]['stat_value'] = $stat->avg_hr;
//                $stats_data[8]['player_id'] = $match->user_id;
//                $stats_data[8]['imei'] = $sensors[0]->imei;
//
//                $stats_data[9]['match_id'] = $match->id;
//                $stats_data[9]['stat_type_id'] = 14;
//                $stats_data[9]['stat_value'] = $stat->impacts;
//                $stats_data[9]['player_id'] = $match->user_id;
//                $stats_data[9]['imei'] = $sensors[0]->imei;
//
//                $stats_data[10]['match_id'] = $match->id;
//                $stats_data[10]['stat_type_id'] = 14;
//                $stats_data[10]['stat_value'] = $stat->impacts;
//                $stats_data[10]['player_id'] = $match->user_id;
//                $stats_data[10]['imei'] = $sensors[0]->imei;
//
//            }
//        }
//
//        MatchStat::insert($stats_data);
//
//        return Helper::apiSuccessResponse(true, 'Session ended', new stdClass());
//    }
//
//    /**
//     * Update Graph data
//     *
//     * @response {
//     * "Response": true,
//     * "StatusCode": 200,
//     * "Message": "Graph updated",
//     * "Result": {}
//     * }
//     *
//     * @bodyParam user_ids array required ids should be in array
//     */
//    public function updateGraph(Request $request)
//    {
////        $start = Carbon::parse('2020-12-03 18:45:00');
////        $end = Carbon::now();
////        return $duration = $end->diffInSeconds($start);
//
//        //getting auth token
//        $auth = HumanOx::partnerLogin();
//
//        if (gettype($auth) == 'integer') {
//            return Helper::apiNotFoundResponse(false, 'Failed to get auth token', new stdClass());
//        }
//
//        $token = $auth->token;
//
//        $matches = Match::where('end_ts', NULL)->get();
//        foreach ($matches as $match) {
//            $user = \App\User::where('id', $match->user_id)->first();
//            $sensors = $user->user_sensors;
//            if(count($sensors) > 0) {
//                $match_data = HumanOx::getMatchData($match->id, $sensors[0]->imei, $token);
//
//                if (gettype($match_data) == 'integer') continue;
//                else if ($match_data == []) continue;
//                else if ($match_data == null) continue;
//
//                foreach ($match_data as $md) {
//                    MatchDetails::create([
//                        'event_id' => $match->id,
//                        'user_id' => $match->user_id,
//                        'event_ts' => $md->event_ts,
//                        'geo_lon' => $md->geo_lon,
//                        'geo_lat' => $md->geo_lat,
//                        'event_type' => $md->event_type,
//                        'event_magnitude' => $md->event_magnitude,
//                        'speed' => $md->speed,
//                        'hr' => $md->hr,
//                        // 'period' => $md->period ,
//                        'steps' => $md->steps,
//                        //'temperature' => $md->temperature
//                    ]);
//                }
//            }
//
//        }
//
//        return Helper::apiSuccessResponse(true, 'graph updated', new stdClass());
//    }
//
//
//
//    /**
//     * Submit Training Session
//     *
//     * @response {
//     * "Response": true,
//     * "StatusCode": 200,
//     * "Message": "session submitted",
//     * "Result": {}
//     * }
//     *
//     * @bodyParam match_id string required
//     * @bodyParam match_type string  required  match/training
//     * @bodyParam player_image file  optional image
//     */
//    public function submitTrainingSession(Request $request){
//        $this->validate($request,[
//            'match_id'=>'required',
//            'match_type'=>'required|in:match,training',
//            'player_image'=>'required'
//        ]);
//        $match = Match::find($request->match_id);
//        if(!$match){
//            return Helper::apiErrorResponse(false, 'invalid match_id', new stdClass());
//        }
//        if($match->user_id!=auth()->user()->id){
//            return Helper::apiErrorResponse(false, 'you are not authorized', new stdClass());
//        }
//        if($request->player_image!=null){
//            $match->player_image = Helper::uploadBase64File($request->player_image,'media/matches');
////            $match->player_image = Storage::putFile('media/matches', $request->player_image);
//        }
//        $match->match_type = $request->match_type;
//        $match->save();
//        return Helper::apiSuccessResponse(true, 'session submitted', new stdClass());
//    }
//}
