<?php

namespace App\Http\Controllers\Api\Dashboard\Setting;

use App\{
    ChatGroup,
    Club,
    PlayerTeamRequest,
    Team
};
use App\Gender;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Line;
use App\TeamPrivacy;
use App\TeamType;
use Carbon\Carbon;
use Illuminate\Http\{
    JsonResponse,
    Request
};
use Illuminate\Support\Facades\{
    Auth,
    DB
};
use App\Imports\DashboardTeamsImport;
use App\Http\Resources\Api\Dashboard\General\CountryCodesResource;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @group Dashboard / Settings
 * APIs for dashboard settings
 */
class TeamsController extends Controller
{
    private $teamModel, $playerTeamRequestModel;

    public function __construct()
    {
        $this->teamModel = new Team();
        
        $this->playerTeamRequestModel = new PlayerTeamRequest();
    }

    /**
     * GetClubTeams
     *
     * @response
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "Teams found",
    "Result": [
    {
    "id": 6,
    "team_name": "Test",
    "image": "",
    "gender": "mixed",
    "team_type": "indoor",
    "description": null,
    "age_group": "23",
    "min_age_group": 13,
    "max_age_group": 13,
    "sensors": 0,
    "total_assignments": 63,
    "total_requests": 0,
    "players_count": 10,
    "subscription": "communication"
    },
    {
    "id": 14,
    "team_name": "Updated Team 14 team AGAIN",
    "image": "",
    "gender": "woman",
    "team_type": "field",
    "description": null,
    "age_group": "19",
    "min_age_group": 0,
    "max_age_group": 0,
    "sensors": 0,
    "total_assignments": 7,
    "total_requests": 0,
    "players_count": 2,
    "subscription": "freemium"
    },
    {
    "id": 36,
    "team_name": "Street 12",
    "image": "",
    "gender": "man",
    "team_type": "indoor",
    "description": null,
    "age_group": "12",
    "min_age_group": 13,
    "max_age_group": 13,
    "sensors": 0,
    "total_assignments": 40,
    "total_requests": 0,
    "players_count": 21,
    "subscription": "freemium"
    },
    {
    "id": 89,
    "team_name": "New Team",
    "image": null,
    "gender": "woman",
    "team_type": "field",
    "description": null,
    "age_group": null,
    "min_age_group": 0,
    "max_age_group": 0,
    "sensors": 0,
    "total_assignments": 0,
    "total_requests": 0,
    "players_count": 0,
    "subscription": "freemium"
    },
    {
    "id": 90,
    "team_name": "New Team",
    "image": null,
    "gender": "woman",
    "team_type": "field",
    "description": null,
    "age_group": null,
    "min_age_group": null,
    "max_age_group": null,
    "sensors": 0,
    "total_assignments": 0,
    "total_requests": 0,
    "players_count": 0,
    "subscription": "freemium"
    }
    ]
    }
     * @queryparam id optional Id of the club.
     * @return JsonResponse
     */
//->where("privacy","open_to_invites")
    public function index(Request $request){
        if ($request->id) {

            // CHECK IF THE TRAINER IS IN THE CLUB OR NOT.
            $clubs = (new Club())->myCLubs($request);
            $club_ids = [];
            foreach ($clubs->original["Result"] as $club)
            {
                $club_ids[] = $club['id'];
            }
            if(!in_array($request->id,$club_ids)){
                return Helper::apiErrorResponse(false, 'Add Club First',new \stdClass());
            }
            // IF IN THE CLUB THEN GET THE TEAMS RELATED TO THAT CLUBS
            $teams = Team::whereHas('clubs', function ($q) use ($request) {
                return $q->where('club_id', $request->id);
            })
                ->with('subscription')
                ->with(["players"=>function($player)
                {
                    $player->withCount("player_assignments");
                }])->get();
            if ($teams->count()) {
                $teams = $teams->map(function ($team) {
                    return (new Team())->getTeamObject($team);
                });
                return Helper::apiSuccessResponse(true, 'Teams found', $teams);
            }
            else{
                return Helper::apiSuccessResponse(false, 'Teams Not found', []);
            }
        }

        // IF CLUB ID IS NOT GIVEN
        $club = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->first();
        if (!$club) {
            return Helper::apiErrorResponse(false, 'Club not found', new \stdClass());
        }
        $club_id = $club->club_id ?? 0;
        $teams = Team::whereHas('clubs', function ($q) use ($club_id) {
            return $q->where('club_id', $club_id);
        })->where("privacy","open_to_invites")
            ->with('subscription')
            ->withCount('players')->get();
        if ($teams->count()) {
            $teams = $teams->map(function ($team) {
                return (new Team())->getTeamObject($team);
            });
            return Helper::apiSuccessResponse(true, 'Teams found', $teams);
        }
        return Helper::apiSuccessResponse(false, 'Teams Not found', []);

    }
    /**
     * Get Age Groups
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": ["16", "19", "22"]
    }
     *
     * @return JsonResponse
     */

