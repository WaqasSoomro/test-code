<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\AccessModifier;
use App\Category;
use App\Comment;
use App\Exercise;
use App\ExercisePrivacy;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Level;
use App\Player;
use App\PlayerExercise;
use App\PlayerScore;
use App\Post;
use App\PricingPlan;
use App\Skill;
use App\Team;
use App\Tool;
use App\User;
use App\Status;
use App\UserPrivacySetting;
use App\Club;
use Carbon\Carbon;
use Carbon\Traits\Date;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use stdClass;
use Illuminate\Support\Facades\File;

/**
 * @authenticated
 * @group Dashboard / Exercise
 *
 * APIs to manage Exercises
 */
class ExerciseController extends Controller
{

    /**
     * GetPlayerAllExercise
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Successfully retrieved player exercises!",
     * "Result": [
     *       {
     *          "id": 1,
     *          "user_id": 2,
     *          "exercise_id": 1,
     *          "level_id": 1,
     *          "status_id": 3,
     *          "completion_time": 50,
     *          "video_file": null,
     *          "start_time": "2020-07-20 00:55:12",
     *          "end_time": "2020-07-21 00:55:12",
     *          "trainer_rating": 4,
     *          "created_at": "2020-07-21 00:44:07",
     *          "updated_at": null,
     *          "deleted_at": null
     *       },
     *       {
     *          "id": 3,
     *          "user_id": 2,
     *          "exercise_id": 3,
     *          "level_id": 1,
     *          "status_id": 3,
     *          "completion_time": 40,
     *          "video_file": null,
     *          "start_time": "2020-07-20 00:55:48",
     *          "end_time": "2020-07-24 00:55:48",
     *          "trainer_rating": 7,
     *          "created_at": "2020-07-21 00:55:48",
     *          "updated_at": null,
     *          "deleted_at": null
     *       }
     *   ]
     *   }
     *
     *
     * @bodyParam id integer required user id for specific player exercises
     *
     *
     * @return JsonResponse
     */
    public function getPlayerAllExercise(Request $request)
    {

        //Check if Target User Id received or not
        if (isset($request->id)) {

            //Target User Id
            $target_user_id = $request->id;


            //User privacy settings
            $user_privacy_settings = UserPrivacySetting::whereUserId($target_user_id)->first();

            if(isset($user_privacy_settings)) {

                //Check user privacy
                $check_privacy = AccessModifier::whereId($user_privacy_settings->access_modifier_id)->first();

            } else {

                return Helper::apiNotFoundResponse(false, 'Records not found!', new stdClass());

            }

            //Check exercises status
            $get_status_id = Status::whereName('completed')->first();

            //Check if privacy is public
            if ($check_privacy->name === 'public') {

                //Get all completed exercises of this user
                $get_player_exercise = PlayerExercise::whereUserId($target_user_id)->whereStatusId($get_status_id->id)->get();

                if(!$get_player_exercise->isEmpty()) {

                    //Return success response
                    return Helper::apiSuccessResponse(true, "Successfully retrieved player exercises!", $get_player_exercise);

                } else {

                    return Helper::apiNotFoundResponse(false, 'Records not found!', new stdClass());

                }

              //Don't give data if privacy is private
            } elseif ($check_privacy->name === "private") {

                return Helper::response(false, 404, 'This player has a private profile!', new stdClass());

            }

        } else {

            //Target User Id not received
            return Helper::apiNotFoundResponse(false, 'Sorry we could not find a player!', new stdClass());

        }

    }

