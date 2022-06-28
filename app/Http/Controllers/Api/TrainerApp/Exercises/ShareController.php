<?php

namespace App\Http\Controllers\Api\TrainerApp\Exercises;

use App\Assignment;
use App\AssignmentExercise;
use App\Exercise;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\PlayerAssignment;
use App\Status;
use App\User;
use Illuminate\Http\Request;
/**
 * @group TrainerApp / Skill Assignment
 *
 */
class ShareController extends Controller
{

    /*
     *
     *
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "success",
     * "Result": {
     * "assignment": {
     * "trainer_user_id": 5,
     * "title": "Box Drill - Tennis Ball",
     * "deadline": "2021-05-23",
     * "description": "Make a large square where the cones are about 3 meters apart. Go around the cones while staying on top of the ball using the soles of your feet. Try keeping your head up as much as possible. Go around the cones clockwise and counter clockwise to do the drill using both feet.",
     * "image": "NULL",
     * "updated_at": "2021-05-21 10:32:37",
     * "created_at": "2021-05-21 10:32:37",
     * "id": 166
     * },
     * "assignment_exercise": {
     * "assignment_id": null,
     * "exercise_id": 48,
     * "sort_order": 1,
     * "level_id": 1,
     * "updated_at": "2021-05-21 10:32:37",
     * "created_at": "2021-05-21 10:32:37",
     * "id": 604
     * }
     * }
     * }
     *
     *
     *
     *
     */


    /**
     *  Share To Player
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Successfully shared assignment to selected players",
     * "Result": []
     * }
     *
     * @bodyParam exercise_id integer required
     * @bodyParam players_id array required integer
     */
    public function shareToPlayer(Request $request){

        $request->validate([
            "exercise_id"=>"required|integer",
            "players_id"=>"required|array",
        ]);
        $exercise = Exercise::with("levels")->find($request->exercise_id); // GETTING CURRENT SELECTED EXERCISE

        // CHECK IF THE EXERCISE WITH THE GIVEN ID IS AVAILABLE.
        if (!$exercise){
            return Helper::apiNotFoundResponse(false, "Exercise doesn't exist", []);
        }

        $assignment = new Assignment();
        $assignment->trainer_user_id = auth()->user()->id;
        $assignment->title = $exercise->title;
        $assignment->deadline = date('Y-m-d', strtotime('+2 days'.now()));
        $assignment->description = $exercise->description;
        $assignment->image = $exercise->image;

        if(!$assignment->save()){
            return Helper::apiErrorResponse(false,"Couldn't create assignment",new \stdClass());
        }

        $assignment_exercise = new AssignmentExercise();
        $assignment_exercise->assignment_id = $assignment->id;
        $assignment_exercise->exercise_id = $exercise->id;
        $assignment_exercise->sort_order = 1;
        $assignment_exercise->level_id = $exercise->levels[0]->id;

        $assignment_exercise->save();

        return $this->share($assignment->id,$request->players_id);

    }

    public function share($assignment_id,$players_id){

        $trainer_user = User::find(auth()->user()->id);// Trainer User

        $status = Status::where('name', 'pending')->first();

        if (count($players_id) > 0){
           for ($i = 0;$i < count($players_id);$i++){
               $player_exercise = new PlayerAssignment();
               $player_exercise->assignment_id = $assignment_id;
               $player_exercise->player_user_id = $players_id[$i];
               $player_exercise->status_id = $status->id;
               if ($player_exercise->save()){
                   // SEND NOTIFICATION
                   $player_user = User::with("user_devices")->find($players_id[$i]);
                   $data = [];
                   $data['from_user_id'] = auth()->user()->id;
                   $data['to_user_id'] = $players_id[$i];
                   $data['model_type'] = 'assignment/assigned';
                   $data['model_type_id'] = $player_exercise->id;
                   $data['click_action'] = 'AssignmentsDetail';
                   $data['message']['en'] = 'You have a new assignment by ' . $trainer_user->first_name . " ". $trainer_user->last_name;
                   $data['message']['nl'] = 'Je hebt een nieuwe opdracht van ' . $trainer_user->first_name . " ". $trainer_user->last_name;
                   $data['message'] = json_encode($data['message']);
                   $data['badge_count'] = $player_user->badge_count + 1;
                   Helper::sendNotification($data, $player_user->user_devices[0]->onesignal_token,$player_user->user_devices[0]->device_type);
                   $player_user->increment("badge_count",1);
               }
               else{
                   return Helper::apiErrorResponse(false,"Could not save player exercise",new \stdClass());
               }
           }
        }
        return Helper::apiSuccessResponse(true,"Successfully shared assignment to selected players",[]);
    }
}