    public function getAgeGroups(){
//        $age_groups = Team::where('age_group', '!=', '')->distinct()->groupBy('age_group')->pluck('age_group');
        $age_groups = [];
        for($i = 5; $i <= 50; $i++) {
            array_push($age_groups, $i);
        }
//        if($age_groups){
            return Helper::apiSuccessResponse(true, 'Age Groups found', $age_groups);
//        }
//        return Helper::apiSuccessResponse(false, 'Age Groups Not found', []);

    }

    /**
     * Get Team Types
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": ["field", "indoor"]
    }
     *
     * @return JsonResponse
     */

    public function getTeamTypes(){
//        $age_groups = Team::where('age_group', '!=', '')->distinct()->groupBy('age_group')->pluck('age_group');
//        $types = ['field', 'indoor', 'outdoor'];
//        if($age_groups){
        $types = TeamType::select('id','name')->whereStatus('active')->get();
        return Helper::apiSuccessResponse(true, 'Success', $types);
//        }
//        return Helper::apiSuccessResponse(false, 'Age Groups Not found', []);

    }

    /**
     * Get Team Privacies
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
    public function getTeamPrivacies(){
        $privacies = TeamPrivacy::select('id','name')->whereStatus('active')->get();
        return Helper::apiSuccessResponse(true,"Success",$privacies);
    }

    /**
     * Get Team Genders
     *
     * @response {
        "Response": true,
        "StatusCode": 200,
        "Message": "Success",
        "Result": [
            {
            "id": 1,
            "type": "Man"
            },
            {
            "id": 2,
            "type": "Woman"
            },
            {
            "id": 3,
            "type": "Other"
            }
        ]
    }
     *
     * @return JsonResponse
     */

    public function getTeamGenders(){
//        $age_groups = Team::where('age_group', '!=', '')->distinct()->groupBy('age_group')->pluck('age_group');
        $types = Gender::select('id','type')->whereStatus('1')->get();
//        if($age_groups){
        return Helper::apiSuccessResponse(true, 'Success', $types);
//        }
//        return Helper::apiSuccessResponse(false, 'Age Groups Not found', []);

    }



