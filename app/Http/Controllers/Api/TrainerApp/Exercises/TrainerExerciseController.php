<?php

namespace App\Http\Controllers\Api\TrainerApp\Exercises;


use Illuminate\Http\Request;
use App\Assignment;
use App\AssignmentExercise;
use App\Category;
use App\Exercise;
use App\ExerciseAiData;
use App\Helpers\Helper;
use App\Helpers\HumanOx;
use App\Http\Controllers\Controller;
use App\Imports\AiImport;
use App\Imports\DashboardPlayersImport;
use App\Level;
use App\Match;
use App\MatchDetails;
use App\MatchStat;
use App\MatchStatType;
use App\PlayerAssignment;
use App\PlayerExercise;
use App\PlayerScore;
use App\Post;
use App\Skill;
use App\Status;
use App\Tool;
use App\User;
use App\Player;
use App\UserSensor;
use App\TrainerSession;
use App\PlayerSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel;
use stdClass;

/**
 * @group TrainerApp / Exercises
 */

class TrainerExerciseController extends Controller
{
    /**
     * Session Create
     * 
     * @response {
     * "Response" : true,
     * "StatusCode": 200,
     * "Message": "Session Created",
     * "Result" : {
     * "session_id": "1"
     * }
     * }
     * @bodyParam player_ids array required 
     * @bodyParam exercise_id integer required
     * @bodyParam level_id integer required 
     */
    
    public function sessionCreate(Request $request)
    { 
        Validator::make($request->all(), [
            'player_ids.*' => 'required|exists:players,user_id',
            'exercise_id' => 'required',
            'level_id' => 'required',
        ])->validate();
      //  $player_ids=[];
        $player_ids= $request->player_ids;
        $exercise_id=$request->exercise_id;
        $level_id=$request->level_id;
        $trainer_id =Auth::user()->id;
        

        if(!is_array($player_ids) )
        {
            return Helper::apiNotFoundResponse(false, 'Player_ids is not an array', new stdClass());
        }
        

        $trainer_session = new TrainerSession();

                $trainer_session->trainer_id=$trainer_id;
                $trainer_session->exercise_id= $exercise_id;
                $trainer_session->level_id= $level_id;
                $trainer_session->save();       

        $session_id=TrainerSession::where('trainer_id', $trainer_id)->orderBy('created_at','desc')->value('id');

        foreach($request->player_ids as $player_id )
        {   
            $player_session = new PlayerSession();
            $player_session->session_id=$session_id;
            $player_session->player_id= $player_id;
            $player_session->save();     
        }
        if(!$player_session->save())
        {
            return Helper::apiNotFoundResponse(false, 'Session Creation Unsuccessfully', new stdClass());
        }

        $data["session_id"]=$session_id;
        return Helper::apiSuccessResponse(true, 'Session Created Successfully', $data);

    }
    
    /**
    * Session Start 
    * 
    * @response {
    *   "Response": true,
    *   "StatusCode": 200,
    *   "Message": "Session started",
    *   "Result": {
    *        "player_id": 4,
    *        "exercise_id": 2,
    *        "level_id": 1
    *   }
    * }
    * @bodyParam player_id integer it will be sent if exercise is to be repeated
    * @bodyParam session_id integer required 
    * @bodyParam is_repeat string required takes 'yes' or 'no' 
    */
    

    public function sessionStart(Request $request)
    { 
        Validator::make($request->all(), [
            'player_id' => 'nullable',
            'session_id' => 'required',
            'is_repeat' => 'required|in:yes,no',
        ])->validate();

            if($request->player_id && $request->is_repeat == 'yes')
            {   
                $player_id=intval($request->player_id);
                $session_id=intval($request->session_id);
                $exercise_id= TrainerSession::where('id',$session_id)->value('exercise_id');
                $level_id= TrainerSession::where('id',$session_id)->orderBy('created_at','desc')->value('level_id');
            }
            else
            {
                $player_id= PlayerSession::where('status', 'pending')->where('session_id',$request->session_id)->orderBy('created_at','desc')->value('player_id');
                $session_id= $request->session_id;
                $exercise_id= TrainerSession::where('id',$session_id)->value('exercise_id');
                $level_id= TrainerSession::where('id',$session_id)->orderBy('created_at','desc')->value('level_id');      
            }

            //$data=[$player_id, $exercise_id,$level_id]; 
            $data=array("player_id"=>$player_id,"exercise_id"=>$exercise_id,"level_id"=>$level_id);

            
            return Helper::apiSuccessResponse(true, 'Session started', $data);
    }