    /**
     * GetPlayerExercise
     *
     * @response {
     *     "Response": true,
     *     "StatusCode": 200,
     *     "Message": "Successfully retrieved player exercise!",
     *     "Result": {
                "player_exercise": {
                    "id": 1,
                    "user_id": 2,
                    "exercise_id": 1,
                    "level_id": 1,
                    "status_id": 3,
                    "completion_time": 50,
                    "video_file": null,
                    "start_time": "2020-07-20 00:55:12",
                    "end_time": "2020-07-21 00:55:12",
                    "trainer_rating": 4,
                    "created_at": "2020-07-21 00:44:07",
                    "updated_at": null,
                    "deleted_at": null,
                    "player_score": {
                        "id": 1,
                        "user_id": 2,
                        "skill_id": 1,
                        "exercise_id": 1,
                        "level_id": 1,
                        "score": 100,
                        "created_at": "2020-07-21 17:57:14",
                        "updated_at": null,
                        "deleted_at": null
                    }
                },
                "exercise_data": {
                    "id": 1,
                    "title": "Ball Juggling",
                    "description": "Ball Juggling",
                    "image": null,
                    "video": null,
                    "leaderboard_direction": "desc",
                    "created_at": "2020-07-21 00:45:34",
                    "updated_at": null,
                    "deleted_at": null
                },
                "post_comments": [
                    {
                        "id": 1,
                        "post_id": 1,
                        "contact_id": 1,
                        "comment": "Awesome!",
                        "status_id": 1,
                        "created_at": "2020-07-21 17:58:38",
                        "updated_at": null,
                        "deleted_at": null
                    },
                    {
                        "id": 2,
                        "post_id": 1,
                        "contact_id": 1,
                        "comment": "Great Work Player!",
                        "status_id": 1,
                        "created_at": "2020-07-21 23:46:49",
                        "updated_at": null,
                        "deleted_at": null
                    }
                ]
            }
     * @bodyParam id integer required user id for specific player exercises
     * @bodyParam level_id integer required level id used to fetch the exercise level
     * @bodyParam exercise_id integer required exercise id used to fetch the exercise
     *
     * @return JsonResponse
     */
    public function getPlayerExercise(Request $request)
    {

        //Target User Id
        $target_user_id = $request->id;
        //Level Id
        $level_id = $request->level_id;
        //Exercise Id
        $exercise_id = $request->exercise_id;

        //Check if Target User Id received or not
        if (isset($target_user_id) && isset($level_id) && isset($exercise_id)) {

            //User privacy settings
            $user_privacy_settings = UserPrivacySetting::whereUserId($target_user_id)->first();

            if(!empty($user_privacy_settings)) {

                //Check user privacy
                $check_privacy = AccessModifier::whereId($user_privacy_settings->access_modifier_id)->first();

                //Check exercises status
                $get_status_id = Status::whereName('completed')->first();

                //Check if privacy is public
                if ($check_privacy->name === 'public') {

                    //Get all details of exercise
                    $get_exercise_data = Exercise::whereId($exercise_id)->first();

                    if (isset($get_exercise_data)) {

                        //Get all details of player exercise
                        $get_player_exercise_data = PlayerExercise::whereUserId($target_user_id)
                            ->whereLevelId($level_id)
                            ->whereExerciseId($exercise_id)
                            ->whereStatusId($get_status_id->id)
                            ->first();

                    } else {

                        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

                    }


                    if(isset($get_player_exercise_data)) {

                        //Get player score for this exercise
                        $get_player_score = PlayerScore::whereUserId($target_user_id)
                            ->whereExerciseId($exercise_id)
                            ->whereLevelId($level_id)
                            ->first();

                        //Combine the player score in player exercise data
                        $get_player_exercise_data['player_score'] = $get_player_score;

                    } else {

                        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

                    }

                    //Get Particular post data
                    $get_post_data = Post::whereAuthorId($target_user_id)
                        ->whereLevelId($level_id)
                        ->whereExerciseId($exercise_id)
                        ->first();

                    //Check if posts exists for this exercise
                    if (isset($get_post_data)) {

                        $get_comment_data = Comment::wherePostId($get_post_data->id)->get();

                    } else {

                        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

                    }

                    if (isset($get_player_exercise_data) && isset($get_exercise_data) && isset($get_comment_data)) {

                        $all_data_combined = array();
                        $all_data_combined['player_exercise'] = $get_player_exercise_data;
                        $all_data_combined['exercise_data'] = $get_exercise_data;
                        $all_data_combined['post_comments'] = $get_comment_data;

                        //Return success response
                        return Helper::apiSuccessResponse(true, "Successfully retrieved player exercise data!", $all_data_combined);

                    } else {

                        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());

                    }


                //Don't give data if privacy is private
                } elseif ($check_privacy->name === "private") {

                    return Helper::response(false, 404, 'This player has a private profile!', new stdClass());

                }

            } else {

                return Helper::apiNotFoundResponse(false, 'This players privacy setting has not been setup', new stdClass());
            }

        } else {

            //Request Params not received
            return Helper::apiNotFoundResponse(false, 'Sorry we could not find a player!', new stdClass());

        }

    }


    /**
     * Add/Update Exercise
     *
     * @response
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": {
    "title": "My SECOND CUSTOM EXERCISE",
    "description": "exe",
    "leaderboard_direction": "asc",
    "badge": "non_ai",
    "privacy": "my_team",
    "android_exercise_type": "NSECONDS",
    "ios_exercise_type": "NSECONDS",
    "score": 0,
    "count_down_milliseconds": 3000,
    "use_questions": 0,
    "selected_camera_facing": "FRONT",
    "camera_mode": null,
    "is_active": 1,
    "updated_at": "2021-07-09 15:28:58",
    "created_at": "2021-07-09 15:28:58",
    "id": 303
    }
    }
     *
     * @bodyParam title string  max 191 chars required
     * @bodyParam tools[] integer required
     * @bodyParam teams[] integer required
     * @bodyParam privacy int required exercise_privacy_id
     * @bodyParam levels[{'level_id': 1,'metric_type':'COUNTDOWN','measure': 90},{'level_id': 2, 'metric_type':'COUNTDOWN' ,'measure': 70}] integer required
     * @bodyParam id integer optional (incase of edit)
     * @bodyParam camera_mode string optional options: portrait, landscape
     * @return JsonResponse
     */
    public function addExercise(Request $request)
    {
        $this->validate($request,[
            'title' => 'required',
            'description' => 'required',
//            'leaderboard_direction' => 'required',
//            'badge' => 'required',
//            'android_exercise_type' => 'required',
//            'ios_exercise_type' => 'required',
//            'selected_camera_facing' => 'required',
//            'teams' => 'required|array',
//            'teams.*' => 'required|exists:teams,id',
            "privacy"=>"required|exists:exercise_privacies,id",
//            'skills' => 'required|array',
//            'skills.*' => 'required|exists:skills,id',
//            'tools' => 'required|array',
//            'tools.*' => 'required|exists:tools,id',
//            'levels' => 'required|array',
//            'levels.*.id' => 'required|array',
//            'levels.*.measure' => 'required|array',
//            'levels.*.level_id' => 'required|exists:levels,id',
//            'levels.*.measure' => 'required'
            'camera_mode' => 'nullable|in:portrait,landscape'
        ]);

        //NEEDED MATERIAL -> TOOLS

        //SKILLS -> FOCUSES

        //EXERCISE CATEGORY

        // EXERCISE NAME

        // EXERCISE PRIVACY

        // EXERCISE DESCRIPTION

        // EXERCISE VIDEO
        try
        {
            // IF THESE PARAMS ARE IN STRING
            $request->teams = (array) json_decode($request->teams);
            $request->tools = (array) json_decode($request->tools);
            $request->levels = (array) json_decode($request->levels);
        }
        catch (\Exception $e)
        {
            // IF NOT SEND AS STRING BUT AS RAW ARRAY
            $request->levels = json_encode($request->levels);
            $request->levels = (array) json_decode($request->levels);

            $request->teams = json_encode($request->teams);
            $request->teams = (array) json_decode($request->teams);

            $request->tools = json_encode($request->tools);
            $request->tools = (array) json_decode($request->tools);
        }


        $exercise = Exercise::find($request->id);
        if(!$exercise){
            $exercise = new Exercise();
        }
        try{
            DB::transaction(function() use($request, $exercise){
                if (Storage::exists($exercise->image) && $request->hasFile('image')) {
                    Storage::delete($exercise->image);
                }
                if (Storage::exists($exercise->video) && $request->hasFile('video')) {
                    Storage::delete($exercise->video);
                }

                if ($request->hasFile('video')) {
                    $file             = $request->file('video');
                    // set storage path to store the file (actual video)
                    $destination_path = public_path().'/uploads';
                    if(!File::exists($destination_path)){
                        File::makeDirectory($destination_path);
                    }
                    // get file extension
                    $extension        = $file->getClientOriginalExtension();
                    $file_name        = date('Ymdhia').".".$extension;
                    $upload_status    = $file->move($destination_path, $file_name);

                    if($upload_status){
                        $thumbnail_path   = public_path().'/uploads';
                        $video_path       = $destination_path.'/'.$file_name;

                        // set thumbnail image name
                        $thumbnail_image  = date('Ymdhia').".jpg";

                        $thumbnail = new \Lakshmaji\Thumbnail\Thumbnail();
                        $thumbnail_status = $thumbnail->getThumbnail($video_path,$thumbnail_path,$thumbnail_image,3);
                        if($thumbnail_status){
                            $exercise->image = Storage::putFile("media/exercises/images", $destination_path."/".$thumbnail_image);
                        }
                    }
                    $exercise->video = Storage::putFile("media/exercises/videos", $video_path);
                    $exercise->video_name = $request->video_name;

                    File::delete($destination_path."/".$thumbnail_image);
                    File::delete($video_path);
                }

                $exercise->title = json_encode(['en' => $request->title, 'nl' => $request->title]);
                $exercise->description = json_encode(['en' => $request->description, 'nl' => $request->description]);
                $exercise->leaderboard_direction = $request->leaderboard_direction ?? 'asc';
                $exercise->badge = $request->badge ?? 'non_ai';
                $exercise->privacy = $request->privacy ?? "2";
                $exercise->android_exercise_type = $request->android_exercise_type;
                $exercise->ios_exercise_type = $request->ios_exercise_type;
                $exercise->score = $request->score ?? 0;
                $exercise->count_down_milliseconds = $request->count_down_milliseconds ?? 3000;
                $exercise->use_questions = $request->use_questions ?? 0;
                $exercise->selected_camera_facing = $request->selected_camera_facing ?? 'FRONT';
                $exercise->camera_mode = $request->camera_mode ?? null;
                $exercise->is_active = $request->is_active ?? 1;
                $exercise->save();
                if($request->tools && is_array($request->tools)){
                    $exercise->tools()->sync($request->tools);
                }if($request->levels && is_array($request->levels)){
//                    dd($request->levels);
                    $exercise->levels()->detach();
                    foreach($request->levels as $level) {
                        $exercise->levels()->attach($level->level_id, ['metric_type'=>$level->metric_type,
                            'measure' => $level->measure]);
                    }
                }if($request->teams && is_array($request->teams)){
                    $exercise->teams()->sync($request->teams);
                }
            });
            return Helper::apiSuccessResponse(true, 'Success',$exercise);

        }catch (\Exception $e){
            return Helper::apiErrorResponse(false, 'Failed to save Exercise',$e->getMessage());
        }

    }

    /**
     * Get Exercise Types
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": [
        "COUNTDOWN",
        "INFINITE",
        "TOTAL",
        "FAILURE",
        "TOTALREPETITIONS",
        "QUESTION",
        "HIGHSCORE",
        "NON_AI"
        ]
     * }
     * @return JsonResponse
     */
    public function getExerciseTypes(Request $request)
    {
        $data = ['COUNTDOWN','INFINITE','TOTAL','FAILURE','TOTALREPETITIONS','QUESTION','HIGHSCORE','NON_AI'];

        return Helper::apiSuccessResponse(true, 'Success',$data);
    }

    /**
     * Get Exercise Categories
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": [
    {
    "id": 1,
    "name": "Ball control"
    },
    {
    "id": 2,
    "name": "Dribbling"
    }
    ]
     * }
     * @return JsonResponse
     */
    public function getCategories(Request $request)
    {
        $data = Category::select('id', 'name')->get();

        return Helper::apiSuccessResponse(true, 'Success',$data);
    }

    /**
     * Get Exercise Tools
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": [
    {
    "id": 1,
    "tool_name": "Cones"
    },
    {
    "id": 2,
    "tool_name": "Ball"
    }
    ]
     * }
     * @return JsonResponse
     */
    public function getTools(Request $request)
    {
        $data = Tool::select('id', 'tool_name')->get();

        return Helper::apiSuccessResponse(true, 'Success',$data);
    }

    /**
     * Get Exercise Skills
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": [
    {
    "id": 1,
    "name": "Agility"
    },
    {
    "id": 2,
    "name": "Ball Control"
    }
    ]
     * }
     * @return JsonResponse
     */
    public function getSkills(Request $request)
    {
        $data = Skill::select('id', 'name')->get();

        return Helper::apiSuccessResponse(true, 'Success',$data);
    }

    /**
     * Get Exercise Levels
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": [
    {
    "id": 1,
    "title": "Level 1"
    },
    {
    "id": 2,
    "title": "Level 2"
    }
    ]
     * }
     * @return JsonResponse
     */
    public function getLevels(Request $request)
    {
        $data = Level::select('id', 'title')->get();

        return Helper::apiSuccessResponse(true, 'Success',$data);
    }

    /**
     * Get Exercise Teams
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": [
    {
    "id": 5,
    "team_name": "JOGO"
    }
    ]
     * }
     * @return JsonResponse
     */
    public function getTeams(Request $request)
    {
        $clubs = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->pluck('club_id');
        $teams = $teams = Team::select('id', 'team_name')->whereHas('clubs',function($q) use ($clubs){
            return $q->whereIn('club_id', $clubs);
        })->get();

        return Helper::apiSuccessResponse(true, 'Success',$teams);
    }

    /**
     * Get My Exercises
     *
     * @response
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": [
    {
    "id": 265,
    "title": "Wandspiel Level 6",
    "android_exercise_type": "NON_AI",
    "ios_exercise_type": "NON_AI",
    "privacy": "My Team",
    "last_edit": "2021-04-18",
    "is_active": 1,
    "categories": [
    "Juggling"
    ],
    "skills": [
    "Ball Control"
    ],
    "tools": [
    "Ball",
    "Goal"
    ],
    "teams": [
    "U13",
    "U95",
    "U78",
    "U12",
    "U21",
    "Updated Team 14 team AGAIN"
    ]
    },
    {
    "id": 268,
    "title": "Binnen-binnen-buiten",
    "android_exercise_type": "NON_AI",
    "ios_exercise_type": "NON_AI",
    "privacy": "My Team",
    "last_edit": "2021-04-18",
    "is_active": 1,
    "categories": [
    "Ball control"
    ],
    "skills": [
    "Agility"
    ],
    "tools": [
    "Cones"
    ],
    "teams": [
    "Test"
    ]
    },
    {
    "id": 269,
    "title": "Kabel: Voorwaarts",
    "android_exercise_type": "NON_AI",
    "ios_exercise_type": "NON_AI",
    "privacy": "My Team",
    "last_edit": "2021-04-18",
    "is_active": 1,
    "categories": [
    "Ball control"
    ],
    "skills": [
    "Agility"
    ],
    "tools": [
    "Cones"
    ],
    "teams": [
    "Test"
    ]
    }
    ]
    }

    @queryParam clubId required integer min:1. Example: 1

     * @return JsonResponse
     */
    public function getMyExercises(Request $request)
    {
        $request->validate([
            'clubId' => 'required|numeric|min:1|exists:clubs,id'
        ]);
        
        $myClubs = (new Club())->myCLubs($request);

        if (count($myClubs->original['Result']) > 0)
        {
            if (in_array($request->clubId, array_column($myClubs->original['Result'], 'id')))
            {
                $exercises = Exercise::select('id', 'title', 'privacy', 'android_exercise_type', 'ios_exercise_type', 'updated_at', 'is_active')
                ->with('levels', 'skills', 'teams', 'tools','exercise_privacy')
                ->whereHas('teams.clubs', function ($query) use($request)
                {
                    $query->where('club_id', $request->clubId);
                })
                ->get();

                if (count($exercises) > 0)
                {
                    $exe = $exercises->map(function ($exercise)
                    {
                        $obj = new stdClass();
                        $obj->id = $exercise->id;
                        $obj->title = $exercise->title;
                        $obj->android_exercise_type = $exercise->android_exercise_type;
                        $obj->ios_exercise_type = $exercise->ios_exercise_type;
                        $obj->privacy = $exercise->exercise_privacy;
                        $obj->last_edit = Carbon::createFromTimeString($exercise->updated_at)->format('Y-m-d');
                        $obj->is_active = $exercise->is_active;
                        $categories = $exercise->categories->pluck('name');
                        $obj->categories = $categories;
                        $skills = $exercise->skills->pluck('name');
                        $obj->skills = $skills;
                        $tools = $exercise->tools->pluck('tool_name');
                        $obj->tools = $tools;
                        $teams = $exercise->teams->pluck('team_name');
                        $obj->teams = $teams;
                        $obj->clubs = $exercise->teams;

                        return $obj;
                    });

                    return Helper::apiSuccessResponse(true, 'Success', $exe);
                }
                else
                {
                    return Helper::apiNotFoundResponse(false, 'No records found', []);
                }
            }
            else
            {
                return Helper::apiNotFoundResponse(true, 'Invalid club id', []);
            }
        }
        else
        {
            return Helper::apiNotFoundResponse(false, 'No clubs found', []);
        }
    }

    /**
     * Get My Exercises Detail
     *
     * @response
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": {
    "id": 308,
    "title_en": "Perform This Exercise",
    "title_nl": "Perform This Exercise",
    "description_en": "Custom description",
    "description_nl": "Custom description",
    "android_exercise_type": null,
    "ios_exercise_type": null,
    "is_active": 1,
    "video": null,
    "video_name": null,
    "image": null,
    "privacy": "my_team",
    "categories": [
    1
    ],
    "skills": [
    1,
    2,
    3
    ],
    "tools": [
    1
    ],
    "teams": [],
    "levels": [
    {
    "level_id": 1,
    "metric_type": "COUNTDOWN",
    "measure": "90",
    "pivot": {
    "exercise_id": 308,
    "level_id": 1,
    "measure": "90"
    }
    }
    ]
    }
    }
     * @queryParam exercise_id required integer
     * @return JsonResponse
     */
    public function getMyExercisesDetail(Request $request)
    {
        $request->validate([
            "exercise_id"=>"required|integer"
        ]);

        $ex = Exercise::select('*', 'title->en AS title_en', 'title->nl AS title_nl', 'description->en AS description_en', 'description->nl AS description_nl')->with('levels', 'skills', 'teams', 'tools')->with(['levels' => function($q) {
            $q->select('level_id','metric_type', 'measure');
        }])
            ->where('id', $request->exercise_id)->first();

        if (!$ex)
        {
            return Helper::apiNotFoundResponse(false,"No Exercise Found", new stdClass());
        }

            $obj = new stdClass();
            $obj->id = $ex->id;
            $obj->title_en = $ex->title_en;
            $obj->title_nl = $ex->title_nl;
            $obj->description_en = $ex->description_en;
            $obj->description_nl = $ex->description_nl;
            $obj->android_exercise_type = $ex->android_exercise_type;
            $obj->ios_exercise_type = $ex->ios_exercise_type;
            $obj->is_active = $ex->is_active;
            $obj->video = $ex->video;
            $obj->video_name = $ex->video_name;
            $obj->image = $ex->image;
            $obj->privacy = $ex->privacy;
            $categories = $ex->categories->pluck('id');
            $obj->categories = $categories;
            $skills = $ex->skills->pluck('id');
            $obj->skills = $skills;
            $tools = $ex->tools->pluck('id');
            $obj->tools = $tools;
            $teams = $ex->teams->pluck('id');
            $obj->teams = $teams;
            $obj->levels = $ex->levels;
        return Helper::apiSuccessResponse(true, 'Success', $obj);
    }




    /**
     * Update Exercise Status
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": {
    "id": 263,
    "title": "Test",
    "description": "Test",
    "image": null,
    "video": null,
    "leaderboard_direction": "asc",
    "badge": "non_ai",
    "android_exercise_type": "COUNTDOWN",
    "ios_exercise_type": "COUNTDOWN",
    "score": 0,
    "count_down_milliseconds": 3000,
    "use_questions": 0,
    "selected_camera_facing": "FRONT",
    "unit": null,
    "is_active": "2",
    "created_at": "2021-02-08 12:14:13",
    "updated_at": "2021-02-10 09:38:13",
    "deleted_at": null
    }
     * }
     *
     * @bodyParam exercise_id integer required
     * @bodyParam is_active integer required (1 (active)/2 (deactive))
     * @return JsonResponse
     */
    public function updateExerciseStatus(Request $request)
    {
        $this->validate($request,[
            'exercise_id' => 'required|exists:exercises,id',
            'is_active' => 'required|in:1,2'
        ]);
        $exercise = Exercise::find($request->exercise_id);
        if(!$exercise){
            return Helper::apiErrorResponse(false, 'Invalid Exercise',new stdClass());
        }
        $exercise->is_active = $request->is_active;
        $exercise->save();
        return Helper::apiSuccessResponse(true, 'Success',$exercise);
    }

    /**
     * Get Exercise Privacies
     *
     * @response
    {
        "Response": true,
        "StatusCode": 200,
        "Message": "Success",
        "Result": [
            {
            "id": 1,
            "name": "Open to invites"
            },
            {
            "id": 2,
            "name": "Closed to invites"
            }
        ]
    }
     * @return JsonResponse
     */

    public function getPrivacy(){
        $privacies = ExercisePrivacy::select('id','name')->whereStatus('active')->get();
        return Helper::apiSuccessResponse(true,"Success",$privacies);
    }

}