    /**
     * Save Team
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Team Saved",
    "Result": [
    {
    "team_name": "New Team",
    "description": null,
    "min_age_group": "5",
    "max_age_group": "10",
    "team_type": "field",
    "privacy": "open_for_invites",
    "gender": "man",
    "image": "media/teams/Oqse2EJaMRtp7eKKh5z9GxnJYetb4sfndCc5kMxu.png",
    "updated_at": "2021-06-25 15:11:08",
    "created_at": "2021-06-25 15:11:08",
    "id": 79
    }
    ]
    }
     *
     * @bodyParam team_name string required  required
     * @bodyParam age_group string required  optional
     * @bodyParam min_group_age integer required
     * @bodyParam max_group_age integer  required
     * @bodyParam gender int required  gender id
     * @bodyParam team_type int required  team_type_id
     * @bodyParam club_id   integer required
     * @bodyParam description string
     * @bodyParam team_privacy id optional team_privacy_id
     * @bodyParam image file optional
     * @bodyParam team_id integer optional required when updating a team
     * @return JsonResponse
     */
    public function saveTeam(Request $request){
        $validation=[
            'team_name' => 'required|max:255',
            //'age_group' => 'nullable|max:255',
            'gender' => 'required|exists:genders,id',
            'team_privacy'=>'nullable|exists:team_privacies,id',
            'club_id'=>"required|integer",
//            'min_age_group' =>'required|integer',
//            'max_age_group' => 'required|integer',
            'team_type' => 'required|exists:team_types,id',
        ];
        $validator = Validator::make($request->all(), $validation);
        if($validator->fails()) {
            return Helper::apiErrorResponse(false, 'Error', $validator->messages()->toArray());
        }
        $clubs = (new Club())->myCLubs($request);
        $club_ids = [];
        foreach ($clubs->original["Result"] as $club)
        {
            $club_ids[] = $club['id'];
        }
        if(!in_array($request->club_id,$club_ids)){
            return Helper::apiErrorResponse(false, 'Add Club First',new \stdClass());
        }
        $club_id = $request->club_id ?? 0;
        DB::beginTransaction();
        $teams=[];
        try{
            $team = Team::find($request->team_id);
            if(!$team){
                $team = new Team();
            }
            $save_team = $team->store($request);
            if($save_team instanceof Team){
                $save_team->clubs()->syncWithoutDetaching([$club_id]);
                $save_team->trainers()->syncWithoutDetaching([Auth::user()->id]);
                $teams[]=$save_team;
            }
        }catch (\Exception $e){
            return Helper::apiErrorResponse(false, 'Something went wrong',new \stdClass());
        }
        DB::commit();
        
        return Helper::apiSuccessResponse(true, 'Team Saved',$teams);
    }



    /**
        Get Club Team Details

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "Team found",
            "Result": {
                "id": 5,
                "team_name": "consequatur",
                "team_type": "field",
                "gender": "man",
                "image": "media/clubs/C1sPmsWImR0t8uJaIvXlVeKlo8RIoJPzox5gSbuy.png",
                "description": "tempore",
                "team_privacy": "open_to_invites",
                "min_age_group": 13,
                "max_age_group": 13,
                "players": [
                    {
                        "id": 523,
                        "first_name": "Player",
                        "last_name": "1",
                        "country_code": {
                            "id": 164,
                            "code": 92
                        },
                        "phone": "1234567890",
                        "gender": "man",
                        "date_of_birth": "1999-11-28",
                        "positions": [
                            {
                                "id": 3,
                                "name": "Goal Keeper",
                                "lines": 2,
                                "pivot": {
                                    "player_id": 219,
                                    "position_id": 3
                                },
                                "line": {
                                    "id": 2,
                                    "name": "GoalKeepers"
                                }
                            }
                        ],
                        "player_name": "Player 1",
                        "profile_picture": null,
                        "teams": [
                            "consequatur"
                        ],
                        "quality_score": 0,
                        "last_seen": 0
                    }
                ],
                "trainers": [
                    {
                        "id": 91,
                        "trainer_name": "Trainer Durgan ",
                        "profile_picture": "media/users/z6IJLTTG0tLsW6LbDtAYEQvAE09UUisv94dV8jw1.jpeg",
                        "email": "trainer@jogo.ai",
                        "last_online": "2021-03-18",
                        "teams": [],
                        "applied_team": ""
                    }
                ],
                "team_overall": {
                    "total_exercise_minutes": 0,
                    "total_exercises": 0,
                    "last_active": 25,
                    "active_sensors": {
                        "active": 0,
                        "out_of": 0
                    }
                },
                "performance": [
                    {
                        "goalkeepers": {
                            "profile_pictures": [
                                "media/users/5f9fc5c306fbf1604306371.jpeg",
                                "media/users/5f996dc5898911603890629.jpeg"
                            ],
                            "exercise_min": 0,
                            "ai_exercises": 0,
                            "active_players": {
                                "active": 0,
                                "out_of": 4
                            },
                            "active_sensors": 0,
                            "quality_score": 0
                        }
                    }
                ]
            }
        }
    */

