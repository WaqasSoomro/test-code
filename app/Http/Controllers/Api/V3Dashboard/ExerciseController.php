<?php

namespace App\Http\Controllers\Api\V3Dashboard;

use App\Club;
use App\Exercise;
use App\Helpers\Helper;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ParagonIE\Sodium\Core\Curve25519\H;
use Psy\Util\Json;

/**
 * @group Dashboard V3 / Settings
 * APIs for V3 dashboard settings
 */
class ExerciseController extends Controller
{
//    //
//    /**
//     * Create / Update An Exercise
//     *
//     * @response
//     * {
//
//     * }
//     */
//    public function create_or_update_exercise(Request $request){
//        $request->validate([
//            "title"=>"required|string|max:191",
//            "category_id"=>"required|integer",
//            "skills"=>"required|array",
//            "tools"=>"required|array",
//            "privacy"=>"required|in:my_team, my_club",
//            "levels"=>"required|array",
//            "id"=>"integer", // OPTIONAL
//            "camera_mode"=>"string|in:portrait, landscape" // OPTIONAL
//        ]);
//
//        $request->levels = json_encode($request->levels);
//        $request->levels = (array) json_decode($request->levels);
//
//        $exercise = Exercise::find($request->id);
//
//        if (!$exercise)
//        {
//            $exercise = new Exercise();
//        }
//        try{
//            DB::transaction(function ()use($request, $exercise){
//                if (Storage::exists($exercise->image) && $request->hasFile('image')) {
//                    Storage::delete($exercise->image);
//                }
//                if (Storage::exists($exercise->video) && $request->hasFile('video')) {
//                    Storage::delete($exercise->video);
//                }
//
//                if ($request->hasFile('video')) {
//                    $file             = $request->file('video');
//                    // set storage path to store the file (actual video)
//                    $destination_path = public_path().'/uploads';
//                    if(!File::exists($destination_path)){
//                        File::makeDirectory($destination_path);
//                    }
//                    // get file extension
//                    $extension        = $file->getClientOriginalExtension();
//                    $file_name        = date('Ymdhia').".".$extension;
//                    $upload_status    = $file->move($destination_path, $file_name);
//
//                    if($upload_status){
//                        $thumbnail_path   = public_path().'/uploads';
//                        $video_path       = $destination_path.'/'.$file_name;
//
//                        // set thumbnail image name
//                        $thumbnail_image  = date('Ymdhia').".jpg";
//
//                        $thumbnail = new \Lakshmaji\Thumbnail\Thumbnail();
//                        $thumbnail_status = $thumbnail->getThumbnail($video_path,$thumbnail_path,$thumbnail_image,3);
//                        if($thumbnail_status){
//                            $exercise->image = Storage::putFile("media/exercises/images", $destination_path."/".$thumbnail_image);
//                        }
//                    }
//                    $exercise->video = Storage::putFile("media/exercises/videos", $video_path);
//                    $exercise->video_name = $request->video_name;
//
//                    File::delete($destination_path."/".$thumbnail_image);
//                    File::delete($video_path);
//                }
//
//                $exercise->title = json_encode(['en' => $request->title, 'nl' => $request->title]);
//                $exercise->description = json_encode(['en' => $request->description, 'nl' => $request->description]);
//                $exercise->leaderboard_direction = $request->leaderboard_direction ?? 'asc';
//                $exercise->badge = $request->badge ?? 'non_ai';
//                $exercise->privacy = $request->privacy ?? "my_team";
//                $exercise->android_exercise_type = $request->android_exercise_type;
//                $exercise->ios_exercise_type = $request->ios_exercise_type;
//                $exercise->score = $request->score ?? 0;
//                $exercise->count_down_milliseconds = $request->count_down_milliseconds ?? 3000;
//                $exercise->use_questions = $request->use_questions ?? 0;
//                $exercise->selected_camera_facing = $request->selected_camera_facing ?? 'FRONT';
//                $exercise->camera_mode = $request->camera_mode ?? null;
//                $exercise->is_active = $request->is_active ?? 1;
//                $exercise->save();
//
//                if($request->category_id){
//                    $exercise->categories()->sync($request->category_id);
//                }
//                if($request->skills && is_array($request->skills)){
//                    $exercise->skills()->sync($request->skills);
//                }
//                if($request->tools && is_array($request->tools)){
//                    $exercise->tools()->sync($request->tools);
//                }
//                if($request->levels && is_array($request->levels)){
//                    $exercise->levels()->detach();
//                    foreach($request->levels as $level) {
//                        $exercise->levels()->attach($level->level_id, ['measure' => $level->measure]);
//                    }
//                }
//                if ($request->privacy == 'my_team'){
//                    $user = \App\User::with("teams_trainers")->find(auth()->user()->id);
//                    $team_ids = $user->teams_trainers->pluck("id");
//                    $exercise->teams()->sync($team_ids);
//                }
//            });
//            return Helper::apiSuccessResponse(true, 'Success',$exercise);
//        }catch(\Exception $e){
////            echo $e->getMessage();
//            return Helper::apiErrorResponse(false, 'Failed to save Exercise',$e);
//        }
//    }

    /**
     * Delete Exercise
     *
     * @response
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "SuccessFully Deleted Exercise",
    "Result": {}
    }
     *
     * @bodyParam exercise_id integer required
     * @bodyParam club_id integer required
     */
    public function delete_exercise(Request $request){
        $request->validate([
            "exercise_id"=>"required|integer",
            "club_id"=>"required|integer",
        ]);

        // CHECK IF THE TRAINER IS IN THE CLUB OR NOT.
        $myClubs = (new Club())->myCLubs($request)->original['Result'];
        
        if (!in_array($request->club_id, array_column($myClubs, 'id')))
        {
            return Helper::apiErrorResponse(false, 'Add Club First', new \stdClass());
        }

        $exercises = Exercise::select('id', 'title', 'android_exercise_type', 'ios_exercise_type', 'updated_at' ,'is_active')
        ->with('levels', 'skills', 'teams', 'tools')
        ->whereHas('teams.clubs', function($query) use($request)
        {
            $query->where('club_id', $request->club_id);
        })
        ->where('id', $request->exercise_id)
        ->first();

        if (!$exercises)
        {
            return Helper::apiNotFoundResponse(false,"Exercise Not Found",new \stdClass());
        }

        try{
            DB::transaction(function () use($exercises){
                $exercises->skills()->detach();
                $exercises->categories()->detach();
                $exercises->tools()->detach();
                $exercises->teams()->detach();
                $exercises->levels()->detach();
                $exercises->delete();
            });
        }catch (\Exception $e){
            return Helper::apiErrorResponse(false,"Something Went Wrong", new \stdClass());
        }
        return Helper::apiSuccessResponse(true,"SuccessFully Deleted Exercise",new \stdClass());
    }
}
