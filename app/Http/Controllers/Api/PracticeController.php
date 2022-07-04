<?php

namespace App\Http\Controllers\Api;

use App\{
    Assignment,
    AssignmentExercise,
    Category,
    Exercise,
    ExerciseAiData,
    ExerciseKpiResponseError,
    Level,
    Match,
    MatchStat,
    PlayerAssignment,
    PlayerExercise,
    PlayerScore,
    Post,
    Skill,
    Status,
    Tool,
    User,
    UserSensor
};
use App\Helpers\{
    Helper,
    HumanOx
};
use App\Http\Controllers\Controller;
use App\Imports\AiImport;
use App\Team;
use Illuminate\Http\{
    JsonResponse,
    Request
};
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Storage,
    Validator
};
use stdClass;
use Exception;

/**
 * @authenticated
 * @group Assignment & Exercise Api's
 */
class PracticeController extends Controller
{
    private $exericseModel;
    public function __construct()
    {
        $this->exericseModel = new Exercise();
    }

    private function getTeamIdArray($request)
    {
        $roles =  Auth::user()->roles->pluck('name')->toArray();
        if(in_array('trainer',$roles)){
            $teams = Team::whereHas('clubs',function($q) use ($request){
                return $q->where('club_id',$request->club_id);
            })->pluck('id');
            return DB::table('team_trainers')->where('trainer_user_id', Auth::user()->id)->whereIn('team_id',$teams)->pluck('team_id');
        }else{
            return DB::table('player_team')->where('user_id', Auth::user()->id)->pluck('team_id');
        }
    }

    private function getPlayerScores($request){
        return DB::table("player_scores")->select(["score", "created_at"])->where("exercise_id", $request->exercise_id)->where("user_id", auth()->user()->id);
    }

    /**
     * Get Exercise Categories
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": [
     * {
     * "id": 1,
     * "name": "cat1",
     * "description": "sdfsdf",
     * "image": null
     * },
     * {
     * "id": 2,
     * "name": "cat 2",
     * "description": "loremipsum",
     * "image": null
     * }
     * ]
     * }
     *
     * @return JsonResponse
     * @queryParam type_id required integer
     */

    public function getExerciseCategories(Request $request)
    {
        $request->validate([
            "type_id" => "required|integer|exists:types,id"
        ]);

//        $categories = Category::select('id', 'name', 'description', 'image', 'new')->whereHas("types",function ($type) use
//        ($request) {
//            $type->where("type_id",$request->type_id);
//        })->latest()->get();

        $categories = Category::select('id', 'name', 'description', 'image', 'new')->latest()->get();

        if (count($categories) > 0) {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $categories);
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', []);
    }


    /**
     * Get Category Exercises
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "exercises": [
     * {
     * "id": 2,
     * "title": "bench press",
     * "description": "bench pressbench pressbench pressbench press",
     * "image": null,
     * "video": null,
     * "badge": "ai",
     * "is_active": 1,
     * "skills": [],
     * "tools": [],
     * "levels": [
     * {
     * "id": 1,
     * "title": "level 1",
     * "pivot": {
     * "level_id": 2,
     * "exercise_id": 1
     * }
     * }
     * ]
     * },
     * {
     * "id": 1,
     * "title": "dumbell curl",
     * "description": "dumbell curldumbell curl",
     * "image": null,
     * "video": null,
     * "badge": "non_ai",
     * "is_active": 1,
     * "skills": [
     * {
     * "id": 1,
     * "name": "Agility",
     * "pivot": {
     * "exercise_id": 1,
     * "skill_id": 1
     * }
     * }
     * ],
     * "tools": [
     * {
     * "id": 1,
     * "tool_name": "Tool 1",
     * "pivot": {
     * "exercise_id": 1,
     * "tool_id": 1
     * }
     * }
     * ],
     * "levels": [
     * {
     * "id": 1,
     * "title": "level 1",
     * "pivot": {
     * "level_id": 1,
     * "exercise_id": 1
     * }
     * }
     * ]
     * }
     * ],
     * "filters": {
     * "tools": [
     * {
     * "id": 1,
     * "tool_name": "Tool 1"
     * },
     * {
     * "id": 2,
     * "tool_name": "Tool 2"
     * }
     * ],
     * "skills": [
     * {
     * "id": 1,
     * "name": "Agility"
     * },
     * {
     * "id": 2,
     * "name": "Bal Control"
     * }
     * ],
     * "levels": [
     * {
     * "id": 1,
     * "title": "level 1"
     * },
     * {
     * "id": 2,
     * "title": "level 2"
     * }
     * ],
     * "badges": [
     * {
     * "title":"ai"
     * },
     * {
     * "title":"non_ai"
     * }
     * ]
     * }
     * }
     * }
     *
     * @urlParam category_id required
     * @urlParam platform required options: ios, android
     *
     * @return JsonResponse
     */

    private function exerciseBasicRelationsQuery($exercise)
    {
        $exercise = $exercise->with([
            'skills' => function ($query)
            {
                $query->select('skills.id', 'skills.name');
            },
            'tools' => function ($query)
            {
                $query->select('tools.id', 'tools.tool_name', 'tools.icon');
            },
            'levels' => function ($query)
            {
                $query->select('levels.id', 'levels.title');
            }
        ]);

        return $exercise;
    }