    public function teamDetails($id){
        $team = Team::with([
            'players'=>function($player){
                $player->select('first_name','middle_name','last_name','profile_picture',"last_seen","active",'user_id as id', 'country_code_id', 'phone', 'gender', 'date_of_birth')->whereHas('player');
                $player->with('teams');
                $player->with("exercises");
            },

            'trainers'=>function($trainer){
                $trainer->select('first_name','middle_name','last_name','profile_picture',"email","last_seen",'trainer_user_id as id');
                $trainer->with('teams');
            },

            'players.player' => function ($q1) {
                $q1->select('players.id', 'players.user_id', 'players.position_id');

            },
            'players.player.positions' => function ($query)
            {
                $query->select('positions.id', 'name', 'lines');
            },
            'players.player.positions.line' => function ($query)
            {
                $query->select('lines.id', 'name');
            },
            'exercises' => function ($exercise){
            $exercise->select("nseconds","badge");
            }
        ])->find($id);
        $team_overall = []; // INITIALIZE TEAM OVERALL
        $lines = Line::get()->pluck('name');
        $performance = [
                [
                    $lines[1] => []
                ],
                [
                    $lines[0] => []
                ],
                [
                    $lines[3] => []
                ],
                [
                    $lines[2] => []
                ]
            ];

        if($team){
            $new_team = new \stdClass();
            $new_team->id= $team->id;
            $new_team->team_name= $team->team_name;
            $new_team->team_type= $team->team_type;
            $new_team->gender= $team->gender;
            $new_team->image= $team->image ? $team->image : (count($team->clubs) > 0 ? $team->clubs[0]->image : null);
            $new_team->description= $team->description;
                    $new_team->team_privacy = $team->privacy;
                    //$new_team->age_group= $team->age_group;
                    $new_team->min_age_group = $team->min_age_group;
                    $new_team->max_age_group = $team->max_age_group;
                    $players = $team->players->map(function ($player){
                        $pl = new \stdClass();
                        $pl->id = $player->id;
                        $pl->first_name = $player->first_name;
                        $pl->last_name = $player->last_name;
                        $pl->country_code = (new CountryCodesResource($player->country_code))->resolve();
                        $pl->phone = $player->phone;
                        $pl->gender = $player->gender;
                        $pl->date_of_birth = $player->date_of_birth;
                        $pl->positions = $player->player->positions ?? [];
                        $pl->player_name = $player->first_name .' '.$player->last_name;
                        $pl->profile_picture = $player->profile_picture;
                        $pl->teams = $player->teams->pluck('team_name');
                        $pl->quality_score = $player->exercises->count();
                        $pl->last_seen = Carbon::createFromDate($player->last_seen)->diffInHours(now());
                        $pl->parent_email= DB::table('parent_players')->where('player_id',$player->id)->value('parent_email') ?? null;
                        return $pl;
                    });
                    $new_team->players = $players;
                    $trainers = $team->trainers->map(function ($trainers){
                        $pl = new \stdClass();
                        $pl->id = $trainers->id;
                        $pl->trainer_name = $trainers->first_name .' '.$trainers->last_name;
                        $pl->profile_picture = $trainers->profile_picture;
                        $pl->email = $trainers->email;
                        $pl->last_online = $trainers->last_seen !== null ? Carbon::create($trainers->last_seen)->format("Y-m-d") : null;
                        $pl->teams = $trainers->teams->pluck('team_name');
                        $pl->applied_team = count($pl->teams) > 0 ? $pl->teams[0] : "";
                        return $pl;
                    });
                    $new_team->trainers = $trainers;
                    $team_overall["total_exercise_minutes"] = ($team->exercises->sum("nseconds")) / 60; // TOTAL TIMES IN MINUTES
                    $team_overall["total_exercises"] = $team->exercises->count(); // TOTAL AI EXERCISES
                    $team_overall["last_active"] = $players->where("last_seen","<=",24)->count(); // PLAYERS COUNT WHO WAS LAST ACTIVE IN 24 HOURS
                    $team_overall["active_sensors"] = ["active"=>0,"out_of"=>0]; // ACTIVE SENSORS (HARDCODED FOR NOW)
                    $new_team->team_overall = $team_overall;

//                    POSITION CATEGORY REFERENCE
//                    Defenders: 1,2,4
//                    Attackers : 7,9,10
//                    Midfielders: 5,6,8
//                    GoalKeeper : 3

                    foreach ($team->players as $player){
                        $playerPositionsArray = $player->player->positions->pluck('id')->toArray();
                    //FOR PLAYER POSITION EQUAL TO GOALKEEPER
                        if (in_array(3, $playerPositionsArray))
                    {
                        $performance[0][$lines[1]] = [
                            "profile_pictures" => $team->players()
                            ->whereNotNull("profile_picture")
                            //->where("player.positions.id",3)
                            ->whereHas("player.positions", function ($query)
                            {
                                $query->where("positions.id", 3);
                            })
                            ->pluck("profile_picture")
                            ->toArray(),
                            "exercise_min"=>$player->exercises->sum("nseconds") / 60, // TOTAL EXERCISES TIME IN MINUTES
                            "ai_exercises"=>$player->exercises->where("badge","!=","non_ai")->where("status_id",3)->count(), // TOTAL AI EXERCISES
                            "active_players" => [
                                "active"=>$team->players()
                                ->where("status_id", 1)
                                //->where("player.positions.id",3)
                                ->whereHas("player.positions", function ($query)
                                {
                                    $query->where("positions.id", 3);
                                })
                                ->count(),
                                "out_of" => $team->players()
                                //->where("player.positions.id",3)
                                ->whereHas("player.positions", function ($query)
                                {
                                    $query->where("positions.id", 3);
                                })
                                ->count()
                            ], // TOTAL ACTIVE PLAYERS
                            "active_sensors"=>0, // TOTAL SENSORS (HARDCODED FOR NOW)
                            "quality_score" => $player->exercises->count()
                            ];
                    }

                     //FOR PLAYER POSITION EQUAL TO DEFENDERS
                    if (in_array(2, $playerPositionsArray) || in_array(1, $playerPositionsArray) || in_array(4, $playerPositionsArray))
                    {
                        $performance[1][$lines[0]] = [
                            "profile_pictures" => $team->players()
                            ->whereNotNull("profile_picture")
                            //->whereIn("player.positions.id",[2,1,4])
                            ->whereHas("player.positions", function ($query)
                            {
                                $query->whereIn("positions.id", [1, 2, 4]);
                            })
                            ->pluck("profile_picture")
                            ->toArray(),
                            "exercise_min"=>$player->exercises->sum("nseconds") / 60, // TOTAL EXERCISES TIME IN MINUTES
                            "ai_exercises"=>$player->exercises->where("badge","!=","non_ai")->where("status_id",3)->count(), // TOTAL AI EXERCISES
                            "active_players" => [
                                "active" => $team->players()
                                ->where("status_id", 1)
                                //->whereIn("player.positions.id",[2,1,4])
                                ->whereHas("player.positions", function ($query)
                                {
                                    $query->whereIn("positions.id", [1, 2, 4]);
                                })
                                ->count(),
                                "out_of" => $team->players()
                                //->whereIn("player.positions.id",[2,1,4])
                                ->whereHas("player.positions", function ($query)
                                {
                                    $query->whereIn("positions.id", [1, 2, 4]);
                                })
                                ->count()
                            ], // TOTAL ACTIVE PLAYERS
                            "active_sensors"=>0, // TOTAL SENSORS (HARDCODED FOR NOW)
                            "quality_score" => $player->exercises->count()
                        ];
                    }

                    // FOR PLAYER POSITION EQUAL TO MIDFIELDERS
                    if (in_array(5, $playerPositionsArray) || in_array(6, $playerPositionsArray) || in_array(8, $playerPositionsArray))
                    {
                        $performance[2][$lines[3]] = [
                            "profile_pictures" => $team->players()
                            ->whereNotNull("profile_picture")
                            //->whereIn("player.positions.id",[5,6,8])
                            ->whereHas("player.positions", function ($query)
                            {
                                $query->whereIn("positions.id", [5, 6, 8]);
                            })
                            ->pluck("profile_picture")
                            ->toArray(),
                            "exercise_min"=>$player->exercises->sum("nseconds") / 60, // TOTAL EXERCISES TIME IN MINUTES
                            "ai_exercises"=>$player->exercises->where("badge","!=","non_ai")->where("status_id",3)->count(), // TOTAL AI EXERCISES
                            "active_players" => [
                                "active" => $team->players()
                                ->where("status_id", 1)
                                //->whereIn("player.positions.id",[5,6,8])
                                ->whereHas("player.positions", function ($query)
                                {
                                    $query->whereIn("positions.id", [5, 6, 8]);
                                })
                                ->count(),
                                "out_of" => $team->players()
                                //->whereIn("player.positions.id",[5,6,8])
                                ->whereHas("player.positions", function ($query)
                                {
                                    $query->whereIn("positions.id", [5, 6, 8]);
                                })
                                ->count()
                            ], // TOTAL ACTIVE PLAYERS
                            "active_sensors"=>0, // TOTAL SENSORS (HARDCODED FOR NOW)
                            "quality_score" => $player->exercises->count()
                        ];
                    }

                    // FOR PLAYER POSITION EQUAL TO ATTACKERS
                    if (in_array(10, $playerPositionsArray) || in_array(7, $playerPositionsArray) || in_array(9, $playerPositionsArray))
                    {
                        $performance[3][$lines[2]] = [
                            "profile_pictures" => $team->players()
                            ->whereNotNull("profile_picture")
                            //->whereIn("player.positions.id",[7,9,10])
                            ->whereHas("player.positions", function ($query)
                            {
                                $query->whereIn("positions.id", [7, 9, 10]);
                            })
                            ->pluck("profile_picture")
                            ->toArray(),
                            "exercise_min"=>$player->exercises->sum("nseconds") / 60, // TOTAL EXERCISES TIME IN MINUTES
                            "ai_exercises"=>$player->exercises->where("badge","!=","non_ai")->where("status_id",3)->count(), // TOTAL AI EXERCISES
                            "active_players" => [
                                "active" => $team->players()
                                ->where("status_id", 1)
                                //->whereIn("player.positions.id",[7,9,10])
                                ->whereHas("player.positions", function ($query)
                                {
                                    $query->whereIn("positions.id", [7, 9, 10]);
                                })
                                ->count(),
                                "out_of" => $team->players()
                                //->whereIn("player.positions.id",[7,9,10])
                                ->whereHas("player.positions", function ($query)
                                {
                                    $query->whereIn("positions.id", [7, 9, 10]);
                                })
                                ->count()
                            ], // TOTAL ACTIVE PLAYERS
                            "active_sensors"=>0, // TOTAL SENSORS (HARDCODED FOR NOW)
                            "quality_score" => $player->exercises->count()
                        ];
                    }
                }

            $new_team->performance = $performance;
            return Helper::apiSuccessResponse(true, 'Team found',$new_team);
        }
        return Helper::apiErrorResponse(false, 'Team not found',new \stdClass());
    }


