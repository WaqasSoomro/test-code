<?php
namespace App\Http\Controllers\Api\Dashboard;
use App\Assignment;
use App\AssignmentExercise;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Dashboard\Assignment\AssignmentResource;
use App\PlayerAssignment;
use App\PlayerExercise;
use App\PlayerTeam;
use App\Status;
use App\Team;
use App\TeamTrainer;
use App\User;
use App\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * @group Dashboard / Skill Assignment APIs
 *
 */
class AssignmentController extends Controller
{
    /**
     * Get Assignments
     *
     * @response
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "Records found successfully!",
    "Result": [
    {
    "id": 180,
    "trainer_user_id": 585,
    "difficulty_level": null,
    "title": "Test Assignment",
    "assign_to": "consequatur",
    "description": "TEST ASSIGNMENT DESCRIPTION",
    "image": "media/assignments/g9jGuqf1oRGZIoE2KKradbxzAt32ExiYqf6V51VO.png",
    "deadline": "2021-07-30 00:00:00",
    "created_at": "2021-07-09 09:38:10",
    "updated_at": "2021-07-09 09:38:10",
    "deleted_at": null,
    "exercises_count": 0,
    "players_count": 1,
    "player_completed_count": 0
    },
    {
    "id": 179,
    "trainer_user_id": 585,
    "difficulty_level": null,
    "title": "Test Assignment",
    "assign_to": "consequatur",
    "description": "TEST ASSIGNMENT DESCRIPTION",
    "image": "",
    "deadline": "2021-07-25 00:00:00",
    "created_at": "2021-07-08 11:46:07",
    "updated_at": "2021-07-08 11:46:07",
    "deleted_at": null,
    "exercises_count": 0,
    "players_count": 1,
    "player_completed_count": 0
    },
    {
    "id": 178,
    "trainer_user_id": 585,
    "difficulty_level": null,
    "title": "My NEW ASSIGNMENT",
    "assign_to": "ManUtd U18",
    "description": "updated",
    "image": "",
    "deadline": "2021-08-01 00:00:00",
    "created_at": "2021-07-01 12:52:09",
    "updated_at": "2021-07-01 12:52:09",
    "deleted_at": null,
    "exercises_count": 0,
    "players_count": 8,
    "player_completed_count": 0
    },
    {
    "id": 5,
    "trainer_user_id": 585,
    "difficulty_level": null,
    "title": "Fourth Assignment",
    "assign_to": null,
    "description": "An assignment",
    "image": "",
    "deadline": "2020-11-19 00:00:00",
    "created_at": "2020-11-09 14:35:36",
    "updated_at": "2020-11-09 14:35:36",
    "deleted_at": null,
    "exercises_count": 1,
    "players_count": 19,
    "player_completed_count": 1
    },
    {
    "id": 4,
    "trainer_user_id": 585,
    "difficulty_level": null,
    "title": "Third Assignment",
    "assign_to": null,
    "description": "Third Assignment 3",
    "image": "",
    "deadline": "2020-11-11 00:00:00",
    "created_at": "2020-11-09 11:36:52",
    "updated_at": "2020-11-09 11:36:52",
    "deleted_at": null,
    "exercises_count": 2,
    "players_count": 13,
    "player_completed_count": 2
    },
    {
    "id": 3,
    "trainer_user_id": 585,
    "difficulty_level": null,
    "title": "Second ever JOGO assignment",
    "assign_to": null,
    "description": "Try his out",
    "image": "media/assignments/10BGRmLRr7h0UvzNso5jopBE5T40XDH8cYwCrEC7.jpeg",
    "deadline": "2020-11-09 00:00:00",
    "created_at": "2020-11-08 20:00:03",
    "updated_at": "2020-11-08 20:00:03",
    "deleted_at": null,
    "exercises_count": 2,
    "players_count": 12,
    "player_completed_count": 2
    },
    {
    "id": 1,
    "trainer_user_id": 585,
    "difficulty_level": null,
    "title": "First ever assignment JOGO",
    "assign_to": null,
    "description": "Ball juggling",
    "image": "media/assignments/cwBvJaBFFYTSfLJRjb1lcMG0tpbtqHmQQjSE0pZs.jpeg",
    "deadline": "2020-11-06 00:00:00",
    "created_at": "2020-11-06 12:59:32",
    "updated_at": "2020-11-06 12:59:32",
    "deleted_at": null,
    "exercises_count": 1,
    "players_count": 11,
    "player_completed_count": 1
    }
    ]
    }
     *
     * @queryParam clubId required integer
     * @return JsonResponse
     */
    public function index(Request $request)
    {

        $request->validate([
            "clubId" => "required|integer"
        ]);

        // CHECK IF THE TRAINER IS IN THE REQUESTED CLUB
        $user_exist_in_club = DB::table("club_trainers")->where("trainer_user_id",auth()->user()->id)
            ->where("club_id",$request->clubId)->first();

        if (!$user_exist_in_club)
        {
            $user_exist_in_club = Club::select('id')
            ->where('owner_id', auth()->user()->id)
            ->first();

            if (!$user_exist_in_club)
            {
                return Helper::apiNotFoundResponse(
                    false,
                    "Trainer Not In Club",
                    new stdClass()
                );
            }
        }

        $teams = Team::
        // WHERE TRAINER IS IN THAT TEAM
        whereHas("trainers",function ($trainer)
        {
            $trainer->where("trainer_user_id",auth()->user()->id);
        })
            // WHERE TEAM IS IN THE REQUEST CLUB
        ->whereHas("clubs",function ($club)
        use($request)
        {
            $club->where("club_id",$request->clubId);
            // WITH ALL THE PLAYERS IN THE TEAM
        })->with('players')
            ->get();

        // GET ALL THE PLAYERS ID FROM TEAMS
        $players = [];
        foreach ($teams as $team)
        {
            $players[] = $team->players->pluck("id")->toArray();
        }
         $players = array_flatten($players);

        if (count($players) == 0)
        {
            return Helper::apiNotFoundResponse(
                false,
                "Records Not Found",
                new stdClass()
            );
        }

        $status = Status::where('name', 'completed')->first();

        $assignments = Assignment::
        where('trainer_user_id', Auth::user()->id)
            ->whereHas('players',function ($player)
            use($players)
            {
                $player->whereIn("player_user_id",$players);
            })
            ->withCount('exercises')
            ->withCount('players')
            ->withCount(['players as player_completed_count' => function ($q) use ($status) {
                $q->where('player_assignments.status_id', $status->id ?? 0);
            }])->latest()->get();

        if (count($assignments) == 0)
        {
            return Helper::apiNotFoundResponse(false, 'Records not found', []);
        }

        $assignment = AssignmentResource::collection($assignments)->toArray($request);

        return Helper::apiSuccessResponse(true, 'Records found successfully!', $assignment);



    }