    /**
     * End Exercise
     *
     * @response {
     *  "Response": true,
     *  "StatusCode": 200,
     *  "Message": "Exercise completed",
     *   "Result": {
     *      "id": 342,
     *      "user_id": 3,
     *      "assignment_id": null,
     *      "exercise_id": 66,
     *      "level_id": 1,
     *      "thumbnail": "media/player_exercises/QhboqdtReygJPGMkVKtXyqzgBmj0UXwqDBh5sMGP.jpeg",
     *      "video_file": "media/player_exercises/XbhSxMz4pqJFyPpMmUw5tjKvTgxudFSH9E7hqyFJ.mp4",
     *      "explicit_response": null,
     *      "completion_time": 33,
     *      "trainer_rating": 0,
     *      "start_time": "2020-12-09 14:21:26",
     *      "end_time": "2021-05-25 08:17:52",
     *      "status_id": 3,
     *      "created_at": "2020-12-09 14:21:26",
     *      "updated_at": "2021-05-25 08:17:52",
     *      "deleted_at": null,
     *      "Player_details": [
     *           {
     *           "first_name": "Hasnain",
     *           "last_name": "Ali"
     *          }
     *      ],
     *      "remaining_player": 1,
     *      "next_player_details": [
     *       {
     *           "first_name": "Tariq",
     *           "last_name": "Sidd"
     *       }
     *      ],
     *      "scores": {
     *          "score": 50
     *      }
     *  }
     *  }
     *
     * 
     * 
     * @bodyParam scores string required scores is a array of objects containing skill_id,score eg: [{skill_id:1,score:20},{skill_id:2,score:23}]
     * @bodyParam completion_time double required
     * @bodyParam video_file file required
     * @bodyParam thumbnail file required
     * @bodyParam player_id integer required
     * @bodyParam session_id integer required
     * @return JsonResponse
     */
    