    /**
     * Get  Team Requests
     *
     * @response{
    "Response": true,
    "StatusCode": 200,
    "Message": "Team requests found",
    "Result": [
    {
    "id": 2,
    "player_user_id": 1,
    "team_id": 6,
    "status": 1,
    "created_at": "2020-11-19 02:43:45",
    "updated_at": "2020-11-19 02:43:45",
    "player": {
    "id": 1,
    "first_name": "Christiano",
    "middle_name": "''",
    "last_name": "Ronaldo",
    "profile_picture": "media/users/5fa3f8cb6b10b1604581579.jpeg",
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
    ]
    }
     *
     * @return JsonResponse
     */

    public function teamRequests(Request $request)
    {
        $response = $this->playerTeamRequestModel->teamRequests($request);

        return $response;
    }


    /**
     * Accept Team Request
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Request Accepted",
    "Result": {}
    }
     *
     * @bodyParam request_id integer required  required
     * @return JsonResponse
     */
    public function acceptTeamRequests(Request $request){
        $request->validate([
            "request_id"=>"required|integer"
        ]);
        
        return $this->playerTeamRequestModel->acceptTeamRequests($request);
    }


    /**
     * Reject Team Request
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Request Rejected",
    "Result": {}
    }
     *
     * @bodyParam request_id integer required  required
     * @return JsonResponse
     */
    public function rejectTeamRequests(Request $request){
        return $this->playerTeamRequestModel->rejectTeamRequests($request);
    }