    /**
     * Add/Edit Assignment
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records found successfully!",
     * "Result": {
     * "trainer_user_id": 585,
     * "title": "My NEW ASSIGNMENT",
     * "assign_to": "ManUtd U18",
     * "description": "updated",
     * "deadline": "2021-08-01",
     * "image": "",
     * "updated_at": "2021-07-01 12:52:09",
     * "created_at": "2021-07-01 12:52:09",
     * "id": 178
     * }
     * }
     *
     * @bodyParam title string required
     * @bodyParam image string required
     * @bodyParam description string required
     * @bodyParam difficulty_level string required options => beginner,intermediate,expert
     * @bodyParam deadline string required eg: 2020-07-26
     * @bodyParam player_id string required only required if team_id is missing
     * @bodyParam team_id string required only required if player_id is missing
     * @bodyParam skill_ids array required it should be an array
     * @bodyParam lines array required it should be an array
     *
     * @return JsonResponse
     */
    public function addEditAssignment(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'image' => 'required',
            'description' => 'required',
            'deadline' => 'required|date',
            'team_id' => 'required|numeric|exists:team_trainers,team_id,trainer_user_id,'.auth()->user()->id,
            'lines' => 'required|array',
            'lines.*' => 'numeric|exists:lines,id,status,active',
            'positions' => 'required|array',
            'positions.*' => 'numeric|exists:positions,id',
            'players' => 'required|array',
            'players.*' => 'numeric|exists:player_team,user_id,team_id,'.$request->team_id
        ]);

        $assignment = Assignment::find($request->assignment_id);

        if (!$assignment)
        {
            $assignment = new Assignment();
        }

        $request->request->add([
            'trainer_user_id' => Auth::user()->id,
            'trainer_full_name' => Auth::user()->first_name . ' ' . Auth::user()->last_name
        ]);

        $assignment = $assignment->store($request);

        if ($assignment instanceof Assignment) {
            return Helper::apiSuccessResponse(true, 'Records found successfully!', $assignment);
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', []);

        /*$response = (new Assignment())->create($request);

        return $response;*/
    }

    /**
     * Delete Assignment
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Record deleted successfully!",
     * "Result": {}
     * }
     *
     * @bodyParam assignment_id string required
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        validator($request->all(), [
            'assignment_id' => 'required|exists:assignments,id',
        ])->validate();

        return DB::transaction(function () use($request){
            try
            {
                $assignment = Assignment::where('id', $request->assignment_id)->where('trainer_user_id', Auth::user()->id)
                    ->first();

                if (!$assignment) {
                    return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());
                }

                PlayerAssignment::whereAssignmentId($request->assignment_id)->delete();

                $assignment->delete();

                return Helper::apiSuccessResponse(true, 'Record deleted successfully', new stdClass());
            }
            catch (\Error $er)
            {
                return Helper::apiErrorResponse(false,"Something Went Wrong",new stdClass());
            }
            catch (\Exception $ex)
            {
                return Helper::apiErrorResponse(false,"Something Went Wrong", new stdClass());
            }
        });

    }

    /**
     * Copy Assignment
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Assignment copied",
     * "Result": {}
     * }
     *
     * @bodyParam assignment_id string required
     * @return JsonResponse
     */
    public function copyAssignment(Request $request)
    {
        validator($request->all(), [
            'assignment_id' => 'required'
        ])->validate();

        $assignment = Assignment::find($request->assignment_id);

        if (!$assignment) {
            return Helper::apiNotFoundResponse(false, 'Assignment not found', new stdClass());
        }

        Assignment::copy($assignment);

        return Helper::apiSuccessResponse(true, 'Assignment copied', new stdClass());
    }

    /**
     * Add exercise To Assignment
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Exercises added to assignment",
     * "Result": {}
     * }
     *
     * @bodyParam assignment_id string required
     * @bodyParam exercises string required [{exercise_id:1,level_id:1,sort_order:1}]
     * @return JsonResponse
     */
    public function addExerciseToAssignment(Request $request)
    {
        validator($request->all(), [
            'assignment_id' => 'required',
            'exercises' => 'required',
            'exercises.*.exercise_id' => 'required|exists:exercises,id',
            'exercises.*.level_id' => 'required|exists:levels,id',
            'exercises.*.sort_order' => 'required'
        ])->validate();

        $assignment = Assignment::where('id', $request->assignment_id)->where('trainer_user_id', Auth::user()->id)->first();

        if (!$assignment) {
            return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());
        }

        $assignment->exercises()->sync($request->exercises);

        return Helper::apiSuccessResponse(true, 'Exercises added to assignment', new stdClass());
    }

    /**
     * Remove Exercise From Assignment
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Record has been removed",
     * "Result": {}
     * }
     *
     * @bodyParam assignment_id required string
     * @bodyParam exercise_id required string
     * @bodyParam level_id required string
     *
     * @return JsonResponse
     */
    public function removeExerciseFromAssignment(Request $request)
    {
        validator($request->all(), [
            'assignment_id' => 'required',
            'exercise_id' => 'required',
            'level_id' => 'required'
        ])->validate();

        $assignment = Assignment::where('id', $request->assignment_id)->where('trainer_user_id', Auth::user()->id)->first();

        if (!$assignment) {
            return Helper::apiNotFoundResponse(false, 'Assignment not found', new stdClass());
        }

        $assignment = AssignmentExercise::where('assignment_id', $request->assignment_id)
            ->where('exercise_id', $request->exercise_id)
            ->where('level_id', $request->level_id)
            ->first();

        if (!$assignment) {
            return Helper::apiNotFoundResponse(false, 'Assignment exercise not found', new stdClass());
        }

        $assignment->delete();

        return Helper::apiSuccessResponse(true, 'Record has been removed', new stdClass());
    }

    /**
     * Remove Player from exercise
     *
     * @bodyParam assignment_id required string
     * @bodyParam player_id string nullable only required if team_id is missing
     * @bodyParam team_id string nullable only required if player_id is missing
     * @return JsonResponse
     */
    public function removePlayerFromExercise(Request $request)
    {
        // AssignmentID, TeamID (optional), playerID (optional)
        $rules = [
            'assignment_id' => 'required|exists:assignments,id'
        ];

        if (!isset($request->team_id)) {
            $rules['player_id'] = 'required|exists:users,id';
        }

        if (!isset($request->player_id)) {
            $rules['team_id'] = 'required|exists:teams,id';
        }

        validator($request->all(), $rules)->validate();

        $assignment = Assignment::where('id', $request->assignment_id)->where('trainer_user_id', Auth::user()->id)->first();

        if (!$assignment) {
            return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());
        }

        if ($request->player_id != "") {
            PlayerAssignment::where('assignment_id', $request->assignment_id)
                ->where('player_user_id', $request->player_id)->delete();
        } else {

            $team = TeamTrainer::where('team_id', $request->team_id)->where('trainer_user_id', Auth::user()->id)->first();

            if (!$team) {
                return Helper::apiNotFoundResponse(false, 'Team not found', new stdClass());
            }

            $players = PlayerTeam::where('team_id', $request->team_id)->get();

            if (count($players) > 0) {
                $ids = $players->pluck('user_id');

                $res = PlayerAssignment::where('assignment_id', $request->assignment_id)
                    ->whereIn('player_user_id', $ids)->delete();

                if (!$res) {
                    return Helper::apiNotFoundResponse(false, 'Record not found', new stdClass());
                }

                return Helper::apiSuccessResponse(true, 'Record has been removed', new stdClass());
            }
        }

        return Helper::apiNotFoundResponse(false, 'Records not found', new stdClass());
    }

    /**
        Get Assignment Edit Detail
        
        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Assignment found",
            "Result": {
                "id": 163,
                "title": "11-08-2021 testing assignment 1",
                "description": "09-08-2021 testing assignment",
                "deadline": "2021-08-09 00:00:00",
                "image": "",
                "assign_to": "5",
                "assignment_team": {
                    "id": 5,
                    "team_name": "consequatur"
                },
                "skills": [],
                "exercises": [],
                "lines": [
                    {
                        "id": 1,
                        "name": "Defenders",
                        "pivot": {
                            "assignment_id": 163,
                            "line_id": 1
                        }
                    },
                    {
                        "id": 4,
                        "name": "MidFielders",
                        "pivot": {
                            "assignment_id": 163,
                            "line_id": 4
                        }
                    }
                ],
                "players": [
                    {
                        "id": 471,
                        "first_name": "Testing",
                        "last_name": "Player 003 Updated",
                        "pivot": {
                            "assignment_id": 163,
                            "player_user_id": 471,
                            "status_id": 4
                        },
                        "player": {
                            "id": 216,
                            "user_id": 471,
                            "position_id": 5,
                            "positions": [
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
                            ]
                        }
                    }
                ],
                "team": {
                    "id": 5,
                    "team_name": "consequatur"
                }
            }
        }
        
        @urlParam assignment_id required
        
        @return JsonResponse
    */
    
    public function viewEditDetail(Request $request)
    {
        validator($request->all(), [
            'assignment_id' => 'required'
        ])->validate();

        $assignment = Assignment::select('id', 'title', 'description', 'deadline', 'image', 'assign_to')
        ->with([
            'skills' => function ($q) {
                $q->select('skills.id', 'skills.name');
            },
            'exercises' => function ($q) {
                $q->select('exercises.id', 'exercises.title', 'exercises.image')
                ->with('tools:tools.id,tools.tool_name')
                ->with('levels:levels.id,levels.title');
            },
            'lines' => function ($query)
            {
                $query->select('lines.id', 'name');
            },
            'players' => function ($query)
            {
                $query->select('users.id', 'first_name', 'last_name');
            },
            'players.player' => function ($query)
            {
                $query->select('id', 'players.user_id');
            },
            'players.player.positions' => function ($query)
            {
                $query->select('positions.id', 'name', 'lines');
            },
            'players.player.positions.line' => function ($query)
            {
                $query->select('lines.id', 'name');
            },
            'team' => function ($query)
            {
                $query->select('id', 'team_name');
            }
        ])
        ->where('assignments.id', $request->assignment_id)
        ->where('trainer_user_id', Auth::user()->id)
        ->first();

        if (!$assignment) {
            return Helper::apiNotFoundResponse(false, 'Assignment not found', new stdClass());
        }

        if (empty($assignment->team))
        {
            $assignment->assignment_team = new \stdClass();

            $team = Team::select('teams.id', 'teams.team_name')
            ->where('team_name', 'like', "%$assignment->assign_to%")
            ->first();

            if ($team)
            {
                $assignment->assignment_team = $team;
            }
        }
        else
        {
            $assignment->assignment_team = $assignment->team;
        }

        return Helper::apiSuccessResponse(true, 'Assignment found', $assignment);
    }

    /**
     * Get Assignment Detail
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Assignment found",
     * "Result": {
     * "assignment_stats": {
     * "player_assigned": 1,
     * "player_completed": 0,
     * "overall_time": 0,
     * "assignment_level": "beginner",
     * "privacy_type": "public"
     * },
     * "players_details": [
     * {
     * "first_name": null,
     * "last_name": null,
     * "profile_picture": null,
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
     * "team_name": "Team 1",
     * "total_exercises": 2,
     * "completed_exercises": 1,
     * "total_comments": 0
     * }
     * ]
     * }
     * }
     *
     * @urlParam assignment_id required
     * @return JsonResponse
     */
    public function detail(Request $request)
    {
        validator($request->all(), [
            'assignment_id' => 'required'
        ])->validate();

        $response = (new Assignment())->getAssignmentDetail($request,['assignments.id', 'assignments.description','assignments.title', 'assignments.difficulty_level']);

        if(!$response['status']){
            return Helper::apiNotFoundResponse(false, $response['msg'], new stdClass());
        }

        return Helper::apiSuccessResponse(true, 'Assignment found', $response['data']);
    }

    /**
     * Get Player Assignments
     * @response
     * @urlParam assignment_id required
     * @return JsonResponse
     */
    public function getPlayerAssignments(Request $request)
    {
        validator($request->all(), [
            'assignment_id' => 'required'
        ])->validate();

        $user = User::select('users.id', 'users.first_name', 'users.last_name', 'users.nationality_id', 'users.date_of_birth')
            ->with(['player_details' => function ($q) {
                $q->select('id', 'user_id', 'height', 'weight', 'jersey_number', 'position_id');
                $q->with(['customaryFoot']);
            }, 'nationality', 'player_exercises', 'comments'])->whereHas('player_assignments', function ($q) use ($request) {
                $q->where('assignment_id', $request->assignment_id);
            })
            ->with([
                'player_details.positions' => function ($query)
                {
                    $query->select('positions.id', 'name', 'lines');
                },
                'player_details.positions.line' => function ($query)
                {
                    $query->select('lines.id', 'name');
                }
            ])
            ->get();

        if (!$user) {
            return Helper::apiNotFoundResponse(false, 'Record not found', new stdClass());
        }


        return Helper::apiSuccessResponse(true, 'Record found', $user);
    }
}
