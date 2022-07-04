<?php

namespace App\Http\Controllers\Api\Dashboard\Setting;

use App\Gender;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Imports\DashboardPlayersImport;
use App\Imports\DashboardTeamsImport;
use App\Player;
use App\Review;
use App\Team;
use App\PricingPlan;
use App\User;
use App\TeamSubscription;
use App\Coupon;
use App\Country;
use App\Club;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

/**
 * @group Dashboard / Settings
 * APIs for dashboard settings
 */
class PlayersController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Get Club Players
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Records  found",
     * "Result": [
     * {
     * "id": 523,
     * "player_name": "Player 2",
     * "profile_picture": null,
     * "age": null,
     * "gender": "man",
     * "points": 0,
     * "teams": [
     * {
     * "id": 5,
     * "team_name": "consequatur",
     * "image": "",
     * "pivot": {
     * "user_id": 523,
     * "team_id": 5,
     * "created_at": "2021-09-02 10:57:45"
     * }
     * }
     * ],
     * "suggestions": 0,
     * "first_name": "Player",
     * "last_name": "2",
     * "phone": "1234567890",
     * "parent_email": null,
     * "age_group": null,
     * "positions": [
     * {
     * "id": 3,
     * "name": "Goal Keeper",
     * "lines": 2,
     * "pivot": {
     * "player_id": 219,
     * "position_id": 3
     * },
     * "line": {
     * "id": 2,
     * "name": "GoalKeepers"
     * }
     * }
     * ]
     * }
     * ]
     * }
     **/

    public function index(Request $request)
    {
        /*$clubs = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->get()->pluck('club_id');*/

        $myClubs = (new Club())->myCLubs($request)->original['Result'];

        $myClubsId = [];

        if (count($myClubs) > 0) {
            $myClubsId = array_column($myClubs, 'id');
        }

        $players = User::role('player')
            ->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.profile_picture', 'users.age', 'users.gender', 'phone')
            ->with([
                'teams' => function ($q) {
                    $q->select('teams.id', 'teams.team_name', 'teams.image');
                },
                'player' => function ($q1) {
                    $q1->select('id', 'players.user_id', 'players.position_id');
                },
                'player.positions' => function ($query) {
                    $query->select('positions.id', 'name', 'lines');
                },
                'player.positions.line' => function ($query) {
                    $query->select('lines.id', 'name');
                },
                'leaderboards' => function ($q3) {
                    $q3->select('leaderboards.id', 'leaderboards.user_id', 'leaderboards.total_score');
                }
            ])
            ->whereHas('clubs_players', function ($q) use ($myClubsId) {
                $q->whereIn('club_id', $myClubsId);
            })
            ->orderBy('created_at')
            ->get();


        if (count($players)) {
            $results = $players->map(function ($item) {
                $obj = new \stdClass();

                $obj->id = $item->id;
                $obj->player_name = $item->first_name . ' ' . $item->last_name;
                $obj->profile_picture = $item->profile_picture;
                $obj->age = $item->age;
                $obj->gender = $item->gender;
                $obj->points = $item->leaderboards->total_score ?? 0;
                $obj->teams = $item->teams ?? [];
                $obj->suggestions = 0;

                //For Edit
                $obj->first_name = $item->first_name;
                $obj->last_name = $item->last_name;
                $obj->phone = $item->phone;
                $obj->parent_email = DB::table('parent_players')->where('player_id', $item->id)->value('parent_email') ?? null;
                $obj->gender = $item->gender;
                $obj->age_group = $item->age;
                $obj->positions = $item->player->positions ?? [];

                return $obj;
            });

            return Helper::apiSuccessResponse(true, 'Records  found', $results);
        }


        return Helper::apiNotFoundResponse(false, 'Records  found', []);
    }

    /**
     * Update Team Player
     *
     * @response {
     *
     *       "Response": true,
     *       "StatusCode": 200,
     *       "Message": "Player Updated",
     *       "Result": {
     *           "first_name": "eaa",
     *           "last_name": "uu",
     *           "phone": "+483545781",
     *           "status_id": 1,
     *           "age": "18",
     *           "gender": "woman",
     *           "verified_at": "2021-06-10 08:18:37",
     *           "updated_at": "2021-06-10T08:41:08.000000Z",
     *           "created_at": "2021-06-10T08:18:37.000000Z",
     *           "id": 466,
     *           "parent_email": "abc@test.com",
     *           "roles": [
     *               {
     *                   "id": 1,
     *                   "name": "player",
     *                   "guard_name": "api",
     *                   "created_at": null,
     *                   "updated_at": null,
     *                   "pivot": {
     *                       "model_id": 466,
     *                       "role_id": 1,
     *                       "model_type": "App\\User"
     *                   }
     *               }
     *           ]
     *       }
     *
     * }
     *
     * @bodyParam first_name string required max 191 chars
     * @bodyParam last_name string required max 191 chars
     * @bodyParam team_id integer required
     * @bodyParam gender string required
     * @bodyParam age_group integer
     * @bodyParam player_id integer required
     * @bodyParam parent_email string
     * @bodyParam positionsId array required
     * @bodyParam linesId array required
     * @return JsonResponse
     */

    public function updatePlayer(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'age_group' => 'nullable',
            'gender' => 'required|in:man,woman,mixed',
            'team_id' => 'required|exists:teams,id',
            'player_id' => 'required|exists:users,id',
            'positionsId' => 'required|array',
            'positionsId.*' => 'numeric|exists:positions,id',
            'linesId' => 'required|array',
            'linesId.*' => 'numeric|exists:lines,id,status,active',
            'parent_email' => 'nullable|email'
        ]);
        $player = User::with('roles')->find($request->player_id);
        if (!$player) {
            return Helper::apiNotFoundResponse(false, 'User not found', new stdClass());
        }
        $team_club = DB::table('club_teams')->where('team_id', $request->team_id)->get()->pluck('club_id');
        $player->first_name = $request->first_name;
        $player->last_name = $request->last_name;
        if ($request->age_group) {
            $player->age = $request->age_group;
        }
        $player->gender = $request->gender;
        $player->who_created = Auth::user()->id ?? null;
        $player->save();
        $player->clubs_players()->sync($team_club);
        $player->teams()->sync([$request->team_id]);
        $parent_email = DB::table('parent_players')->where('player_id', $player->id)->value('parent_email');
        $user = new stdClass();
        $user->first_name = $player->first_name;
        $user->last_name = $player->last_name;
        $user->phone = $player->phone;
        $user->status_id = $player->status_id;
        $user->age = $player->age;
        $user->gender = $player->gender;
        $user->verified_at = $player->verified_at;
        $user->updated_at = $player->updated_at;
        $user->created_at = $player->created_at;
        $user->id = $player->id;
        if ($request->parent_email) {
            $user->parent_email = $request->parent_email;
            DB::table('parent_players')
                ->update([
                    'parent_email' => $request->parent_email
                ]);

        } else {
            $user->parent_email = $parent_email;
        }
        $user->roles = $player->roles;

        $user->player->positions()->sync($request->positionsId);

        return Helper::apiSuccessResponse(true, 'Player Updated', $user);
    }

    /**
     * Players/Get Age Groups
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": ["16", "19", "22"]
     * }
     *
     * @return JsonResponse
     */

    public function getAgeGroups()
    {
//        $age_groups = Team::where('age_group', '!=', '')->distinct()->groupBy('age_group')->pluck('age_group');
        $age_groups = [];
        for ($i = 5; $i <= 50; $i++) {
            array_push($age_groups, $i);
        }
//        if($age_groups){
        return Helper::apiSuccessResponse(true, 'Age Groups found', $age_groups);
//        }
//        return Helper::apiSuccessResponse(false, 'Age Groups Not found', []);

    }

    /**
     * Players/Get Genders
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

    public function getGenders()
    {
//        $age_groups = Team::where('age_group', '!=', '')->distinct()->groupBy('age_group')->pluck('age_group');
//        $types = ["man", "woman", "mixed"];
//        if($age_groups){
        $types = Gender::select('id','type')->whereStatus('1')->get();
        return Helper::apiSuccessResponse(true, 'Success', $types);
//        }
//        return Helper::apiSuccessResponse(false, 'Age Groups Not found', []);

    }


    /**
     * Add Team Player
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Players has been created successfully.",
     * "Result": [
     * {
     * "first_name": "Player By F",
     * "last_name": "MF",
     * "date_of_birth": "2020-06-28",
     * "phone": "+923330541785",
     * "status_id": 1,
     * "gender": "man",
     * "verified_at": "2021-06-28T10:40:42.060233Z",
     * "updated_at": "2021-06-28 10:40:42",
     * "created_at": "2021-06-28 10:40:42",
     * "id": 586,
     * "roles": [
     * {
     * "id": 1,
     * "name": "player",
     * "guard_name": "api",
     * "created_at": null,
     * "updated_at": null,
     * "pivot": {
     * "model_id": 586,
     * "role_id": 1,
     * "model_type": "App\\User"
     * }
     * }
     * ]
     * }
     * ]
     * }
     *
     * @bodyParam first_name[] string  max 191 chars required
     * @bodyParam last_name[] string  max 191 chars required
     * @bodyParam country_code[] string required
     * @bodyParam phone[] string  max 191 chars required
     * @bodyParam team_id[] integer required
     * @bodyParam date_of_birth[] date required format 2021-06-25
     * @bodyParam age_group[] integer
     * @bodyParam gender[] integer required gender_id
     * @bodyParam positionsId array required
     * @bodyParam linesId array required
     * @bodyParam parent_email[]  string
     *
     * @return JsonResponse
     */
    public function addPlayer(Request $request)
    {
        $validation = [
//            'team_id' => 'required|array',
            '*.team_id' => 'required|exists:teams,id',
//            'first_name' => 'required|array',
            '*.first_name' => 'required|max:255',
//            'last_name' => 'required|array',
            '*.last_name' => 'required|max:255',
            '*.age_group' => 'nullable',
            '*.gender' => 'required|exists:genders,id',
            '*.date_of_birth' => 'required|date|date_format:Y-m-d',
//            'phone' => 'required|array',
            '*.phone' => 'required',
            '*.country_code' => 'required|numeric|exists:countries,phone_code',
            '*.positionsId' => 'required|array',
            '*.positionsId.*' => 'numeric|exists:positions,id',
            '*.linesId' => 'required|array',
            '*.linesId.*' => 'numeric|exists:lines,id,status,active',
            '*.parent_email' => 'nullable|email'
            //'phone' => 'required',
            //'ip' => 'required|ip',
            //'device_type' => 'required|in:ios,android',
            //'imei' => 'required_if:device_type,android',
            //'udid' => 'required_if:device_type,ios',
        ];
        $validator = Validator::make($request->all(), $validation);
        if ($validator->fails()) {
            return Helper::apiErrorResponse(false, 'Error', $validator->messages()->toArray());
        }
        // $check = PricingPlan::checkAvailability(count($request->all()), 'players');

        // if($check) {
        //     return Helper::apiErrorResponse(true, "Your Plan exceeds the limit, kindly upgrade your plan to add more players.", new stdClass());
        // }

        DB::beginTransaction();
        $users = [];
        $flag = 0;
        $check_limit = '';
        $check = [];
        //$parent= $request->parent_email;
        //return "email: " .$request->parent_email;
        try {
            foreach ($request->all() as $index => $val) {
                $phoneCode = Country::select('id')
                    ->where('phone_code', $val['country_code'])
                    ->first();

                $row = new stdClass();
                $row->team_id = $val['team_id'];
                $row->first_name = $val['first_name'];
                $row->last_name = $val['last_name'];
                $row->country_code_id = $phoneCode->id;
                $row->phone = $val['phone'];
                $row->age = $val['age_group'] ?? null;
                $row->date_of_birth = $val['date_of_birth'];
                $row->gender = $val['gender'];
                $row->ip = '192.0.2.245';
                $row->imei = '490154203237518';
                $row->device_type = 'android';
                if (isset($val['parent_email'])) {
                    $row->parent_email = $val['parent_email'];
                }
                $row->add_explicitly = true;
                $row->club_manager = 1;

                $processCheckLimit = true;

                //SECRET COUPON CHECK
                $teamSubscripion = TeamSubscription::where('team_id', $row->team_id)
                    ->where('status', '1')
                    ->first();

                if ($teamSubscripion && !empty($teamSubscripion->coupon_id)) {
                    $coupon = Coupon::where('id', $teamSubscripion->coupon_id)
                        ->first();

                    if ($coupon && $coupon->code == env('SECRET_COUPON')) {
                        $processCheckLimit = false;
                    }
                }

                if ($processCheckLimit) {
                    $check_limit = PricingPlan::checkLimit($val['team_id'], $row, 'player');

                } else {
                    $check_limit = 0;
                }

                //            $request->request->add(['ip' => '192.0.2.245', 'imei' => '490154203237518', 'device_type' => 'android', 'add_explicitly' => true]);


                if (gettype($check_limit) == 'object') {
                    return $check_limit;
                } else if (gettype($check_limit) == 'array') {
                    array_push($check, $check_limit);
                    $flag = 1;
                } else if (gettype($check_limit) == 'integer') {
                    $flag = 0;
                } else {
                    return $check_limit;
                }

                if ($flag == 0) {


                    /*if ($row->phone[0] != '+') {
                        $row->phone = '+'.$val['country_code'].$row->phone;
                    }*/


                    $user_exist = User::where('country_code_id', $phoneCode->id)
                        ->where('phone', $row->phone)
                        ->first();

                    if ($user_exist) {
                        //!empty($val['id']) && $val['id'] != $user_exist->id
                        if ((!empty($val['id']) && $val['id'] != $user_exist->id) || empty($val['id'])) {
                            return Helper::apiErrorResponse(false, 'Phone number already exists', new stdClass());
                        }
                    }

                    //$user = User::where('phone', $row->phone)
                    $user = User::where('id', $val['id'] ?? 0)
                        ->whereHas('roles', function ($q) {
                            $q->where('roles.name', 'player');
                        })->first();

                    if ($user) {
//                    return Helper::apiErrorResponse(true, 'Phone number already exists', new stdClass());
                        $user->registerPlayer($row);
                        $player = Player::where('user_id', $user->id)->first();
                        //parent_player insertion

                        if (!$player) {
                            $player = new Player();
                        } else {
                            $player->updated_at = now();
                        }

                        $player->user_id = $user->id;
                        //$player->position_id = $val['position'];
                        $player->save();

                        $player->positions()->sync($val['positionsId']);

                        $users[] = $user;
                    } else {

                        $response = Helper::sendOtp("+" .$val["country_code"] .$row->phone);
                        
                        if (gettype($response) == 'string') {
                            activity()->performedOn(new User())->log($response);
                            return Helper::apiErrorResponse(false, $response, new stdClass(), 400);
                        }
                        
                        $user = new User();
                        $user->registerPlayer($row);
                        $player = new Player();
                        $player->user_id = $user->id;
                        //$player->position_id = $val['position'];
                        $player->created_at = now();
                        $player->save();

                        $player->positions()->sync($val['positionsId']);

                        $users[] = $user;
                    }

                    if (isset($val['parent_email'])) {
                        $this->inviteParent($val['parent_email'], $user->id);
                    }
                }

                //SEND CUSTOM MESSAGE TO THE PLAYER
                // Helper::sendCustomMessage($row->phone,"You have been invited to Jogo as a Player, Download Jogo @ https://bit.ly/390efza");
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            return Helper::apiErrorResponse(true, 'Error', $ex->getMessage());
        }
        if ($flag == 0 && empty($check)) {
            DB::commit();
            return Helper::apiSuccessResponse(true, "Players has been created successfully.", $users);
        } else if (!empty($check)) {
            DB::commit();
            return Helper::apiSuccessResponse(true, 'Success but the following Players are not created due to pricing plan restrictions', $check);
        } else {
            return $check_limit;
        }


        /*$this->validate($request,[
            'team_id'=>'required',
            'first_name'=>'required',
            'last_name'=>'required',
            'phone'=>'required'
        ]);
        if ($request->phone[0] != '+') {
            $request->phone = '+' . $request->phone;
        }
        $check_phone = User::where('phone',$request->phone)->first();
        if($check_phone){
            return Helper::apiErrorResponse(true, 'Phone number already exists',new stdClass());
        }
       try{
            DB::transaction(function() use($request){
                $user  = new User();
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->phone = $request->phone;
                $user->save();
                $team = Team::find($request->team_id);
                $user->teams()->sync([$team->id]);
            });
           return Helper::apiSuccessResponse(true, 'Player Added',new stdClass());

       }catch (\Exception $e){
           return Helper::apiErrorResponse(false, 'Failed to add player',new stdClass());

       }*/

    }

    /**
     * Dashboard Bulk Import Players
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Imported Successfully",
     * "Result": {}
     * }
     *
     * @bodyParam csv file required  required
     * @return JsonResponse
     */
    public function bulkImport(Request $request)
    {
        $this->validate($request, [
            'csv' => 'required|max:10000'
        ]);
        \Session::forget('response_players_csv');
        $res = Excel::import(new DashboardPlayersImport, $request->file('csv'));
//        dd($res);
        $res = \Session::get('response_players_csv');
        if ($res == 'success') {
            return Helper::apiSuccessResponse(true, 'Imported Successfully', new \stdClass());
        } elseif ($res == 'limit_exceed') {
            return Helper::apiErrorResponse(false, "Your Plan exceeds the limit, kindly upgrade your plan to add more players.", new stdClass());
        } else {
            return Helper::apiErrorResponse(false, 'Validation Error', $res);
        }
    }

    /**
     * Dashboard Sample Export Players
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Success",
     * "Result": ['media': 'media/sample_csv/sample_players.csv']
     * }
     *
     * @bodyParam csv file required  required
     * @return JsonResponse
     */
    public function sampleExport(Request $request)
    {
        return Helper::apiSuccessResponse(true, 'Success', ['media' => 'media/sample_csv/sample_players.csv']);
    }


    /**
     * reviewPlayer
     *
     * @bodyParam  player_id required player id is required
     * @bodyParam  rating required rating  is required
     * @bodyParam  review optional
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Player reviewed",
     * "Result": {}
     * }
     *
     *
     */
    public function reviewPlayer(Request $request)
    {
        $this->validate($request, [
            'player_id' => 'required',
            'rating' => 'required|lte:5'
        ]);
        $user_teams_trainer = auth()->user()->teams_trainers()->whereHas('players', function ($t) use ($request) {
            $t->where('user_id', $request->player_id);
        })->get();
        if ($user_teams_trainer->count()) {
            $review_exist = Review::where('reviewer_id', auth()->user()->id)->where('reviewed_id', $request->player_id)->first();
            if ($review_exist) {
                return Helper::apiErrorResponse(false, 'Player already reviewed', new \stdClass());
            }
            $review = new Review();
            $review->reviewer_id = auth()->user()->id;
            $review->reviewed_id = $request->player_id;
            $review->rating = $request->rating;
            $review->review = $request->review;
            if ($review->save()) {
                return Helper::apiSuccessResponse(true, 'Player reviewed', new \stdClass());
            }
        }
        return Helper::apiErrorResponse(false, 'You can only rate members of your team', new \stdClass());

    }

    /**
     * Delete Player
     *
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Record has deleted successfully",
     * "Result": {}
     * }
     *
     * @response 500
     * {
     * "Response": false,
     * "StatusCode": 500,
     * "Message": "Something wen't wrong",
     * "Result": {}
     * }
     *
     * @response 404
     * {
     * "Response": false,
     * "StatusCode": 404,
     * "Message": "Invalid Id",
     * "Result": {}
     * }
     **/

    public function delete(Request $request, $id)
    {
        $apiType = 'dashboard';

        $event = $this->userModel->removePlayerTeam($id, $apiType);

        return $event;
    }

    public function inviteParent($email, $id)
    {
        $parent = new stdClass();
        $parent->email = $email;
        $parent->playerId = $id;
        (new User())->inviteParent($parent);
    }
}