    /**
     * Dashboard Sample Export Teams
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": ['media': 'media/sample_csv/sample_teams.csv']
    }
     *
     * @bodyParam csv file required  required
     * @return JsonResponse
     */
    public function sampleExport(Request $request){
        return Helper::apiSuccessResponse(true, 'Success', ['media' => 'media/sample_csv/sample_teams.csv']);
    }

    /**
     * Dashboard Bulk Import Teams
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Imported Successfully",
    "Result": {}
    }
     *
     * @bodyParam csv file required  required
     * @return JsonResponse
     */
    public function bulkImport(Request $request){
        $this->validate($request,[
            'csv' => 'required|max:10000'
        ]);
        \Session::forget('response_team_csv');
        $res = Excel::import(new DashboardTeamsImport,$request->file('csv'));
        //dd($res);
        $res = \Session::get('response_team_csv');
        if($res == 'success') {
            return Helper::apiSuccessResponse(true, 'Imported Successfully', new \stdClass());
        } else {
            return Helper::apiErrorResponse(false, 'Validation Error', $res);
        }
    }


    /**
     *RemovePlayer
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Player removed from team",
    "Result": {}
    }
     *
     * @bodyParam player_id  required  required
     * @bodyParam team_id  required  required
     * @return JsonResponse
     */

    public function removePlayer(Request $request){
        $player_teams = DB::table('player_team')->where('user_id',$request->player_id)->where('team_id',$request->team_id)->first();
        if(!$player_teams){
            return Helper::apiErrorResponse(false, 'Specified player is not member of this team', new \stdClass());
        }
        $delete_team = DB::table('player_team')
            ->where('user_id',$request->player_id)
            ->where('team_id',$request->team_id)->delete();
        if($delete_team){
            //remove player from team group
            $group =ChatGroup::where('team_id',$request->team_id)->first();
            if($group){
                $group->members()->detach([$request->player_id]);
            }
            return Helper::apiSuccessResponse(true, 'Player removed from team', new \stdClass());
        }
        return Helper::apiErrorResponse(false, 'Something went wrong', new \stdClass());
    }

    /**
        Delete Team

        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "Record has deleted successfully",
            "Result": {}
        }

        @response 500
        {
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid Id",
            "Result": {}
        }
    **/

    public function delete(Request $request, $id)
    {
        $apiType = 'dashboard';

        $event = $this->teamModel->remove($id, $apiType);

        return $event;
    }
}