    public function endExercise(Request $request)
    {
        Validator::make($request->all(), [    
            // 'match_id' => 'required',    
            'completion_time' => 'required',    
            'scores.*.skill_id' => 'required|exists:skills,id', 
            'scores.*.score' => 'required', 
            'thumbnail' => 'required',  
            'video_file' => 'required',
            'player_id' => 'required',
            'session_id' => 'required',
        ])->validate(); 
        $player_id=$request->player_id;
        $session_id=$request->session_id;

        $player_exercise = new PlayerExercise();
        $player_exercise->user_id = $player_id;
        $player_exercise->exercise_id = TrainerSession::where('id',$session_id)->value('exercise_id');
        $player_exercise->thumbnail = $request->thumbnail;
        $player_exercise->video_file = $request->video_file;
        $player_exercise->completion_time = $request->completion_time;
        $player_exercise->save();
        
        $status = Status::where('name', 'completed')->first();
        $player_exercise_id= PlayerExercise::where('user_id', $player_id)->orderBy('created_at','desc')->value('id');
        $pl_ex = PlayerExercise::where('id', $player_exercise_id)  
            ->where('user_id', $player_id)  
            ->first();  
        if (!$pl_ex) {  
            return Helper::apiNotFoundResponse(false, 'Record not found', new stdClass());  
        }   
        // //getting auth token 
        // $auth = HumanOx::partnerLogin(); 
        // if (gettype($auth) == 'integer') {   
        //     return Helper::apiNotFoundResponse(false, 'Failed to get auth token', new stdClass());   
        // }    
        // $token = $auth->token;   
        // $sensor = UserSensor::where('user_id', Auth::user()->id)->first();   
        // $match_id=null;  
        // if($sensor){ 
        //     $match = HumanOx::getMatch($sensor->imei, $token);   
        //     if($match){  
        //          if(count($match) > 0) { 
        //             $match_id = $match[0]->match_id; 
        //         }    
        //     }    
        // }    

        $res = DB::transaction(function () use ($request, $status,$player_id,$player_exercise_id) {
            $pl_ex = (new PlayerExercise())->updatePlayerExercise($request,$player_id);

            foreach ($request->scores as $value)
            {  
                (new PlayerScore())->createPlayerScore($pl_ex,Auth::user()->id,$value);
            }

            (new Post())->createPost($pl_ex,Auth::user()->id);

            return Helper::completeExercise($pl_ex);
        }); 

        $res = $this->saveVideoFile($request,$pl_ex);
        $res=$pl_ex;
        if (!$res) {    
            return Helper::apiNotFoundResponse(false, 'Failed to save data in post or player exercise', new stdClass());    
        }   
        // if (!empty($video_file)) 
        // {    
        //     $assignment = Assignment::with('author') 
        //     ->where('id', $pl_ex->assignment_id) 
        //     ->first();   
        //     if ($assignment) 
        //     {    
        //         $data['from_user_id'] = Auth::user()->id;    
        //         $data['to_user_id'] = $assignment->trainer_user_id;  
        //         $data['model_type'] = 'exercises/finished';  
        //         $data['model_type_id'] = $assignment->id;    
        //         $data['click_action'] = 'ViewExercises'; 
        //         $data['message']['en'] = auth()->user()->first_name.' '.auth()->user()->last_name.' has finished the exercise '.$assignment->title;  
        //         $data['message']['nl'] = auth()->user()->first_name.' '.auth()->user()->last_name.' har avslutat övningen '.$assignment->title;  
        //        $data['message'] = json_encode($data['message']); 
        //         $data['badge_count'] = ($assignment->author->badge_count ?? "") + 1; 
        //         $devices = $assignment->author->user_devices;    
        //     }    
        //     else 
        //     {    
        //         $data['from_user_id'] = auth()->user()->id;  
        //         $data['to_user_id'] = null;  
        //         $data['model_type'] = 'exercises/finished';  
        //         $data['model_type_id'] = null;   
        //         $data['click_action'] = 'ViewExercises'; 
        //         $data['message']['en'] = auth()->user()->first_name.' '.auth()->user()->last_name.' has finished the exercise '; 
        //         $data['message']['nl'] = auth()->user()->first_name.' '.auth()->user()->last_name.' har avslutat övningen '; 
        //         $data['message'] = json_encode($data['message']);    
        //         $data['badge_count'] = 0;    
        //         $devices = [];   
        //     }    
        //     $tokens = [];    
        //     foreach ($devices as $key => $value) 
        //     {    
        //         if ($value->device_token)    
        //         {    
        //             array_push($tokens, $value->device_token);   
        //         }    
        //     }    
        //     if (count($tokens) > 0)  
        //     {    
        //        Helper::sendNotification($data, $tokens); 
        //         User::where('id', $data['to_user_id'])   
        //         ->update([   
        //             'badge_count' => $data['badge_count']    
        //         ]);  
        //     }    
        // }
        $current_user=Player::where('id',$player_id)->value('user_id');
        $user_details=new stdClass();
        $user_details=User::where('id',$current_user)->select('first_name','last_name')->first();   
        PlayerSession::where('player_id', $player_id)->where('session_id',$session_id)->update(array('status' => 'done'));
        $remaining_players= PlayerSession::where('status','pending')->where('session_id',$session_id)->count();
        $res->player_details = $user_details;
        //$res["Player_details"]=$user_details;

        $res["remaining_player"]=$remaining_players;

        
       
        $query=PlayerSession::where('status','pending')->where('session_id',$session_id)->first();
        if(!$query)
        {

            $next_user_details=new stdClass();
            $res->next_player_details = $next_user_details;
           // $res["next_player_details"]=$next_user_details;
            return Helper::apiSuccessResponse(true, 'Session Done', $res);

        }
        $next_player=$query->player_id;
        $next_user=Player::where('id',$next_player)->value('user_id');
        $next_user_details=User::where('id',$next_user)->select('first_name','last_name')->first();
        if($remaining_players >0)
        {
            //$res["next_player_details"] = $next_user_details;
            $res->next_player_details = $next_user_details;
        }
        else
        {
            $res->next_player_details = new stdClass();
        }
        


        if(!$query)
        {
            TrainerSession::where('id', $session_id)->update(array('status' => 'end'));
        }

        return Helper::apiSuccessResponse(true, 'Exercise completed', $res);
    }
}