    public function getCategoryExercises(Request $request)
    {
        Validator::make($request->all(), [
            'category_id' => 'required',
            'platform' => "required|in:ios,android"
        ])->validate();

        $status = Status::where('name', 'active')->first();
        $exercises = Exercise::doesntHave('teams')->select('id', 'title', 'description', 'image', 'video', 'badge', 'is_active')
            ->whereHas('categories', function ($q1) use ($request) {
                $q1->where('categories.id', $request->category_id);
            });

        $this->exerciseBasicRelationsQuery($exercises);

        $exercises = $exercises->latest()
            ->where('is_active', $status->id ?? 0)
            /*->where('video_file', '!=', NULL)
            ->where('completion_time', '>', 0)
            ->whereHas('player_scores_users')*/
            ->get();


        foreach ($exercises as $key) {
            foreach ($key->tools as $value) {
                $name = $value->tool_name;
                if (str_contains($name, "/") == true) {
                    $name = str_replace("/", "_", $name);

                }
                $name = strtolower($name);
                $value->file_name = $name;
            }

        }

        $ex_ids = $exercises->pluck('id');

        $skills = $this->skillsQuery($ex_ids);

        $tools = $this->toolsQuery($ex_ids);

        $levels = $this->levelsQuery($ex_ids);

        foreach ($exercises as $key => $value) {
            if ($request->platform == "android") // IF PLATFORM/DEVICE_TYPE IS ANDROID
            {
                if ($value->badge == "ai_android" || $value->badge == "ai_both") // ANDROID_AI or AI_BOTH
                {
                    $exercises[$key]->badge = "ai"; // THEN SET THE BADGE TO AI
                } else {
                    $exercises[$key]->badge = "non_ai"; // ELSE SET THE BADGE TO NON_AI
                }
            } elseif ($request->platform == "ios") { // IF PLATFORM/DEVICE_TYPE IS IOS
                if ($value->badge == "ai_ios" || $value->badge == "ai_both") // IOS_AI OR AI_BOTH
                {
                    $exercises[$key]->badge = "ai"; // SET THE BADGE TO AI
                } else {
                    $exercises[$key]->badge = "non_ai"; // ELSE SET THE BADGE TO NON_AI
                }
            }
        }

        $badge1 = new stdClass();
        $badge1->title = 'ai';

        $response['exercises'] = $exercises;
        $response['filters'] = ['tools' => $tools, 'skills' => $skills, 'levels' => $levels, 'badges' => [$badge1]];

        if (count($response['exercises']) > 0) {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', $response);
    }

    /**
        All exercises

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Records found successfully!",
            "Result": {
                "exercises": [
                    {
                        "id": 253,
                        "title": "Russian twists",
                        "description": "Start by sitting upright on the floor, with your legs grounded, and your knees in a 90-degree angle. Raise your feet and lean slightly backwards until you feel the tension in your abdominal muscles. Twist your torso, while touching the ground next to your hip with either your hands or a ball.",
                        "image": "media/exercise/images/JOGO_B20.jpeg",
                        "video": "media/exercise/BV2/JOGO_B20_v2.mp4",
                        "badge": "non_ai",
                        "is_active": 1,
                        "skills": [
                            {
                                "id": 13,
                                "name": "Core",
                                "pivot": {
                                    "exercise_id": 253,
                                    "skill_id": 13
                                }
                            },
                            {
                                "id": 14,
                                "name": "Condition",
                                "pivot": {
                                    "exercise_id": 253,
                                    "skill_id": 14
                                }
                            }
                        ],
                        "tools": [],
                        "levels": [
                            {
                                "id": 1,
                                "title": "Level 1",
                                "pivot": {
                                    "exercise_id": 253,
                                    "level_id": 1,
                                    "measure": "36"
                                }
                            }
                        ]
                    }
                ]
            }
        }
     * @bodyParam club_id integer optional it is required only when you use trainerapp
    */

    public function allExercises(Request $request)
    {
        $status = Status::where('name', 'active')->first();
        $teams = $this->getTeamIdArray($request);
        $custom_exercises = Exercise::whereHas('teams', function ($q) use ($teams) {
            $q->whereIn('team_id', $teams);
        })->select('id', 'title', 'description', 'image', 'video', 'badge', 'is_active');

        $this->exerciseBasicRelationsQuery($custom_exercises);

        $custom_exercises = $custom_exercises->latest()
            ->where('is_active', $status->id ?? 0)->get();
        $custom_exercises = $this->getToolName($custom_exercises)->toArray();

        $exercises = Exercise::select('id', 'title', 'description', 'image', 'video', 'badge', 'is_active')
        ->doesntHave('teams');
        $this->exerciseBasicRelationsQuery($exercises);

        $exercises = $exercises->latest()
        ->where('is_active', $status->id ?? 0)
        ->get();

        $exercises = $this->getToolName($exercises)->toArray();

        $response['exercises'] = array_merge($custom_exercises,$exercises);

        if (count($response['exercises']) > 0)
        {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', $response);
    }

    /**
     * Platform exercises
     *
     * @response{
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully",
     * "Result": [
     * {
     * "id": 215,
     * "title": "Jumping jacks",
     * "description": "Stand upright with your legs together, arms next to your body. Slightly bend your knees, and jump into the air. At the same time, simultaneously spread your legs and stretch your arms out and over your head. Jump back into the starting position. Repeat.",
     * "android_exercise_type": "NREPETITIONS",
     * "nseconds": 30000,
     * "count_down_milliseconds": 30000,
     * "score": 20,
     * "android_exercise_variation": 1,
     * "question_count": 0,
     * "answer_count": 0,
     * "question_path": "pictureQuestions.json",
     * "question_mode": "PICTURE"
     * }
     * ]
     * }
     *
     * @response 404{
     * "Response": false,
     * "StatusCode": 404,
     * "Message": "No records found",
     * "Result": []
     * }
     *
     * @queryParam platform required string options:android,ios. Example: android
     */

    protected function platformExercises(Request $request)
    {
        $request->validate([
            'platform' => 'required|string|in:android,ios'
        ]);

        $status = Status::select('id')
            ->where('name', 'active')
            ->first();

        $exercises = Exercise::select('id', 'title', 'description', $request->platform . '_exercise_type', 'nseconds', 'count_down_milliseconds', 'score', $request->platform . '_exercise_variation', 'question_count', 'answer_count', 'question_path', 'question_mode')
            ->whereIn('badge', $status->platform == 'android' ? ['ai_android', 'ai_both'] : ['ai_ios', 'ai_both'])
            ->where('is_active', $status->id ?? 1)
            ->orderBy('created_at', 'desc')
            ->get();

        if (count($exercises) > 0) {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $exercises);
        } else {
            return Helper::apiNotFoundResponse(false, 'Records not found', []);
        }
    }


    /**
     * Get Custom Exercises
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "exercises": [
     * {
     * "id": 2,
     * "title": "bench press",
     * "description": "bench pressbench pressbench pressbench press",
     * "image": null,
     * "video": null,
     * "badge": "ai",
     * "is_active": 1,
     * "skills": [],
     * "tools": [],
     * "levels": [
     * {
     * "id": 1,
     * "title": "level 1",
     * "pivot": {
     * "level_id": 2,
     * "exercise_id": 1
     * }
     * }
     * ]
     * },
     * {
     * "id": 1,
     * "title": "dumbell curl",
     * "description": "dumbell curldumbell curl",
     * "image": null,
     * "video": null,
     * "badge": "non_ai",
     * "is_active": 1,
     * "skills": [
     * {
     * "id": 1,
     * "name": "Agility",
     * "pivot": {
     * "exercise_id": 1,
     * "skill_id": 1
     * }
     * }
     * ],
     * "tools": [
     * {
     * "id": 1,
     * "tool_name": "Tool 1",
     * "pivot": {
     * "exercise_id": 1,
     * "tool_id": 1
     * }
     * }
     * ],
     * "levels": [
     * {
     * "id": 1,
     * "title": "level 1",
     * "pivot": {
     * "level_id": 1,
     * "exercise_id": 1
     * }
     * }
     * ]
     * }
     * ],
     * "filters": {
     * "tools": [
     * {
     * "id": 1,
     * "tool_name": "Tool 1"
     * },
     * {
     * "id": 2,
     * "tool_name": "Tool 2"
     * }
     * ],
     * "skills": [
     * {
     * "id": 1,
     * "name": "Agility"
     * },
     * {
     * "id": 2,
     * "name": "Bal Control"
     * }
     * ],
     * "levels": [
     * {
     * "id": 1,
     * "title": "level 1"
     * },
     * {
     * "id": 2,
     * "title": "level 2"
     * }
     * ],
     * "badges": [
     * {
     * "title":"ai"
     * },
     * {
     * "title":"non_ai"
     * }
     * ]
     * }
     * }
     * }
     *
     * @urlParam category_id required
     *
     * @return JsonResponse
     */

    private function skillsQuery($exercisesId)
    {
        $skills = Skill::select('name')->whereHas('exercises', function ($query) use ($exercisesId)
        {
            $query->whereIn('exercise_id', $exercisesId);
        })
        ->get();

        return $skills;
    }

    private function toolsQuery($exercisesId)
    {
        $tools = Tool::select('tool_name', 'icon')->whereHas('exercises', function ($query) use ($exercisesId)
        {
            $query->whereIn('exercise_id', $exercisesId);
        })
        ->get();

        return $tools;
    }

    private function levelsQuery($exercisesId)
    {
        $levels = Level::select('title')->whereHas('exercises', function ($query) use ($exercisesId)
        {
            $query->whereIn('exercise_id', $exercisesId);
        })->get();

        return $levels;
    }

    public function getCustomExercises(Request $request)
    {
        $status = Status::where('name', 'active')->first();
        $teams = $this->getTeamIdArray($request);
        $exercises = Exercise::whereHas('teams', function ($q) use ($teams) {
            $q->whereIn('team_id', $teams);
        })->select('id', 'title', 'description', 'image', 'video', 'badge', 'is_active');

        $this->exerciseBasicRelationsQuery($exercises);

        $exercises = $exercises->latest()
            ->where('is_active', $status->id ?? 0)
            /*->where('video_file', '!=', NULL)
            ->where('completion_time', '>', 0)
            ->whereHas('player_scores_users')*/
            ->get();

        $ex_ids = $exercises->pluck('id');

        $skills = $this->skillsQuery($ex_ids);

        $tools = $this->toolsQuery($ex_ids);

        $levels = $this->levelsQuery($ex_ids);

        if (count($teams) > 0) {
            $club_id = DB::table('club_teams')->where('team_id', $teams[0])->value('club_id');
            $club_logo = DB::table('clubs')->where('id', $club_id)->value('image');
        }

        $badge1 = new stdClass();
        $badge1->title = 'ai';

        $response['exercises'] = $exercises;
        $response['filters'] = ['tools' => $tools, 'skills' => $skills, 'levels' => $levels, 'badges' => [$badge1]];

        if (isset($club_logo)) {
            $response['club_logo'] = $club_logo;
        }
        if (count($response['exercises']) > 0) {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', $response);
    }

    public function sortLeaderboard($data)
    {
        $data = $data;
        $data2 = $data;
        for ($i = 0; $i < count($data); $i++) {
            if (!isset($data[$i]->player_scores_skills[0])) {
                unset($data2[$i]);
            }
        }
        $data = array_values($data2);

        $score = [];
        for ($i = 0; $i < count($data); $i++) {
            if (isset($data[$i]->player_scores_skills[0])) {
                $score[] = ['index' => $i, 'score' => $data[$i]->player_scores_skills[0]->score];
            }
        }
        for ($j = 0; $j < count($score); $j++) {
            for ($i = 0; $i < count($score) - 1; $i++) {

                if ($score[$i]['score'] < $score[$i + 1]['score']) {
                    $temp = $score[$i + 1];
                    $score[$i + 1] = $score[$i];
                    $score[$i] = $temp;
                }
            }
        }

        $leaderboard = [];
        for ($i = 0; $i < count($score); $i++) {
            $leaderboard[] = $data[$score[$i]['index']];
        }

        return $leaderboard;
    }

    /**
     * Get Exercise Detail
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "exercise": {
     * "id": 215,
     * "title": "Jumping jacks",
     * "description": "Stand upright with your legs together, arms next to your body. Slightly bend your knees, and jump into the air. At the same time, simultaneously spread your legs and stretch your arms out and over your head. Jump back into the starting position. Repeat.",
     * "image": "media/exercise/images/jumpingjack.jpeg",
     * "video": "NULL",
     * "leaderboard_direction": "asc",
     * "badge": "ai_both",
     * "android_exercise_type": "NREPETITIONS",
     * "ios_exercise_type": "NREPETITIONS",
     * "score": 20,
     * "count_down_milliseconds": 30000,
     * "use_questions": 0,
     * "selected_camera_facing": "FRONT",
     * "unit": null,
     * "android_exercise_variation": 1,
     * "ios_exercise_variation": 0,
     * "question_count": 0,
     * "answer_count": 0,
     * "camera_mode": "portrait",
     * "nseconds": 30000,
     * "question_mode": "PICTURE",
     * "leaderboard": [
     * {
     * "id": 27,
     * "first_name": "Hassan",
     * "last_name": "Shah",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 27,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 50,
     * "pivot": {
     * "user_id": 27,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 50,
     * "created_at": "2020-12-09 15:02:04",
     * "updated_at": "2020-12-09 15:02:04"
     * }
     * }
     * ]
     * },
     * {
     * "id": 73,
     * "first_name": "Thomas",
     * "last_name": "Andre de la Porte",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 73,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 50,
     * "pivot": {
     * "user_id": 73,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 50,
     * "created_at": "2020-11-16 13:15:49",
     * "updated_at": "2020-11-16 13:15:49"
     * }
     * }
     * ]
     * },
     * {
     * "id": 2,
     * "first_name": "Fatima",
     * "last_name": "Sultana",
     * "middle_name": null,
     * "profile_picture": "media/users/60a3d1946b6701621348756.jpeg",
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 2,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 2,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-07 10:55:56",
     * "updated_at": "2021-05-07 10:55:56"
     * }
     * }
     * ]
     * },
     * {
     * "id": 122,
     * "first_name": "Sohail",
     * "last_name": "Zia",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 122,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 122,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-21 12:50:40",
     * "updated_at": "2021-05-21 12:50:40"
     * }
     * }
     * ]
     * },
     * {
     * "id": 16,
     * "first_name": "Ali",
     * "last_name": "Mehdi",
     * "middle_name": "''",
     * "profile_picture": "media/users/609543696d2ac1620394857.jpeg",
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 16,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 15,
     * "pivot": {
     * "user_id": 16,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 15,
     * "created_at": "2020-11-09 13:25:31",
     * "updated_at": "2020-11-09 13:25:31"
     * }
     * }
     * ]
     * },
     * {
     * "id": 3,
     * "first_name": "Hasnain",
     * "last_name": "Ali",
     * "middle_name": null,
     * "profile_picture": "media/users/609bdad748e321620826839.jpeg",
     * "completion_time": 15,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 3,
     * "level_id": null
     * },
     * "player_scores_skills": [
     * {
     * "score": 6,
     * "pivot": {
     * "user_id": 3,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": null,
     * "score": 6,
     * "created_at": "2021-06-10 16:10:59",
     * "updated_at": "2021-06-10 16:10:59"
     * }
     * }
     * ]
     * },
     * {
     * "id": 4,
     * "first_name": "Tariq",
     * "last_name": "Sidd",
     * "middle_name": null,
     * "profile_picture": "media/users/5f7b294249cca1601907010.jpeg",
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 4,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 5,
     * "pivot": {
     * "user_id": 4,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": null,
     * "score": 5,
     * "created_at": "2021-06-10 16:11:44",
     * "updated_at": "2021-06-10 16:11:44"
     * }
     * }
     * ]
     * }
     * ],
     * "exercise_tips": [
     * {
     * "exercise_id": 215,
     * "description": "Put your smartphone against a wall, water bottle or any other object it can steady lean on during your exercise.",
     * "media": "https://jogobucket.s3.eu-west-2.amazonaws.com/media/exercise_tips/151-1.png",
     * "media_type": "image",
     * "orientation": "portrait"
     * },
     * {
     * "exercise_id": 215,
     * "description": "The exercise starts automatically with a countdown once your body is fully visible to the camera.",
     * "media": "https://jogobucket.s3.eu-west-2.amazonaws.com/media/exercise_tips/151-2.png",
     * "media_type": "image",
     * "orientation": "portrait"
     * },
     * {
     * "exercise_id": 215,
     * "description": "Stand straight with your legs hip-width, arms next to your body",
     * "media": "https://jogobucket.s3.eu-west-2.amazonaws.com/media/exercise_tips/215-3.png",
     * "media_type": "image",
     * "orientation": "portrait"
     * },
     * {
     * "exercise_id": 215,
     * "description": "Slightly bend your knees, and jump into the air. Then simultaneously spread your legs shoulder-width apart and stretch your arms out, over your head.",
     * "media": "https://jogobucket.s3.eu-west-2.amazonaws.com/media/exercise_tips/215-4.png",
     * "media_type": "image",
     * "orientation": "portrait"
     * }
     * ],
     * "levels": [
     * {
     * "id": 1,
     * "title": "Level 1",
     * "image": "media/tools/level_1.png",
     * "measure": "21",
     * "status": "0",
     * "pivot": {
     * "exercise_id": 215,
     * "level_id": 1,
     * "measure": "21"
     * }
     * }
     * ],
     * "tools": [],
     * "categories": [
     * {
     * "id": 5,
     * "name": "Fitness",
     * "description": "Fitness training improves your physical performance just as your risk of injury.",
     * "image": "https://d1jpc7l8q60rkw.cloudfront.net/media/exercise/categories/B5+squads.jpeg",
     * "new": 0,
     * "created_at": null,
     * "updated_at": null,
     * "deleted_at": null,
     * "pivot": {
     * "exercise_id": 215,
     * "category_id": 5
     * }
     * }
     * ]
     * },
     * "current_player_index": 1,
     * "full_list_leaderboard": [
     * {
     * "id": 27,
     * "first_name": "Hassan",
     * "last_name": "Shah",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 27,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 50,
     * "pivot": {
     * "user_id": 27,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 50,
     * "created_at": "2020-12-09 15:02:04",
     * "updated_at": "2020-12-09 15:02:04"
     * }
     * }
     * ]
     * },
     * {
     * "id": 73,
     * "first_name": "Thomas",
     * "last_name": "Andre de la Porte",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 73,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 50,
     * "pivot": {
     * "user_id": 73,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 50,
     * "created_at": "2020-11-16 13:15:49",
     * "updated_at": "2020-11-16 13:15:49"
     * }
     * }
     * ]
     * },
     * {
     * "id": 159,
     * "first_name": "first name",
     * "last_name": "last name",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 11,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 159,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 50,
     * "pivot": {
     * "user_id": 159,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 50,
     * "created_at": "2020-12-13 16:47:49",
     * "updated_at": "2020-12-13 16:47:49"
     * }
     * }
     * ]
     * },
     * {
     * "id": 122,
     * "first_name": "Sohail",
     * "last_name": "Zia",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 122,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 122,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-21 12:50:40",
     * "updated_at": "2021-05-21 12:50:40"
     * }
     * }
     * ]
     * },
     * {
     * "id": 461,
     * "first_name": "muhammad",
     * "last_name": "fahad",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 461,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 461,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-07 16:41:08",
     * "updated_at": "2021-05-07 16:41:08"
     * }
     * }
     * ]
     * },
     * {
     * "id": 477,
     * "first_name": "Ali",
     * "last_name": "Ahmed",
     * "middle_name": "''",
     * "profile_picture": "media/users/608fef8c625061620045708.jpeg",
     * "completion_time": 71,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 477,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 477,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-07 13:11:44",
     * "updated_at": "2021-05-07 13:11:44"
     * }
     * }
     * ]
     * },
     * {
     * "id": 16,
     * "first_name": "Ali",
     * "last_name": "Mehdi",
     * "middle_name": "''",
     * "profile_picture": "media/users/609543696d2ac1620394857.jpeg",
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 16,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 15,
     * "pivot": {
     * "user_id": 16,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 15,
     * "created_at": "2020-11-09 13:25:31",
     * "updated_at": "2020-11-09 13:25:31"
     * }
     * }
     * ]
     * },
     * {
     * "id": 3,
     * "first_name": "Hasnain",
     * "last_name": "Ali",
     * "middle_name": null,
     * "profile_picture": "media/users/609bdad748e321620826839.jpeg",
     * "completion_time": 15,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 3,
     * "level_id": null
     * },
     * "player_scores_skills": [
     * {
     * "score": 6,
     * "pivot": {
     * "user_id": 3,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": null,
     * "score": 6,
     * "created_at": "2021-06-10 16:10:59",
     * "updated_at": "2021-06-10 16:10:59"
     * }
     * }
     * ]
     * },
     * {
     * "id": 4,
     * "first_name": "Tariq",
     * "last_name": "Sidd",
     * "middle_name": null,
     * "profile_picture": "media/users/5f7b294249cca1601907010.jpeg",
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 4,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 5,
     * "pivot": {
     * "user_id": 4,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": null,
     * "score": 5,
     * "created_at": "2021-06-10 16:11:44",
     * "updated_at": "2021-06-10 16:11:44"
     * }
     * }
     * ]
     * }
     * ],
     * "related_exercise": [
     * {
     * "id": 320,
     * "title": "EXERCISE USING FORM DATA",
     * "description": "1sdfasdf",
     * "image": null,
     * "video": null,
     * "skills": [],
     * "tools": [],
     * "levels": []
     * },
     * {
     * "id": 316,
     * "title": "EXERCISE USING FORM DATA",
     * "description": "1sdfasdf",
     * "image": null,
     * "video": null,
     * "skills": [],
     * "tools": [],
     * "levels": []
     * },
     * {
     * "id": 213,
     * "title": "Singe leg deadlifts",
     * "description": "Start with your feet hip-width apart. Lean forward on one leg with your knee slightly bent, shifting your body weight onto one leg while the other one extends straight behind you. Once your upper body is parallel to the ground and forms a 'T' shape, return slowly back to starting the position. Repeat with your other leg — this exercise requests both core stability and strength in the hamstrings and upper back.",
     * "image": "media/exercise/images/JOGO_B1.jpeg",
     * "video": "media/exercise/BV2/JOGO_B1_v2.mp4",
     * "skills": [
     * {
     * "id": 5,
     * "name": "Power",
     * "pivot": {
     * "exercise_id": 213,
     * "skill_id": 5
     * }
     * }
     * ],
     * "tools": [],
     * "levels": [
     * {
     * "id": 1,
     * "title": "Level 1",
     * "pivot": {
     * "exercise_id": 213,
     * "level_id": 1,
     * "measure": "36"
     * }
     * }
     * ]
     * },
     * {
     * "id": 214,
     * "title": "Nordic hamstring exercise (NHE)",
     * "description": "Start in a kneeling position with your ankles held in place by a partner or an immobile object (bar, barbell, etc.). Slowly lean forward with your back straight. When you can't resist anymore, fall down on the floor — catching yourself with your hands.",
     * "image": "media/exercise/images/JOGO_B2.jpeg",
     * "video": "media/exercise/BV2/JOGO_B2_v2.mp4",
     * "skills": [
     * {
     * "id": 5,
     * "name": "Power",
     * "pivot": {
     * "exercise_id": 214,
     * "skill_id": 5
     * }
     * }
     * ],
     * "tools": [],
     * "levels": [
     * {
     * "id": 1,
     * "title": "Level 1",
     * "pivot": {
     * "exercise_id": 214,
     * "level_id": 1,
     * "measure": "36"
     * }
     * }
     * ]
     * },
     * {
     * "id": 216,
     * "title": "Lunges",
     * "description": "Start with your feet hip-width apart and body straight. Move one leg forward and lower your hip until both knees reach a 90-degree angle. Rise back to the starting position. Repeat with your other leg.",
     * "image": "media/exercise/images/JOGO_B3.jpeg",
     * "video": "media/exercise/BV2/JOGO_B3_v2.mp4",
     * "skills": [
     * {
     * "id": 5,
     * "name": "Power",
     * "pivot": {
     * "exercise_id": 216,
     * "skill_id": 5
     * }
     * }
     * ],
     * "tools": [],
     * "levels": [
     * {
     * "id": 1,
     * "title": "Level 1",
     * "pivot": {
     * "exercise_id": 216,
     * "level_id": 1,
     * "measure": "36"
     * }
     * }
     * ]
     * }
     * ],
     * "personal_best": {
     * "score": 20,
     * "time": "2021-06-21 10:07:00"
     * }
     * }
     * }
     *
     * @urlParam exercise_id required
     *
     * @return JsonResponse
     */

    public function getExerciseDetail(Request $request)
    {
        Validator::make($request->all(), [
            'exercise_id' => 'required'
        ])->validate();

        $status = Status::where('name', 'active')->first();
        $flag = false;
        $exe = Exercise::find($request->exercise_id);
        $ex = $request->exercise_id;
        $exercise = [];
        $team_id = $this->getTeamIdArray($request);
        $exercise = $this->exericseModel->getExerciseDetails($ex,$exe,$request,function ($query) use ($exe) {
            $query->select('users.id', 'users.first_name', 'users.last_name', 'users.middle_name', 'users.profile_picture', DB::raw("ROUND(completion_time) as completion_time"))->groupBy('user_id');
        });

        if (!$exercise) {
            return Helper::apiNotFoundResponse(false, "Exercise Not Found", new stdClass());
        }

        //Tools as a file_name
        foreach ($exercise->tools as $value) {
            $value->file_name = strtolower(str_replace("/", "_", $value->tool_name));
        }

        $index_player = null;
        if (isset($exercise->leaderboard)) {
            for ($i = 0; $i < count($exercise->leaderboard); $i++) {
                if (Auth::user()->id == $exercise->leaderboard[$i]->id) {
                    $flag = true;
                }
                if ($i == 9) {
                    break;
                }

            }


            for ($i = 0; $i < count($exercise->leaderboard); $i++) {
                if (Auth::user()->id == $exercise->leaderboard[$i]->id) {
                    $index_player = $i + 1;
                }
            }

            $arr = [];
            $arr1 = [];
            $i = 0;
            for ($i = 0; $i < count($exercise->leaderboard); $i++) {
                if ($i == 10) {
                    break;
                }
                if (isset($exercise->leaderboard[$i]->player_scores_skills[0]->score)) {
                    $arr[$i] = $exercise->leaderboard[$i];

                }


            }

            for ($i = 1; $i < count($exercise->leaderboard); $i++) {
                if (isset($exercise->leaderboard[1]->player_scores_skills[0]->score)) {
                    $arr1[$i] = $exercise->leaderboard[$i];
                }

            }

            if (!$flag) {

                $obj = $this->exericseModel->getExerciseDetails($ex,$exe,$request,
                    function ($query) {
                        $query->select('users.id', 'users.first_name', 'users.last_name', 'users.middle_name', 'users.profile_picture', DB::raw("ROUND(completion_time) as completion_time"))
                            ->where('user_id', auth()->user()->id)
                            ->limit(1);
                    },
                    function ($query) use($exe){
                        $query->orderBy('player_scores.score', $exe->leaderboard_direction)->groupBy('user_id');
                });

                if (isset($obj->leaderboard[0])) {
                    $data = $obj->leaderboard[0];
                    $arr = array_merge($arr, [$data]);

                }
            }
            $list = $arr1;
            $list = self::sortLeaderboard($list);
            unset($exercise->leaderboard);
            $exercise->leaderboard = $arr;
        }
        $temp = $exercise->leaderboard;
        $exercise->leaderboard = self::sortLeaderboard($temp);


        $response['exercise'] = $exercise;
        $response['current_player_index'] = $index_player;
        $response['full_list_leaderboard'] = $list;
        $response['related_exercise'] = [];

        if ($exercise) {
            $teams = $this->getTeamIdArray($request);
            $other_teams_exercises = DB::table('exercise_teams')->distinct()->whereNotIn('team_id', $teams)->pluck('exercise_id');
            $related_exercises = Exercise::select('id', 'title', 'description', 'image', 'video')
                ->whereHas('categories', function ($q1) use ($exercise) {
                    $q1->whereIn('categories.id', $exercise->categories->pluck('id'));
                });

            $this->exerciseBasicRelationsQuery($related_exercises);

            $related_exercises = $related_exercises->where('id', '!=', $exercise->id)
                ->where('is_active', $status->id ?? 0)
                ->whereNotIn('id', $other_teams_exercises)
                /*->where('video_file', '!=', NULL)
                ->where('completion_time', '>', 0)
                ->whereHas('player_scores_users')*/
                ->latest()
                ->limit(5)
                ->get();

            foreach ($related_exercises as $key => $exercise) {
                foreach ($exercise->tools as $value) {
                    $value->file_name = strtolower(str_replace("/", "_", $value->tool_name));
                }

                $related_exercises[$key]->badge = "ai";
            }

            /**
             * GET THE PERSONAL BEST OF THE CURRENT USER
             */
            $personalBest = $this->getPlayerScores($request)->get();
            $personalBest_Score = $personalBest->max("score");
            $personalBest_Time = $personalBest->max("created_at");

            if (count($personalBest) == 0) {
                $response['personal_best'] = new stdClass();
            } else {
                $response['personal_best'] = [
                    "score" => $personalBest_Score,
                    "time" => $personalBest_Time
                ];
            }


            $response['related_exercise'] = $related_exercises;

            unset($exercise->categories);

            return Helper::apiSuccessResponse(true, 'Records found successfully!', $response);
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', $response);
    }


    /**
     *
     * Get Previous Result
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": [
     * {
     * "date": "2021-06-21 10:07:00",
     * "score": "0/20",
     * "index": "First",
     * "value": "First"
     * },
     * {
     * "date": "2021-06-21 10:06:55",
     * "score": "0/20",
     * "index": "Same",
     * "value": "Same"
     * },
     * {
     * "date": "2021-06-10 19:29:46",
     * "score": "7/20",
     * "index": "inc",
     * "value": "+7"
     * },
     * {
     * "date": "2021-06-10 12:48:04",
     * "score": "4/20",
     * "index": "dec",
     * "value": "-4"
     * },
     * {
     * "date": "2021-06-10 12:42:47",
     * "score": "4/20",
     * "index": "Same",
     * "value": "Same"
     * }
     * ]
     * }
     *
     *
     * @queryParam exercise_id integer required
     * @return JsonResponse
     */
    public function getPreviousResults(Request $request)
    {
        $request->validate([
            "exercise_id" => "required|integer"
        ]);

        $exercise_total_score = Exercise::select("score")->whereId($request->exercise_id)->first();

        if (!$exercise_total_score) {
            return Helper::apiNotFoundResponse(false, "Exercise Not Found", new stdClass());
        }

        $exercise = $this->getPlayerScores($request)
            ->limit(5)
            ->get();

        if (count($exercise) === 0) {
            return Helper::apiNotFoundResponse(false, "Current Exercise Not Assigned To User", new stdClass());
        }

        $previousScores = $exercise->map(function ($exe, $index) use ($exercise, $exercise_total_score) {
            $obj = new stdClass();
            $obj->date = "";
            $obj->score = "";
            $obj->index = "";
            $obj->value = "";

            $obj->date = $exe->created_at;
            $obj->score = $exe->score . "/" . $exercise_total_score->score;

            if ($index == 0) {
                $obj->index = "First";
                $obj->value = "First";
            } else if ($obj->score == $exercise[$index - 1]->score) {
                $obj->index = "Same";
                $obj->value = "Same";
            } else {
                if ($obj->score > $exercise[$index - 1]->score) {
                    $obj->index = "inc";
                    $obj->value = "+" . $exercise[$index]->score;
                } else {
                    $obj->index = "dec";
                    $obj->value = "-" . $exercise[$index - 1]->score;
                }
            }

            return $obj;
        });

        return Helper::apiSuccessResponse(true, "Success", $previousScores);
    }

    /**
     * Get Assignments
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": [
     * {
     * "id": 54,
     * "title": "New Year Assignment",
     * "deadline": "2021-01-08 00:00:00",
     * "assigned_from": "Fami Sultana",
     * "assigned_from_profile_picture": "",
     * "assigned_to": "",
     * "assigned_date": "2021-01-06T14:58:03.000000Z",
     * "player_assignment_id": 2364,
     * "status": "pending"
     * },
     * {
     * "id": 48,
     * "title": "Christmas Assignment",
     * "deadline": "2020-12-28 00:00:00",
     * "assigned_from": "Fami Sultana",
     * "assigned_from_profile_picture": "",
     * "assigned_to": "",
     * "assigned_date": "2020-12-25T12:54:59.000000Z",
     * "player_assignment_id": 2136,
     * "status": "pending"
     * },
     * ]
     * }
     *
     * @urlParam user_id required
     *
     * @return JsonResponse
     */
    public function getAssignments(Request $request)
    {
        Validator::make($request->all(), [
            'user_id' => 'required'
        ])->validate();

        $team_name = Auth::user()->teams[0]->team_name ?? '';
        $_assignments = PlayerAssignment::select('id','status_id','assignment_id','player_user_id')->with(['assignment' => function($q) use ($request){
            $q->select('assignments.id', 'assignments.title', 'trainer_user_id', 'deadline', 'created_at');
            $q->with(['author' => function ($q) {
                $q->select(DB::raw("rtrim(CONCAT(IFNULL(first_name,''), ' ' ,IFNULL(last_name,''))) as assigned_from"), 'id', 'profile_picture');
            }]);
            $q->whereHas('players', function ($query) use ($request) {
                $query->where('users.id', $request->user_id);
            });
        }])->whereplayerUserId($request->user_id)
            ->orderBy('assignment_id','DESC')
            ->get();
        $assignments = $_assignments->map(function ($assignment) use ($team_name, $request) {
            $obj = new stdClass();
            $obj->id = $assignment->assignment_id;
            $obj->title = $assignment->assignment['title'];
            $obj->deadline = $assignment->assignment['deadline'];
            $obj->assigned_from = $assignment->assignment['author']->assigned_from ?? "";
            $obj->assigned_from_profile_picture = $assignment->assignment['author']->profile_picture ?? "";
            $obj->assigned_to = $team_name;
            $obj->assigned_date = $assignment->assignment['created_at'];
            $obj->player_assignment_id = $assignment->id;

            $players_ex_count = PlayerExercise::where('assignment_id', $assignment->assignment_id)
                ->where('status_id', 3)
                ->where('user_id', $request->user_id)
                ->count();

            $assignment_ex_count = AssignmentExercise::where('assignment_id', $assignment->assignment_id)->count();

            $status = 'pending';
            if ($players_ex_count >= $assignment_ex_count) {
                $status = 'completed';
                PlayerAssignment::where('assignment_id', $assignment->assignment_id)->where('player_user_id', $request->user_id)
                    ->update(['status_id' => 3]);
            }

            $obj->status = $status;
            return $obj;
        });

        if (count($assignments) > 0) {
            return Helper::apiSuccessResponse(true, __('messages.assignment.found'), $assignments);
        }

        return Helper::apiNotFoundResponse(false, __('messages.assignment.not_found'), []);
    }

    /**
     * Get Assignment Detail
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "id": 1,
     * "title": "Assignment 1",
     * "assigned_from": "trainertest last",
     * "assigned_from_profile_picture": null,
     * "deadline": "2020-07-16 13:07:26",
     * "assigned_to": "Team A",
     * "description": "Assignment 1Assignment 1Assignment 1Assignment 1Assignment 1",
     * "exercises": [
     * {
     * "id": 2,
     * "title": "bench press",
     * "image": null,
     * "video": null,
     * "skills": [],
     * "tools": [],
     * "levels": [
     * {
     * "id": 1,
     * "title": "level 1",
     * "pivot": {
     * "level_id": 2,
     * "exercise_id": 1
     * }
     * }
     * ]
     * },
     * {
     * "id": 1,
     * "title": "dumbell curl",
     * "image": null,
     * "video": null,
     * "skills": [
     * {
     * "id": 1,
     * "name": "Agility",
     * "pivot": {
     * "exercise_id": 1,
     * "skill_id": 1
     * }
     * }
     * ],
     * "tools": [
     * {
     * "id": 1,
     * "tool_name": "Tool 1",
     * "pivot": {
     * "exercise_id": 1,
     * "tool_id": 1
     * }
     * }
     * ],
     * "levels": [
     * {
     * "id": 1,
     * "title": "level 1",
     * "pivot": {
     * "level_id": 1,
     * "exercise_id": 1
     * }
     * }
     * ]
     * }
     * ]
     * }
     * }
     *
     * @urlParam assignment_id required
     * @urlParam user_id required
     *
     *
     * @return JsonResponse
     */
    public function getAssignmentDetail(Request $request)
    {
        Validator::make($request->all(), [
            'assignment_id' => 'required',
            'user_id' => 'required'
        ])->validate();

        $assignment = Assignment::join('player_assignments', 'player_assignments.assignment_id', '=', 'assignments.id')
            ->join('users as author', 'assignments.trainer_user_id', '=', 'author.id')
            ->join('users as player', 'player.id', '=', 'player_assignments.player_user_id')
            ->join('player_team', 'player_team.user_id', '=', 'player.id')
            ->join('teams', 'teams.id', '=', 'player_team.team_id')
            ->select('assignments.id', 'assignments.title', 'assignments.image',
                DB::raw("rtrim(CONCAT(IFNULL(author.first_name,''), ' ' ,IFNULL(author.last_name,''))) as assigned_from"),
                'author.profile_picture as assigned_from_profile_picture',
                'assignments.deadline', 'teams.team_name as assigned_to', 'assignments.description')
            ->where('player_assignments.player_user_id', $request->user_id)
            ->whereNull('assignments.deleted_at')
            ->where('assignments.id', $request->assignment_id)
            ->with(['skills:name'])
            ->first();

        if ($assignment) {

            $assign_exercises = Exercise::select('id', 'title', 'image', 'video')
                ->whereHas('assignments', function ($q1) use ($assignment) {
                    $q1->where('assignments.id', $assignment->id);
                });
                
                $this->exerciseBasicRelationsQuery($assign_exercises);

                /*->where('video_file', '!=', NULL)
                ->where('completion_time', '>', 0)
                ->whereHas('player_scores_users')*/
            $assign_exercises = $assign_exercises->latest()
                ->get();

            $status = Status::where('name', 'completed')->first();

            foreach ($assign_exercises as $ax) {

                $px = PlayerExercise::where('assignment_id', $request->assignment_id)
                    ->where('user_id', $request->user_id)
                    ->where('exercise_id', $ax->id)
                    ->where('status_id', $status->id)
                    ->first();

                if ($px) {
                    $ax->status = 'completed';
                } else {
                    $ax->status = 'pending';
                }
            }

            $assignment->exercises = $assign_exercises;
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $assignment);
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());
    }

    /**
     * Start Exercise
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Exercise started",
     * "Result": {
     * "user_id": 1,
     * "exercise_id": "1",
     * "level_id": "1",
     * "start_time": "2020-10-07T13:01:55.811422Z",
     * "status_id": 5,
     * "updated_at": "2020-10-07 13:01:55",
     * "created_at": "2020-10-07 13:01:55",
     * "id": 154,
     * "match_id": 1024,
     * "high_score": 100
     * }
     * }
     *
     * @bodyParam assignment_id string optional it is required only when you perform exercise from assignments
     * @bodyParam exercise_id string required
     * @bodyParam level_id string required
     *
     * @return JsonResponse
     */
    public function startExercise(Request $request)
    {
        Validator::make($request->all(), [
            'exercise_id' => 'required|exists:exercises,id',
            'level_id' => 'required|exists:levels,id'
        ],
            [
                'exercise_id.exists' => 'Exercise does not exists',
                'level_id.exists' => 'Level does not exists'
            ]
        )->validate();

        //getting auth token
        // $auth = HumanOx::partnerLogin();

        // if (gettype($auth) == 'integer') {
        //     return Helper::apiNotFoundResponse(false, 'Failed to get auth token', new stdClass());
        // }

        // $token = $auth->token;

        $status = Status::where('name', 'in-process')->first();

        $pl_ex = PlayerExercise::create([
            'assignment_id' => $request->assignment_id,
            'user_id' => Auth::user()->id,
            'exercise_id' => $request->exercise_id,
            'level_id' => $request->level_id,
            'start_time' => now(),
            'status_id' => $status->id ?? null
        ]);

        // $sensor = UserSensor::where('user_id', Auth::user()->id)->first();

        // if ($sensor) {
        //     $match = HumanOx::getMatch($sensor->imei, $token);

        //     if (count($match) > 0) {
        //         $check_match = Match::find($match[0]->match_id);
        //         if (!$check_match) {
        //             Match::create([
        //                 'id' => $match[0]->match_id,
        //                 'init_ts' => now(),
        //                 'exercise_id' => $request->exercise_id,
        //                 'level_id' => $request->level_id,
        //                 'user_id' => Auth::user()->id
        //             ]);
        //         }
        //     }
        // }

        $pl_ex->match_id = $match[0]->match_id ?? 0;

        $pl_ex->high_score = PlayerScore::where('exercise_id', $request->exercise_id)
            ->where('level_id', $request->level_id)
            ->max('score');

        return Helper::apiSuccessResponse(true, 'Exercise started', $pl_ex);
    }

    /**
     * End Exercise
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Exercise completed",
     * "Result": {
     * "id": 2,
     * "user_id": "1",
     * "exercise_id": "1",
     * "exercise_level_id": "1",
     * "score": null,
     * "video_file": "media/savedExercises/vfp9y5HSL9dPXSG4U0HfIVWZOHrFpeLvTxnPOLSk.txt",
     * "completion_time": "40",
     * "start_time": "2020-07-13 20:58:14",
     * "end_time": "2020-07-13T16:16:19.216528Z",
     * "created_at": null,
     * "updated_at": "2020-07-13 16:16:19",
     * "deleted_at": null
     * }
     * }
     *
     *
     * @bodyParam player_exercise_id string required you will get this id from start exercise response which is named by id
     * @bodyParam scores string required scores is a array of objects containing skill_id,score eg: [{skill_id:1,score:20},{skill_id:2,score:23}]
     * @bodyParam completion_time double required
     * @bodyParam thumbnail image required
     * @bodyParam video_file video required
     * @bodyParam version string optional
     *
     * @return JsonResponse
     */

    public function endExercise(Request $request)
    {
        ini_set('max_execution_time', '300');
        
        Validator::make($request->all(), [
            'player_exercise_id' => 'required|exists:player_exercise,id',
            // 'match_id' => 'required',
            'completion_time' => 'required',
            'scores.*.skill_id' => 'required|exists:skills,id',
            'scores.*.score' => 'required',
            'thumbnail' => 'required',
            'video_file' => 'required',
            'json_data' => 'required|file',
        ])->validate();

        $status = Status::where('name', 'completed')->first();

        $pl_ex = PlayerExercise::where('id', $request->player_exercise_id)
            ->where('user_id', Auth::user()->id)
            ->first();

        if (!$pl_ex) {
            return Helper::apiNotFoundResponse(false, 'Record not found', new stdClass());
        }

        //getting auth token
        // $auth = HumanOx::partnerLogin();

        // if (gettype($auth) == 'integer') {
        //     return Helper::apiNotFoundResponse(false, 'Failed to get auth token', new stdClass());
        // }

        // $token = $auth->token;

        // $sensor = UserSensor::where('user_id', Auth::user()->id)->first();
        // $match_id = null;

        // if ($sensor) {

        //     $match = HumanOx::getMatch($sensor->imei, $token);
        //     if ($match) {
        //         if (count($match) > 0) {
        //             $match_id = $match[0]->match_id;
        //         }
        //     }
        // }

        $res = DB::transaction(function () use ($request, $status) {

            $pl_ex = (new PlayerExercise())->updatePlayerExercise($request,Auth::user()->id);

            foreach ($request->scores as $key => $value) {

                (new PlayerScore())->createPlayerScore($pl_ex,Auth::user()->id,$value);
            }

            (new Post())->createPost($pl_ex,Auth::user()->id);


            return Helper::completeExercise($pl_ex);
        });


        // if ($sensor && $match_id != null) {

        //     $stats = HumanOx::getMatchStats($match_id, $sensor->imei, $token);

        //     $match = Match::find($match_id);
        //     if ($match) {
        //         $match->end_ts = now();
        //         $match->save();
        //     }

        //     if (gettype($stats) == 'array') {

        //         foreach ($stats as $key => $stat) {

        //             $stats_data[0]['match_id'] = $match_id;
        //             $stats_data[0]['stat_type_id'] = 1;
        //             $stats_data[0]['stat_value'] = $stat->distance;
        //             $stats_data[0]['player_id'] = $match->user_id;
        //             $stats_data[0]['imei'] = $sensor->imei;

        //             $stats_data[1]['match_id'] = $match_id;
        //             $stats_data[1]['stat_type_id'] = 15;
        //             $stats_data[1]['stat_value'] = $stat->steps;
        //             $stats_data[1]['player_id'] = $match->user_id;
        //             $stats_data[1]['imei'] = $sensor->imei;

        //             $stats_data[2]['match_id'] = $match_id;
        //             $stats_data[2]['stat_type_id'] = 4;
        //             $stats_data[2]['stat_value'] = $stat->walking;
        //             $stats_data[2]['player_id'] = $match->user_id;
        //             $stats_data[2]['imei'] = $sensor->imei;

        //             $stats_data[3]['match_id'] = $match_id;
        //             $stats_data[3]['stat_type_id'] = 17;
        //             $stats_data[3]['stat_value'] = $stat->running;
        //             $stats_data[3]['player_id'] = $match->user_id;
        //             $stats_data[3]['imei'] = $sensor->imei;

        //             $stats_data[4]['match_id'] = $match_id;
        //             $stats_data[4]['stat_type_id'] = 6;
        //             $stats_data[4]['stat_value'] = $stat->sprinting;
        //             $stats_data[4]['player_id'] = $match->user_id;
        //             $stats_data[4]['imei'] = $sensor->imei;

        //             $stats_data[5]['match_id'] = $match_id;
        //             $stats_data[5]['stat_type_id'] = 7;
        //             $stats_data[5]['stat_value'] = $stat->maxspeed;
        //             $stats_data[5]['player_id'] = $match->user_id;
        //             $stats_data[5]['imei'] = $sensor->imei;

        //             $stats_data[6]['match_id'] = $match_id;
        //             $stats_data[6]['stat_type_id'] = 2;
        //             $stats_data[6]['stat_value'] = $stat->avgspeed;
        //             $stats_data[6]['player_id'] = $match->user_id;
        //             $stats_data[6]['imei'] = $sensor->imei;

        //             $stats_data[7]['match_id'] = $match_id;
        //             $stats_data[7]['stat_type_id'] = 11;
        //             $stats_data[7]['stat_value'] = $stat->max_hr;
        //             $stats_data[7]['player_id'] = $match->user_id;
        //             $stats_data[7]['imei'] = $sensor->imei;

        //             $stats_data[8]['match_id'] = $match_id;
        //             $stats_data[8]['stat_type_id'] = 3;
        //             $stats_data[8]['stat_value'] = $stat->avg_hr;
        //             $stats_data[8]['player_id'] = $match->user_id;
        //             $stats_data[8]['imei'] = $sensor->imei;

        //             $stats_data[9]['match_id'] = $match_id;
        //             $stats_data[9]['stat_type_id'] = 14;
        //             $stats_data[9]['stat_value'] = $stat->impacts;
        //             $stats_data[9]['player_id'] = $match->user_id;
        //             $stats_data[9]['imei'] = $sensor->imei;

        //             $stats_data[10]['match_id'] = $match_id;
        //             $stats_data[10]['stat_type_id'] = 14;
        //             $stats_data[10]['stat_value'] = $stat->impacts;
        //             $stats_data[10]['player_id'] = $match->user_id;
        //             $stats_data[10]['imei'] = $sensor->imei;

        //         }

        //         MatchStat::insert($stats_data);
        //     }

        // }

        $res = $this->saveVideoFile($request,$pl_ex);

        if (!$res) {
            return Helper::apiNotFoundResponse(false, 'Failed to save data in post or player exercise', new stdClass());
        }

        if (!empty($video_file)) {
            $assignment = Assignment::with('author')
                ->where('id', $pl_ex->assignment_id)
                ->first();

            $response = $this->notificationData($assignment);
            $data = $response['data'];
            $devices = $response['devices'];
            $this->sendNotificationsOnDevices($devices,$data);

            $ai_json = '';
            try {
                if ($request->hasFile('json_data')) {
                    $file = $request->file('json_data');
                    $ai_json = time() . $file->getClientOriginalName() . ".json";
                    $fileData = json_decode(file_get_contents($file), true);
                    Storage::put($ai_json, json_encode($fileData));

                    $pl_ex = PlayerExercise::where('id', $request->player_exercise_id)
                        ->where('user_id', Auth::user()->id)
                        ->first();

                    $pl_ex->ai_json = $ai_json;
                    $pl_ex->save();

                    $data = [
                        'user_id' => Auth::user()->id,
                        'exercise_id' => $request->player_exercise_id,
                        'file_name' => $ai_json,
                    ];

                    $endpoint = Helper::settings('alki', 'URL');
                    echo $endpoint;
                    $client = new \GuzzleHttp\Client();
                    $response = $client->post($endpoint, [
                        'headers' => ['Content-Type' => 'application/json'],
                        'body' => json_encode($data)
                    ]);
                    $content = json_decode($response->getBody(), true);

                    $res = DB::transaction(function () use ($request, $content, $ai_json) {

                        $pl_ex = PlayerExercise::where('id', $request->player_exercise_id)
                            ->where('user_id', Auth::user()->id)
                            ->first();

                        $pl_ex->ai_json = $ai_json;
                        $pl_ex->kpi_json = serialize($content['KPIs']);
                        $pl_ex->html_file_name = $content['file_name'];
                        $pl_ex->version = $request->version;
                        $pl_ex->save();

                        return $pl_ex;
                    });
                }

            } catch (\Exception $exception) {
                $error = new ExerciseKpiResponseError;
                $error->player_exercise_id = $request->player_exercise_id;
                $error->error = $exception->getMessage();
                $error->json_file = $ai_json;
                $error->save();
            }
        }

        return Helper::apiSuccessResponse(true, 'Exercise completed', $res);
    }

    /**
     * Perform exercise
     *
     * @response[
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Exercise performed successfully",
     * "Result": {}
     * }
     * ]
     *
     * @response 422{
     * "Response": false,
     * "StatusCode": 422,
     * "Message": "Invalid Parameters",
     * "Result": {
     * "assignment_id": [
     * "The assignment id  must be a number."
     * ]
     * }
     * }
     *
     * @response 404[
     * {
     * "Response": false,
     * "StatusCode": 404,
     * "Message": "Exercise not found",
     * "Result": {}
     * }
     * ]
     *
     * @response 500[
     * {
     * "Response": false,
     * "StatusCode": 500,
     * "Message": "Something wen't wrong",
     * "Result": {}
     * }
     * ]
     *
     * @bodyParam assignment_id string optional it is required only when you perform exercise from assignments
     * @bodyParam exercise_id string required
     * @bodyParam level_id string required
     * @bodyParam scores string required scores is a array of objects containing skill_id, score eg: [{skill_id:1, score:20}]
     * @bodyParam started_time double required
     * @bodyParam end_time double required
     * @bodyParam completion_time double required
     * @bodyParam thumbnail image required
     * @bodyParam video_file video required
     * @bodyParam version string optional
     * @bodyParam json_data video required
     */

    public function performExercise(Request $request)
    {
        $request->validate([
            'assignment_id' => 'nullable|numeric|exists:assignments,id',
            'exercise_id' => 'required|numeric|exists:exercises,id',
            'level_id' => 'required|numeric|exists:levels,id',
            'scores' => 'required|array',
            'scores.*.skill_id' => 'required|numeric|exists:skills,id',
            'scores.*.score' => 'required|numeric',
            'started_time' => 'required|date|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date|date_format:Y-m-d H:i:s|after:started_time',
            'completion_time' => 'required|numeric',
            'thumbnail' => 'required|file',
            'video_file' => 'required|file',
            'json_data' => 'required|file',
            'version' => 'required|numeric'
        ]);

        DB::beginTransaction();

        try {
            $completedStatus = Status::select('id')
                ->where('name', 'completed')
                ->first();

            $playerExercise = new PlayerExercise();

            $playerExercise->assignment_id = $request->assignment_id;
            $playerExercise->user_id = auth()->user()->id;
            $playerExercise->exercise_id = $request->exercise_id;
            $playerExercise->level_id = $request->level_id;
            $playerExercise->start_time = $request->started_time;
            $playerExercise->end_time = $request->end_time;
            $playerExercise->completion_time = $request->completion_time;
            $playerExercise->status_id = $completedStatus->id ?? NULL;
            $playerExercise->save();

            foreach ($request->scores as $key => $value) {
                $playerExerciseScore = new PlayerScore();

                $playerExerciseScore->user_id = auth()->user()->id;
                $playerExerciseScore->exercise_id = $request->exercise_id;
                $playerExerciseScore->level_id = $request->level_id;
                $playerExerciseScore->skill_id = $value['skill_id'];
                $playerExerciseScore->score = $value['score'];
                $playerExerciseScore->save();
            }

            $exercise = Exercise::select('id', 'title')
                ->where('id', $request->exercise_id)
                ->first();

            $notSharedStatus = Status::select('id')
                ->where('name', 'not-shared')
                ->first();

            $totalPlayerExercises = PlayerExercise::where('assignment_id', $request->assignment_id)
                ->where('user_id', auth()->user()->id)
                ->where('status_id', 3)
                ->distinct('assignment_id', 'user_id', 'exercise_id')
                ->count();

            $totalAssignmentExercises = AssignmentExercise::where('assignment_id', $request->assignment_id)
                ->count();

            if ($totalPlayerExercises >= $totalAssignmentExercises) {
                $playerAssignment = PlayerAssignment::select('id')
                    ->where('assignment_id', $request->assignment_id)
                    ->where('player_user_id', auth()->user()->id)
                    ->first();

                if ($playerAssignment) {
                    $playerAssignment->status_id = $completedStatus->id ?? NULL;
                    $playerAssignment->save();
                }
            }

            $video_file = '';

            $thumbnail = '';

            if ($request->hasFile('video_file')) {
                $video_file = Storage::putFile(PlayerExercise::$media, $request->video_file);
            }

            if ($request->hasFile('thumbnail')) {
                $thumbnail = Storage::putFile(PlayerExercise::$media, $request->thumbnail);
            }

            if (empty($video_file) || empty($thumbnail)) {
                return Helper::apiNotFoundResponse(false, 'Failed to upload video or thumbnail', new stdClass());
            }

            $playerExercise->thumbnail = $thumbnail;
            $playerExercise->video_file = $video_file;
            $playerExercise->save();

            $post = new Post();

            $post->player_exercise_id = $playerExercise->id;
            $post->author_id = auth()->user()->id;
            $post->exercise_id = $request->exercise_id;
            $post->level_id = $request->level_id;
            $post->post_title = $exercise->title;
            $post->thumbnail = $thumbnail;
            $post->post_attachment = $video_file;
            $post->status_id = $notSharedStatus->id ?? NULL;
            $post->save();

            $assignment = Assignment::with('author')
                ->where('id', $request->assignment_id)
                ->first();

            $response = $this->notificationData($assignment);
            $data = $response['data'];
            $devices = $response['devices'];

            $tokens = [];

            foreach ($devices as $key => $value) {
                if ($value->device_token) {
                    array_push($tokens, $value->device_token);
                }
            }

            if (count($tokens) > 0) {
                foreach ($devices as $device) {
                    Helper::sendNotification($data, $device->onesignal_token, $device->device_type);
                }

                auth()->user()->badge_count = $data['badge_count'];
                auth()->user()->save();
            }

            $ai_json = '';

            try {
                $ai_json = time() . $request->file('json_data')->getClientOriginalName() . '.json';
                $fileData = json_decode(file_get_contents($request->file('json_data')), true);

                Storage::put($ai_json, json_encode($fileData));

                $data = [
                    'user_id' => auth()->user()->id,
                    'exercise_id' => $playerExercise->id,
                    'file_name' => $ai_json,
                ];

                $endpoint = Helper::settings('alki', 'URL');

                $client = Client();
                $response = $client->post($endpoint, [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($data)
                ]);
                $content = json_decode($response->getBody(), true);

                $playerExercise->ai_json = $ai_json;
                $playerExercise->kpi_json = serialize($content['KPIs']);
                $playerExercise->html_file_name = $content['file_name'];
                $playerExercise->version = $request->version;
                $playerExercise->save();

            } catch (Exception $ex) {
                $error = new ExerciseKpiResponseError;

                $error->player_exercise_id = $playerExercise->id;
                $error->error = $ex->getMessage();
                $error->json_file = $ai_json;
                $error->save();
            }

            DB::commit();

            $response = Helper::apiSuccessResponse(true, 'Exercise performed successfully', new stdClass());
        } catch (Exception $ex) {
            DB::rollBack();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
        }

        return $response;
    }


//    public function endExercise(Request $request){
//        try{
//            $file =  $request->file('json_data');
//            $ai_json = time() . $file->getClientOriginalName();
//            $fileData = json_decode(file_get_contents($file), true);
//            $file_transfer = Storage::put($ai_json, json_encode($fileData));
//            $data = [
//                'user_id' => Auth::user()->id,
//                'exercise_id' => '99',
//                'file_name' => $ai_json,
//            ];
//
//            $endpoint = "https://x69fys06hk.execute-api.eu-west-2.amazonaws.com/prod/exercise_datapoints/";
//            $client = new \GuzzleHttp\Client();
//            $response = $client->post( $endpoint, [
//                'headers' => ['Content-Type' => 'application/json'],
//                'body' => json_encode($data)
//            ]);
//            $content = json_decode($response->getBody(), true);
//
//            return $content;
//        }catch (\Exception $exception){
//            return Helper::apiNotFoundResponse(false, $exception->getMessage(), new stdClass());
//        }
//    }
    /**
     * Get User Exercise Detail
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Record found",
     * "Result": {
     * "id": 215,
     * "title": "Jumping jacks",
     * "video_file": "media/player_exercises/UeqFXtrG0fsNkFktDrVN1u2mwwBQqFloJ8qkzKDg.mp4",
     * "completion_time": 15.31252587890625,
     * "start_time": null,
     * "end_time": "2021-06-10 16:10:59",
     * "status_id": 3,
     * "unit": null,
     * "score": 0,
     * "leaderboard": [
     * {
     * "id": 27,
     * "first_name": "Hassan",
     * "last_name": "Shah",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 27,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 50,
     * "pivot": {
     * "user_id": 27,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 50,
     * "created_at": "2020-12-09 15:02:04",
     * "updated_at": "2020-12-09 15:02:04"
     * }
     * }
     * ]
     * },
     * {
     * "id": 73,
     * "first_name": "Thomas",
     * "last_name": "Andre de la Porte",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 73,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 50,
     * "pivot": {
     * "user_id": 73,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 50,
     * "created_at": "2020-11-16 13:15:49",
     * "updated_at": "2020-11-16 13:15:49"
     * }
     * }
     * ]
     * },
     * {
     * "id": 159,
     * "first_name": "first name",
     * "last_name": "last name",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 11,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 159,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 50,
     * "pivot": {
     * "user_id": 159,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 50,
     * "created_at": "2020-12-13 16:47:49",
     * "updated_at": "2020-12-13 16:47:49"
     * }
     * }
     * ]
     * },
     * {
     * "id": 2,
     * "first_name": "Fatima",
     * "last_name": "Sultana",
     * "middle_name": null,
     * "profile_picture": "media/users/60a3d1946b6701621348756.jpeg",
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 2,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 2,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-07 10:55:56",
     * "updated_at": "2021-05-07 10:55:56"
     * }
     * }
     * ]
     * },
     * {
     * "id": 122,
     * "first_name": "Sohail",
     * "last_name": "Zia",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 122,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 122,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-21 12:50:40",
     * "updated_at": "2021-05-21 12:50:40"
     * }
     * }
     * ]
     * },
     * {
     * "id": 461,
     * "first_name": "muhammad",
     * "last_name": "fahad",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 461,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 461,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-07 16:41:08",
     * "updated_at": "2021-05-07 16:41:08"
     * }
     * }
     * ]
     * },
     * {
     * "id": 477,
     * "first_name": "Ali",
     * "last_name": "Ahmed",
     * "middle_name": "''",
     * "profile_picture": "media/users/608fef8c625061620045708.jpeg",
     * "completion_time": 71,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 477,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 477,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-07 13:11:44",
     * "updated_at": "2021-05-07 13:11:44"
     * }
     * }
     * ]
     * },
     * {
     * "id": 16,
     * "first_name": "Ali",
     * "last_name": "Mehdi",
     * "middle_name": "''",
     * "profile_picture": "media/users/609543696d2ac1620394857.jpeg",
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 16,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 15,
     * "pivot": {
     * "user_id": 16,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 15,
     * "created_at": "2020-11-09 13:25:31",
     * "updated_at": "2020-11-09 13:25:31"
     * }
     * }
     * ]
     * },
     * {
     * "id": 3,
     * "first_name": "Hasnain",
     * "last_name": "Ali",
     * "middle_name": null,
     * "profile_picture": "media/users/609bdad748e321620826839.jpeg",
     * "completion_time": 15,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 3,
     * "level_id": null
     * },
     * "player_scores_skills": [
     * {
     * "score": 6,
     * "pivot": {
     * "user_id": 3,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": null,
     * "score": 6,
     * "created_at": "2021-06-10 16:10:59",
     * "updated_at": "2021-06-10 16:10:59"
     * }
     * }
     * ]
     * },
     * {
     * "id": 4,
     * "first_name": "Tariq",
     * "last_name": "Sidd",
     * "middle_name": null,
     * "profile_picture": "media/users/5f7b294249cca1601907010.jpeg",
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 4,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 5,
     * "pivot": {
     * "user_id": 4,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": null,
     * "score": 5,
     * "created_at": "2021-06-10 16:11:44",
     * "updated_at": "2021-06-10 16:11:44"
     * }
     * }
     * ]
     * }
     * ],
     * "current_player_index": 4,
     * "full_list_leaderboard": [
     * {
     * "id": 73,
     * "first_name": "Thomas",
     * "last_name": "Andre de la Porte",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 73,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 50,
     * "pivot": {
     * "user_id": 73,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 50,
     * "created_at": "2020-11-16 13:15:49",
     * "updated_at": "2020-11-16 13:15:49"
     * }
     * }
     * ]
     * },
     * {
     * "id": 159,
     * "first_name": "first name",
     * "last_name": "last name",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 11,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 159,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 50,
     * "pivot": {
     * "user_id": 159,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 50,
     * "created_at": "2020-12-13 16:47:49",
     * "updated_at": "2020-12-13 16:47:49"
     * }
     * }
     * ]
     * },
     * {
     * "id": 2,
     * "first_name": "Fatima",
     * "last_name": "Sultana",
     * "middle_name": null,
     * "profile_picture": "media/users/60a3d1946b6701621348756.jpeg",
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 2,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 2,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-07 10:55:56",
     * "updated_at": "2021-05-07 10:55:56"
     * }
     * }
     * ]
     * },
     * {
     * "id": 122,
     * "first_name": "Sohail",
     * "last_name": "Zia",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 122,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 122,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-21 12:50:40",
     * "updated_at": "2021-05-21 12:50:40"
     * }
     * }
     * ]
     * },
     * {
     * "id": 461,
     * "first_name": "muhammad",
     * "last_name": "fahad",
     * "middle_name": "''",
     * "profile_picture": null,
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 461,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 461,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-07 16:41:08",
     * "updated_at": "2021-05-07 16:41:08"
     * }
     * }
     * ]
     * },
     * {
     * "id": 477,
     * "first_name": "Ali",
     * "last_name": "Ahmed",
     * "middle_name": "''",
     * "profile_picture": "media/users/608fef8c625061620045708.jpeg",
     * "completion_time": 71,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 477,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 20,
     * "pivot": {
     * "user_id": 477,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 20,
     * "created_at": "2021-05-07 13:11:44",
     * "updated_at": "2021-05-07 13:11:44"
     * }
     * }
     * ]
     * },
     * {
     * "id": 16,
     * "first_name": "Ali",
     * "last_name": "Mehdi",
     * "middle_name": "''",
     * "profile_picture": "media/users/609543696d2ac1620394857.jpeg",
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 16,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 15,
     * "pivot": {
     * "user_id": 16,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": 1,
     * "score": 15,
     * "created_at": "2020-11-09 13:25:31",
     * "updated_at": "2020-11-09 13:25:31"
     * }
     * }
     * ]
     * },
     * {
     * "id": 3,
     * "first_name": "Hasnain",
     * "last_name": "Ali",
     * "middle_name": null,
     * "profile_picture": "media/users/609bdad748e321620826839.jpeg",
     * "completion_time": 15,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 3,
     * "level_id": null
     * },
     * "player_scores_skills": [
     * {
     * "score": 6,
     * "pivot": {
     * "user_id": 3,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": null,
     * "score": 6,
     * "created_at": "2021-06-10 16:10:59",
     * "updated_at": "2021-06-10 16:10:59"
     * }
     * }
     * ]
     * },
     * {
     * "id": 4,
     * "first_name": "Tariq",
     * "last_name": "Sidd",
     * "middle_name": null,
     * "profile_picture": "media/users/5f7b294249cca1601907010.jpeg",
     * "completion_time": 0,
     * "pivot": {
     * "exercise_id": 215,
     * "user_id": 4,
     * "level_id": 1
     * },
     * "player_scores_skills": [
     * {
     * "score": 5,
     * "pivot": {
     * "user_id": 4,
     * "skill_id": 1,
     * "exercise_id": 215,
     * "level_id": null,
     * "score": 5,
     * "created_at": "2021-06-10 16:11:44",
     * "updated_at": "2021-06-10 16:11:44"
     * }
     * }
     * ]
     * }
     * ],
     * "previous_scores": [
     * {
     * "date": "2021-05-07T10:55:56.000000Z",
     * "score": 20,
     * "index": "First",
     * "value": "First"
     * },
     * {
     * "date": "2021-05-07T10:58:06.000000Z",
     * "score": 3,
     * "index": "dec",
     * "value": "-3"
     * },
     * {
     * "date": "2021-05-07T11:04:05.000000Z",
     * "score": 10,
     * "index": "inc",
     * "value": "+10"
     * }
     * ],
     * "achievements": [
     * {
     * "image": null,
     * "title": "Achievement 1",
     * "description": "Finish in 90 seconds"
     * },
     * {
     * "image": null,
     * "title": "Achievement 2",
     * "description": "Finish in 60 seconds"
     * }
     * ],
     * "levels": [
     * {
     * "id": 1,
     * "title": "Level 1",
     * "image": "media/tools/level_1.png",
     * "measure": "21",
     * "status": "0",
     * "pivot": {
     * "exercise_id": 215,
     * "level_id": 1,
     * "measure": "21"
     * }
     * }
     * ]
     * }
     * }
     *
     * @urlParam exercise_id required
     *
     * @return JsonResponse
     */

    public function getUserExerciseDetail(Request $request)
    {
        Validator::make($request->all(), [
            'exercise_id' => 'required'
        ])->validate();
        $ex = $request->exercise_id;
        $flag = false;
        $exe = Exercise::find($request->exercise_id);
        $detail = $this->exericseModel->getUserExerciseDetail($request,$exe,$ex);
        if (!$detail) {
            return Helper::apiNotFoundResponse(false, 'Record not found', new stdClass());
        }

        $temp = json_decode($detail->leaderboard);
        unset($detail->leaderboard);
        $detail->leaderboard = self::sortLeaderboard($temp);

        $player_score = PlayerScore::select('id', 'user_id', 'exercise_id', 'score')
            ->where('user_id', Auth::user()->id)
            ->where('exercise_id', $request->exercise_id)
            ->latest('updated_at')
            ->first();

        if (!$player_score) {
            $detail->score = 0;
        } else {
            $detail->score = $player_score->score;
        }

        $index_player = null;

        if (isset($detail->leaderboard)) {
            for ($i = 0; $i < count($detail->leaderboard); $i++) {
                if (Auth::user()->id == $detail->leaderboard[$i]->id) {
                    $flag = true;
                }
                if ($i == 9) {
                    break;
                }

            }

            for ($i = 0; $i < count($detail->leaderboard); $i++) {
                if (Auth::user()->id == $detail->leaderboard[$i]->id) {
                    $index_player = $i + 1;
                }
            }

            $arr = [];
            $arr1 = [];
            for ($i = 0; $i < count($detail->leaderboard);) {
                if ($i == 10) {
                    break;
                }
                if (isset($detail->leaderboard[$i]->player_scores_skills[0]->score)) {
                    $arr[$i] = $detail->leaderboard[$i];
                    $i++;
                }


            }
            for ($i = 1; $i < count($detail->leaderboard); $i++) {
                if (isset($detail->leaderboard[1]->player_scores_skills[0]->score)) {
                    $arr1[$i] = $detail->leaderboard[$i];
                }

            }

            if (!$flag) {

                $obj = $this->exericseModel->getUserExerciseDetail($request,$exe,$ex);
                if (isset($obj->leaderboard[0])) {
                    $data = $obj->leaderboard[0];
                    $arr = array_merge($arr, [$data]);
                }
            }
            $list = $arr1;
            $list = self::sortLeaderboard($list);
            unset($detail->leaderboard);
            $detail->leaderboard = $arr;
        }
        // GET PREVIOUS SCORES OF THE PLAYER
        $previousScores = PlayerScore::select("score", "created_at")
            ->whereExerciseIdAndUserId($detail->id, \auth()->user()->id)
            ->orderBy("created_at", "ASC")->limit(3)->get();
        $previousScores = $previousScores->map(function ($exe, $index) use ($previousScores) {
            $obj = new stdClass();
            $obj->date = "";
            $obj->score = "";
            $obj->index = "";
            $obj->value = "";

            $obj->date = $exe->created_at;
            $obj->score = $exe->score;

            if ($index == 0) {
                $obj->index = "First";
                $obj->value = "First";
            } else if ($obj->score == $previousScores[$index - 1]->score) {
                $obj->index = "Same";
                $obj->value = "Same";
            } else {
                if ($obj->score > $previousScores[$index - 1]->score) {
                    $obj->index = "inc";
                    $obj->value = "+" . $previousScores[$index]->score;
                } else {
                    $obj->index = "dec";
                    $obj->value = "-" . $previousScores[$index]->score;
                }
            }

            return $obj;
        });
        $temp = $detail->leaderboard;
        $detail->leaderboard = self::sortLeaderboard($temp);

        $detail['current_player_index'] = $index_player;
        $detail['full_list_leaderboard'] = $list;
        $detail['previous_scores'] = $previousScores;


        $ach_1 = new stdClass();
        $ach_1->image = null;
        $ach_1->title = 'Achievement 1';
        $ach_1->description = 'Finish in 90 seconds';

        $ach_2 = new stdClass();
        $ach_2->image = null;
        $ach_2->title = 'Achievement 2';
        $ach_2->description = 'Finish in 60 seconds';

        $detail->achievements = [$ach_1, $ach_2];


        return Helper::apiSuccessResponse(true, 'Record found', $detail);
    }

    /**
     *
     * Complete Assignment
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Assignment completed",
     * "Result": {}
     * }
     *
     * @bodyParam assignment_id string required
     *
     * @return JsonResponse
     */
    public function completeAssignment(Request $request)
    {
        Validator::make($request->all(), [
            'assignment_id' => 'required'
        ])->validate();

        $assignment = PlayerAssignment::where('player_user_id', Auth::user()->id)->where('assignment_id', $request->assignment_id)->first();

        if (!$assignment) {
            return Helper::apiNotFoundResponse(false, 'Assignment not found', new stdClass());
        }

        $status = Status::where('name', 'completed')->first();
        $assignment->status_id = $status->id ?? null;
        $assignment->save();

        $_assignment = Assignment::find($request->assignment_id);

        $data['from_user_id'] = Auth::user()->id;
        $data['to_user_id'] = $_assignment->trainer_user_id;
        $data['model_type'] = 'assignment/completed';
        $data['model_type_id'] = $_assignment->id;
        $data['click_action'] = '';
        $data['message']['en'] = Auth::user()->first_name . ' ' . Auth::user()->last_name . ' has completed assignment: ' . $_assignment->title;
        $data['message']['nl'] = Auth::user()->first_name . ' ' . Auth::user()->last_name . ' heeft opdracht afgerond: ' . $_assignment->title;
        $data['message'] = json_encode($data['message']);
        $data['badge_count'] = Auth::user()->badge_count + 1;

        $devices = $_assignment->author->user_devices;

        $this->sendNotificationsOnDevices($devices,$data);

        return Helper::apiSuccessResponse(true, 'Assignment completed', new stdClass());
    }

    /**
     * Share Post
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Post has been shared",
     * "data": [
     * {
     * "id": 109,
     * "author_id": 1,
     * "exercise_id": 207,
     * "level_id": 1,
     * "post_title": " Low power shot (L)",
     * "post_desc": "test1",
     * "thumbnail": "media/player_exercises/NmDe8rtDeK1aC7je3eIafMTb3HP16Zkvzlss2zds.jpeg",
     * "post_attachment": "media/player_exercises/8YTaMMIJT5qQpyApihskfQ8D3K4eOJhpl7FF01Bx.mp4",
     * "status_id": 7,
     * "created_at": "2020-11-03T16:48:17.000000Z",
     * "updated_at": "2020-11-03T16:48:40.000000Z",
     * "deleted_at": null,
     * "author": {
     * "id": 1,
     * "first_name": "muhammad.",
     * "last_name": "shahzaib",
     * "profile_picture": "media/users/5f996dc5898911603890629.jpeg"
     * },
     * "comments": 0,
     * "likes": 0,
     * "user_likes_count": 0,
     * "i_liked": false,
     * "user_privacy_settings": 1
     * },
     * {
     * "id": 83,
     * "author_id": 11,
     * "exercise_id": 89,
     * "level_id": 1,
     * "post_title": "Out & in (L/R)",
     * "post_desc": "look at me now",
     * "thumbnail": "media/player_exercises/k1Se5Mna0pHeCcyxzBfYelIkm3mjVnzf8xjvfjYf.jpeg",
     * "post_attachment": "media/player_exercises/ZqMaSJE3MkysQ2qEQjspkhOJIalnr7VZVZmvIt2O.mp4",
     * "status_id": 7,
     * "created_at": "2020-10-31T16:07:59.000000Z",
     * "updated_at": "2020-10-31T16:08:19.000000Z",
     * "deleted_at": null,
     * "author": {
     * "id": 11,
     * "first_name": "Saad",
     * "last_name": "Saleem",
     * "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg"
     * },
     * "comments": 0,
     * "likes": 0,
     * "user_likes_count": 0,
     * "i_liked": false
     * },
     * {
     * "id": 50,
     * "author_id": 2,
     * "exercise_id": 213,
     * "level_id": 1,
     * "post_title": "Singe leg deadlifts",
     * "post_desc": "testing iOS thumbnail",
     * "thumbnail": "media/player_exercises/5DyqIWsGYKdR5NxdHL49nxqsD5mlWKLmXaDptBRm.jpeg",
     * "post_attachment": "media/player_exercises/HZV9SpjuKa8IAVGoQHi3TAI22WSgZhs4KXiyCU0X.mp4",
     * "status_id": 7,
     * "created_at": "2020-10-29T12:41:17.000000Z",
     * "updated_at": "2020-10-29T12:41:27.000000Z",
     * "deleted_at": null,
     * "author": {
     * "id": 2,
     * "first_name": "Fatima",
     * "last_name": "Sultana",
     * "profile_picture": "media/users/5f8d8640225f41603110464.jpeg"
     * },
     * "comments": 0,
     * "likes": 14,
     * "user_likes_count": 0,
     * "i_liked": false
     * },
     * {
     * "id": 22,
     * "author_id": 11,
     * "exercise_id": 3,
     * "level_id": 1,
     * "post_title": "10 Cones dribble (L)",
     * "post_desc": "ok",
     * "thumbnail": "media/player_exercises/JKgXd3ZX1MXSbaFwOnURPO1SQARJJC29T4u14GNO.jpeg",
     * "post_attachment": "media/player_exercises/QhLYrCC75iOlulIYnL31abjkUNz1nRgH4CmTRBhZ.mp4",
     * "status_id": 7,
     * "created_at": "2020-10-28T20:36:41.000000Z",
     * "updated_at": "2020-10-28T20:36:47.000000Z",
     * "deleted_at": null,
     * "author": {
     * "id": 11,
     * "first_name": "Saad",
     * "last_name": "Saleem",
     * "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg"
     * },
     * "comments": 0,
     * "likes": 29,
     * "user_likes_count": 0,
     * "i_liked": false
     * },
     * {
     * "id": 16,
     * "author_id": 2,
     * "exercise_id": 68,
     * "level_id": 1,
     * "post_title": "Laces push-pull (L)",
     * "post_desc": "release apk test",
     * "thumbnail": "media/player_exercises/HAsFokAR8ZHGKxgE4am9CsBq7YgOb4zJdNkTxuIp.jpeg",
     * "post_attachment": "media/player_exercises/NFNoDWGKBrfDP9CZkfrDufvAqvBRP5t6ZXLcOfGc.mp4",
     * "status_id": 7,
     * "created_at": "2020-10-28T19:28:34.000000Z",
     * "updated_at": "2020-10-28T19:28:49.000000Z",
     * "deleted_at": null,
     * "author": {
     * "id": 2,
     * "first_name": "Fatima",
     * "last_name": "Sultana",
     * "profile_picture": "media/users/5f8d8640225f41603110464.jpeg"
     * },
     * "comments": 2,
     * "likes": 22,
     * "user_likes_count": 0,
     * "i_liked": false
     * }
     * ],
     * "meta": {
     * "current_page": 1,
     * "first_page_url": "http://localhost/jogo/api/v1/app/home/feeds?page=1",
     * "from": 1,
     * "last_page": 2,
     * "last_page_url": "http://localhost/jogo/api/v1/app/home/feeds?page=2",
     * "next_page_url": "http://localhost/jogo/api/v1/app/home/feeds?page=2",
     * "per_page": 5,
     * "prev_page_url": null,
     * "total": 8
     * }
     * }
     * }
     * }
     *
     * @bodyParam player_exercise_id string required you will get this id from start exercise response which is named by id
     * @bodyParam description string required
     *
     * @return JsonResponse
     */
    public function sharePost(Request $request)
    {
        Validator::make($request->all(), [
//            'exercise_id' => 'required',
//            'level_id' => 'required',
            'player_exercise_id' => 'required|exists:player_exercise,id',
            'description' => 'required'
        ])->validate();

//        $pl_exr = PlayerExercise::where('user_id', Auth::user()->id)
//            ->where('exercise_id', $request->exercise_id)
//            ->where('level_id', $request->level_id)
//            ->first();

        $pl_exr = PlayerExercise::where('user_id', Auth::user()->id)
            ->where('id', $request->player_exercise_id)
            ->first();

        if (!$pl_exr) {
            return Helper::apiNotFoundResponse(false, 'Exercise not found', new stdClass());
        }

//        $exercise = Exercise::find($request->exercise_id);

        $status = Status::where('name', 'shared')->first();
//        Post::updateOrCreate([
//            'author_id' => Auth::user()->id,
//            'exercise_id' => $exercise->id,
//            'level_id' => $request->level_id,
//        ],
//            [
//                'post_title' => $exercise->title,
//                'post_attachment' => $pl_exr->video_file,
//                'status_id' => $status->id ?? 0,
//                'post_desc' => $request->description
//            ]
//        );

        $endpoint = "http://18.132.132.4:8000/checkcontent";
        $client = new \GuzzleHttp\Client();
        $video_link = "https://" . env('AWS_BUCKET') . ".s3.eu-west-2.amazonaws.com/" . $pl_exr->video_file;
        $response = $client->post($endpoint, ['form_params' => ['file' => $video_link]]);
//        $statusCode = $response->getStatusCode();
        $abc = json_decode($response->getBody(), true);
        $content = json_decode($abc);
        $pl_exr->explicit_response = serialize($content);
        $pl_exr->save();

        if (empty($content)) {
            return Helper::apiNotFoundResponse(false, 'Exercise video not found', new stdClass());
        }
        if ($content->explicit_content == "yes") {
            $post = Post::wherePlayerExerciseId($request->player_exercise_id)->first();
            $data['from_user_id'] = Auth::user()->id;
            $data['to_user_id'] = $post->author_id;
            $data['model_type'] = 'posts/player_exercise';
            $data['model_type_id'] = $post->id;
            $data['click_action'] = 'PlayerExercise';
            $data['message']['en'] = 'Exercise video contains explicit content';
            $data['message']['nl'] = 'Oefenvideo bevat expliciete inhoud';
            $data['message'] = json_encode($data['message']);


            $devices = $post->author->user_devices;
            $tokens = [];

            foreach ($devices as $device) {
                if ($device->device_token) {
                    array_push($tokens, $device->device_token);
                }
            }

            if (Auth::user()->id != $post->author_id) {
                $data['badge_count'] = $post->author->badge_count + 1;
                Helper::sendNotification($data, $tokens);
                User::where('id', $post->author_id)->update([
                    'badge_count' => $data['badge_count']
                ]);
            }
            return Helper::apiNotFoundResponse(false, 'Exercise video contains explicit content', new stdClass());
        }

        Post::wherePlayerExerciseId($request->player_exercise_id)->update([
            'post_attachment' => $pl_exr->video_file,
            'status_id' => $status->id ?? 0,
            'post_desc' => $request->description
        ]);

        $ex = Helper::postQuery();
        $ex = $ex->wherePlayerExerciseId($request->player_exercise_id)->first();

        return Helper::apiSuccessResponse(true, 'Post has been shared', Helper::getPostObject($ex));

//        return Helper::apiSuccessResponse(true, 'Post has been shared', new stdClass());
    }

    /**
     * Upload Exercise Video
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Exercise video uploaded",
     * "Result": {}
     * }
     *
     * @bodyParam player_exercise_id string required you will get this id from start exercise/end exercise response which is named by id
     * @bodyParam thumbnail string required
     * @bodyParam video_file string required
     *
     * @return JsonResponse
     */
//    public function uploadVideo(Request $request)
//    {
//        Validator::make($request->all(), [
//            'player_exercise_id' => 'required|exists:player_exercise,id',
//            'thumbnail' => 'required',
//            'video_file' => 'required',
//        ],
//            [
//                'exercise_id.exists' => 'Exercise does not exists',
//                'level_id.exists' => 'Level does not exists'
//            ]
//        )->validate();
//
//        $pl_ex = PlayerExercise::where('id', $request->player_exercise_id)->where('user_id', Auth::user()->id)->first();
//
//        if (!$pl_ex) {
//            return Helper::apiNotFoundResponse(false, 'Record not found', new stdClass());
//        }
//
////        if (Storage::exists($pl_ex->video_file) && $request->hasFile('video_file')) {
////            Storage::delete($pl_ex->video_file);
////            Storage::delete($pl_ex->thumbnail);
////        }
//
//        $video_file = "";
//        if ($request->hasFile('video_file')) {
//            $video_file = Storage::putFile(PlayerExercise::$media, $request->video_file);
//        }
//
//        $thumbnail = "";
//        if ($request->hasFile('thumbnail')) {
//            $thumbnail = Storage::putFile(PlayerExercise::$media, $request->thumbnail);
//        }
//
//        if ($video_file == "" || $thumbnail == "") {
//            return Helper::apiNotFoundResponse(false, 'Failed to upload video or thumbnail', new stdClass());
//        }
//
//        $res = DB::transaction(function () use ($thumbnail, $video_file, $request, $pl_ex) {
//
//            $pl_ex->thumbnail = $thumbnail;
//            $pl_ex->video_file = $video_file;
//
//            $pl_ex->save();
//
//            $post = Post::wherePlayerExerciseId($request->player_exercise_id)->update([
//                'thumbnail' => $thumbnail,
//                'post_attachment' => $video_file
//            ]);
//
//            return (int)($post && $pl_ex);
//        });
//
//        if (!$res) {
//            return Helper::apiNotFoundResponse(false, 'Failed to save data in post or player exercise', new stdClass());
//        }
//
//        return Helper::apiSuccessResponse(true, 'Exercise video uploaded', new stdClass());
//    }


    /**
     * Upload Exercise CSV
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Exercise csv uploaded",
     * "Result": {}
     * }
     *
     * @bodyParam player_exercise_id string required
     * @bodyParam exercise_id string required
     * @bodyParam file file csv
     *
     * @return JsonResponse
     */

    public function uploadCsv(Request $request)
    {
        $rules = [
            'player_exercise_id' => 'required|exists:player_exercise,id',
            'exercise_id' => 'required|exists:exercises,id',
            'file' => 'required|file'
        ];
        $this->validate($request, $rules);

        $p_exercise = PlayerExercise::find($request->player_exercise_id);
        if (!$p_exercise) {
            return Helper::apiErrorResponse(false, 'Invalid Player Exercise ID', new stdClass());
        }

        $input = $request->only('player_exercise_id', 'exercise_id', 'file');
        if ($request->hasFile('file')) {
            try {
                $reader = \Maatwebsite\Excel\Facades\Excel::toCollection(new AiImport(), $request->file('file'));
                if (!isset($reader[0]) && count($reader[0]) <= 0) {
                    return Helper::apiErrorResponse(false, 'Failed to save data', new stdClass());
                }
                foreach ($reader[0] as $row) {
                    $ai = new ExerciseAiData();
                    $ai->exercise_id = $request->exercise_id;
                    $ai->player_exercise_id = $request->player_exercise_id;
                    $ai->level_id = $p_exercise->level_id;
                    $ai->user_id = \Auth::user()->id;
                    $ai->title = $row[0];
                    $ai->value = json_encode($row);
                    $ai->save();
                }

                return Helper::apiSuccessResponse(true, 'Csv uploaded', new stdClass());
            } catch (\Exception $e) {
                return Helper::apiErrorResponse(false, 'Failed to save data', new stdClass());
            }
        }

    }

    /**
     * Get Player Exercise Json
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result":
     * {
     * "person_data": [
     * {
     * "frame_id": 0,
     * "time_stamp": 244983918,
     * "RIGHT_EYEx": 0,
     * "RIGHT_EYEy": 0,
     * "status": "MISSING",
     * "width": 0,
     * "height": 0,
     * "RIGHT_THUMBx": 0,
     * "RIGHT_THUMBy": 0,
     * "LEFT_WRISTx": 0,
     * "LEFT_WRISTy": 0,
     * "RIGHT_ELBOWx": 0,
     * "RIGHT_ELBOWy": 0,
     * "LEFT_INDEXx": 0,
     * "LEFT_INDEXy": 0,
     * "RIGHT_WRISTx": 0,
     * "RIGHT_WRISTy": 0,
     * "LEFT_EYEx": 0,
     * "LEFT_EYEy": 0,
     * "LEFT_ANKLEx": 0,
     * "LEFT_ANKLEy": 0,
     * "LEFT_SHOULDERx": 0,
     * "LEFT_SHOULDERy": 0,
     * "LEFT_PINKYx": 0,
     * "LEFT_PINKYy": 0,
     * "RIGHT_ANKLEx": 0,...
     * }
     * ]
     * }
     * }
     *
     * @bodyParam user_id required
     * @bodyParam exercise_id required
     * @return JsonResponse
     */

    public function getPlayerExerciseJson(Request $request)
    {
        Validator::make($request->all(), [
            'exercise_id' => 'required',
            'user_id' => 'required|exists:users,id',
        ])->validate();

        $pl_ex = PlayerExercise::whereUserIdAndExerciseId($request->user_id, $request->exercise_id)->first();

        if ($pl_ex) {
            return Helper::apiSuccessResponse(true, 'Record found', json_decode(['ai_json' => $pl_ex->ai_json, 'kpi_json' => $pl_ex->kpi_json]));
        } else {
            return Helper::apiErrorResponse(false, 'Record not found', new stdClass());
        }
    }

    /**
     *
     * Alki Player Exercise Json
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Record updated",
     * "Result": {}
     * }
     *
     * @bodyParam exercise_id integer required
     * @bodyParam user_id integer required
     * @bodyParam json_data file required
     *
     * @return JsonResponse
     */

    public function alkiPlayerExerciseJson(Request $request)
    {
        Validator::make($request->all(), [
            'exercise_id' => 'required',
            'user_id' => 'required|exists:users,id',
            'json_data' => 'required|file',
        ])->validate();

        $pl_ex = PlayerExercise::where('id', $request->player_exercise_id)
            ->where('user_id', Auth::user()->id)
            ->first();

        if ($pl_ex) {
            $pl_ex->kpi_json = fopen($request->file('json_data'), 'r');
            $pl_ex->save();
            return Helper::apiSuccessResponse(true, 'Record updated', []);
        } else {
            return Helper::apiErrorResponse(false, 'Record not found', new stdClass());
        }
    }

    public function notificationData($assignment){
        $data['from_user_id'] = auth()->user()->id;
        $data['to_user_id'] = $assignment ? $assignment->trainer_user_id : null;
        $data['model_type'] = 'exercises/finished';
        $data['model_type_id'] = $assignment ? $assignment->id : null;
        $data['click_action'] = 'ViewExercises';
        $data['message']['en'] = auth()->user()->first_name . ' ' . auth()->user()->last_name . ' has finished the exercise ' . ($assignment ? $assignment->title : '');
        $data['message']['nl'] = auth()->user()->first_name . ' ' . auth()->user()->last_name . ' har avslutat övningen ' . ($assignment ? $assignment->title : '');
        $data['message'] = json_encode($data['message']);
        $data['badge_count'] = $assignment ? ($assignment->author->badge_count ?? "") + 1 : 0;

        $devices = $assignment ? $assignment->author->user_devices : [];
        return ['data' => $data, 'devices' => $devices];
    }

    public function sendNotificationsOnDevices($devices,$data){
        $tokens = [];

        foreach ($devices as $key => $value) {
            if ($value->device_token) {
                array_push($tokens, $value->device_token);
            }
        }

        if (count($tokens) > 0) {
            foreach ($devices as $device) {
                Helper::sendNotification($data, $device->onesignal_token, $device->device_type);
            }

            User::where('id', $data['to_user_id'])
                ->update([
                    'badge_count' => $data['badge_count']
                ]);
        }
    }

    public function getToolName($exercises){
        foreach ($exercises as $key => $value)
        {
            $exercises[$key]->badge = $exercises[$key]->badge == 'ai_both' ? "ai" : 'non-ai';

            // foreach ($value->tools as $value)
            // {

            //     // $fileName = json_decode($value->getAttributes()['tool_name']);

            //     // if ($fileName) {
            //     //     $value->file_name = strtolower(str_replace("/", "_", $fileName->en));
            //     // }
            // }
        }

        return $exercises;
    }

    /**
     * Remove Assignments
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Player assignment deleted successfully",
     * "Result": [
     * ]
     * }
     *
     * @urlParam player_assignment_id required integer
     *
     * @return JsonResponse
     */

    public function removeAssignments(Request $request){
        Validator::make($request->all(), [
            'player_assignment_id' => 'required|exists:player_assignments,id'
        ])->validate();

        $player_assignment = PlayerAssignment::where('id', $request->player_assignment_id)->first();
        if(empty($player_assignment)){
            return Helper::apiErrorResponse(false, 'Player assignment not found', new stdClass());
        }
        if($player_assignment->status_id != '3'){
            $deadline = Assignment::whereId($player_assignment->assignment_id)->first()->deadline;
            if(date('Y-m-d') <= date('Y-m-d',strtotime($deadline))){
                return Helper::apiErrorResponse(false, 'Player assignment not completed or expire', new stdClass());
            }
        }

        $player_assignment->delete();
        return Helper::apiSuccessResponse(true, 'Player assignment deleted successfully',[]);
    }




}