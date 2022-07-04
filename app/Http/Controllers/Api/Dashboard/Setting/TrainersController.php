<?php

namespace App\Http\Controllers\Api\Dashboard\Setting;

use App\Club;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Imports\DashboardTeamsImport;
use App\Imports\DashboardTrainersImport;
use App\PlanTransaction;
use App\Team;
use App\User;
use App\PricingPlan;
use App\UserSubscription;
use App\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;
use Exception;

/**
 * @group Dashboard / Settings
 * APIs for dashboard settings
 */
class TrainersController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * getTabsTrack
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Tabs found",
     * "Result": {
     * "club": 1,
     * "teams": "1",
     * "trainers": "1",
     * "players": "0",
     * "plans": "0"
     * }
     *
     *
     * }
     *
     * @return JsonResponse
     */


    public function getTabsTrack()
    {
        $res = [
            'club' => 0,
            'teams' => 0,
            'trainers' => 0,
            'players' => 0,
            'plans' => 0
        ];

        $club = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->first();
        if (!$club) {
            return Helper::apiSuccessResponse(true, 'Tabs found', $res);
        }
        $res['club'] = 1;
        $club_id = $club->club_id ?? 0;

        $teams = Team::whereHas('clubs',function($q) use ($club_id){
            return $q->where('club_id',$club_id);
        })->withCount('players')->get();

        if (count($teams) <= 0) {
            return Helper::apiSuccessResponse(true, 'Tabs found', $res);
        }
        $res['teams'] = 1;

        $trainers = User::select('id', 'first_name', 'last_name', 'middle_name', 'profile_picture')->whereHas('clubs_trainers', function ($q) use ($club_id) {
            return $q->where('club_id', $club_id);
        })->with([
            'teams' => function ($t) {
                $t->withCount('players');
            }
        ])->get();
        if (count($trainers) <= 0) {
            return Helper::apiSuccessResponse(true, 'Tabs found', $res);
        }
        $res['trainers'] = 1;

        $players = User::role('player')->whereHas('clubs_players', function ($q) use ($club_id) {
            $q->where('club_id', $club_id);
        })->get();

        if (count($players) <= 0) {
            return Helper::apiSuccessResponse(true, 'Tabs found', $res);
        }
        $res['players'] = 1;

        $transaction = PlanTransaction::where('user_id',auth()->user()->id)->latest()->first();

        if(!$transaction || $transaction->is_expired == 1) {
            return Helper::apiSuccessResponse(true, 'Tabs found', $res);
        }
        $res['plans'] = 1;

        return Helper::apiSuccessResponse(true, 'Tabs found', $res);
    }

    /**
     * Get Club Trainers
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Trainers found",
     * "Result": [
     * {
     * "id": 6,
     * "first_name": "JOGO",
     * "last_name": "Trainer",
     * "middle_name": null,
     * "profile_picture": "media/users/kb4hWeG5OEf0MY1E0wI9ywAVw8DrBYthg35azVG6.png",
     * "teams": [
     * {
     * "id": 5,
     * "team_name": "JOGO",
     * "age_group": null,
     * "image": "https://jogobucket.s3.eu-west-2.amazonaws.com/media/users/ic_launcher_APP.png",
     * "description": "Together we revolutionise the world of football and help youth players reach their full potential\r\n",
     * "created_at": null,
     * "updated_at": null,
     * "deleted_at": null,
     * "players_count": 79,
     * "pivot": {
     * "user_id": 6,
     * "team_id": 5,
     * "created_at": "2020-11-05 19:09:54"
     * }
     * }
     * ]
     * }
     *
     * ]
     *
     *
     * }
     *
     * @return JsonResponse
     */


    public function index()
    {
        $clubs = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->get()->pluck('club_id');
        if (count($clubs) <= 0) {
            return Helper::apiErrorResponse(false, 'Club not found', new stdClass());
        }

        //$club_id = $club->club_id ?? 0;
        $trainers = User::select('id', 'first_name', 'last_name', 'middle_name', 'profile_picture')->whereHas('clubs_trainers', function ($q) use ($clubs) {
            return $q->whereIn('club_id', $clubs);
        })->with([
            'teams' => function ($t) {
                $t->withCount('players');
            }
        ])->get();
        if ($trainers->count()) {
            $results = $trainers->map(function ($trainer) {
                $obj = new stdClass();
                $obj->id = $trainer->id;
                $obj->trainer_name = $trainer->first_name . ' ' . $trainer->last_name;
                $obj->total_payers = $trainer->teams->sum('players_count');
                $obj->teams = $trainer->teams->map(function ($team) {
                    return [
                        'id' => $team->id,
                        'name' => $team->team_name,
                        'players_count' => $team->players_count
                    ];
                });
                return $obj;
            });
            return Helper::apiSuccessResponse(true, 'Trainers found', $results);
        }
        return Helper::apiSuccessResponse(false, 'Trainers not found', new stdClass());
    }


    /**
     * Get Club Trainer Details
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Trainer Details",
     * "Result": {
     * "id": 6,
     * "first_name": "JOGO",
     * "middle_name": null,
     * "last_name": "Trainer",
     * "profile_picture": "media/users/kb4hWeG5OEf0MY1E0wI9ywAVw8DrBYthg35azVG6.png",
     * "phone": "+923420203535",
     * "email": "trainer@jogo.ai",
     * "trainer_permissions": {
     * "skill_assignment_access": {
     * "sk_edit_skill_assignment": false,
     * "sk_create_skill_assignment": false,
     * "sk_assign_skill_assignment": false,
     * "sk_see_assignment_videos": false,
     * "sk_writing_comments": false
     * },
     * "player_database_access": {
     * "pd_access_all_players": false,
     * "pd_access_only_team_players": false,
     * "pd_see_player_videos": false,
     * "pd_writing_comments": false
     * }
     * },
     * "teams": [
     * {
     * "id": 5,
     * "team_name": "JOGO",
     * "age_group": null,
     * "image": "https://jogobucket.s3.eu-west-2.amazonaws.com/media/users/ic_launcher_APP.png",
     * "description": "Together we revolutionise the world of football and help youth players reach their full potential\r\n",
     * "created_at": null,
     * "updated_at": null,
     * "deleted_at": null,
     * "pivot": {
     * "user_id": 6,
     * "team_id": 5,
     * "created_at": "2020-11-05 19:09:54"
     * }
     * }
     * ],
     * "permissions": [],
     * "roles": [
     * {
     * "id": 2,
     * "name": "trainer",
     * "guard_name": "api",
     * "created_at": null,
     * "updated_at": null,
     * "pivot": {
     * "model_id": 6,
     * "role_id": 2,
     * "model_type": "App\\User"
     * }
     * }
     * ]
     * }
     * }
     *
     * @return JsonResponse
     */

    public function trainerDetails($id)
    {
        $trainer = User::with('teams_trainers')->select('id', 'first_name', 'middle_name', 'last_name', 'profile_picture', 'phone', 'email')->find($id);
        if (!$trainer) {
            return Helper::apiErrorResponse(false, 'Trainer not found', new stdClass());
        }
        $trainer->rank=$trainer->id;
        $permissions = [
            'skill_assignment_access' => [
                'sk_edit_skill_assignment' => $trainer->hasPermissionTo('sk_edit_skill_assignment'),
                'sk_create_skill_assignment' => $trainer->hasPermissionTo('sk_create_skill_assignment'),
                'sk_assign_skill_assignment' => $trainer->hasPermissionTo('sk_assign_skill_assignment'),
                'sk_see_assignment_videos' => $trainer->hasPermissionTo('sk_see_assignment_videos'),
                'sk_writing_comments' => $trainer->hasPermissionTo('sk_writing_comments'),
            ],
            'player_database_access' => [
                'pd_access_all_players' => $trainer->hasPermissionTo('pd_access_all_players'),
                'pd_access_only_team_players' => $trainer->hasPermissionTo('pd_access_only_team_players'),
                'pd_see_player_videos' => $trainer->hasPermissionTo('pd_see_player_videos'),
                'pd_writing_comments' => $trainer->hasPermissionTo('pd_writing_comments'),
            ],
        ];

        $trainer->trainer_permissions = collect($permissions);
        return Helper::apiSuccessResponse(true, 'Trainer Details', $trainer);

    }


    /**
     * Update Trainer
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Trainer updated",
     * "Result": {}
     * }
     *
     * @bodyParam first_name string required max 191 chars
     * @bodyParam last_name string required max 191 chars
     * @bodyParam phone string required max 191 chars required
     * @bodyParam teams  array required eg:[2,4]
     * @bodyParam permissions  array required eg:[sk_create_skill_assignment,sk_edit_skill_assignment,sk_assign_skill_assignment,sk_see_assignment_videos,sk_writing_comments,pd_access_all_players,pd_access_only_team_players,pd_see_player_videos,pd_writing_comments]
     * @bodyParam trainer_id  integer required
     * @return JsonResponse
     */


    public function updateTrainer(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'teams' => 'required|array',
            'permissions' => 'required|array',
            'trainer_id' => 'required'
        ]);
        $trainer = User::find($request->trainer_id);
        if(!$trainer){
            return Helper::apiNotFoundResponse(false, 'User not found', new stdClass());
        }
        $trainer->first_name = $request->first_name;
        $trainer->last_name = $request->last_name;
        $trainer->middle_name = $request->middle_name;
        $trainer->who_created = Auth::user()->id ?? null;
        $trainer->phone = $request->phone;
        $trainer->save();
        $trainer->syncPermissions($request->permissions);
        $trainer->teams_trainers()->sync($request->teams);

        /*$response = Helper::updateContact($trainer,$trainer->id);
        Helper::leadResponse($response,'trainer',$trainer->id);*/
        return Helper::apiSuccessResponse(true, 'Trainer Updated', []);
    }


    /**
     * Add Trainer
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Trainer Created",
     * "Result": {}
     * }
     *
     * @bodyParam first_name string required max 191 chars
     * @bodyParam last_name string required max 191 chars
     * @bodyParam surname string nullable max 191 chars
     * @bodyParam email string required max 191 chars
     * @bodyParam phone string required max 191 chars required
     * @bodyParam nationality_id string required
     * @bodyParam teams  array required [1,3]
     * @bodyParam permission  array required
     * @return JsonResponse
     */

    public function addTrainer(Request $request)
    {

        Validator::make($request->all(), [
            '*.first_name' => 'nullable|max:191',
            '*.last_name' => 'nullable|max:191',
            '*.email' => 'required|max:191|unique:users',
            '*.teams' => 'required|array||exists:teams,id'
        ])->validate();

        $club = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->first();

        if (!$club) {
            return Helper::apiErrorResponse(false, 'Club not found', new stdClass());
        }
        $check = PricingPlan::checkAvailability(count($request->all()), 'trainers');

        if($check) {
            return Helper::apiErrorResponse(true, "Your Plan exceeds the limit, kindly upgrade your plan to add more trainers.", new stdClass());
        }

        DB::beginTransaction();
        $users = [];
        try {
            foreach ($request->all() as $index => $val) {
                $row = new stdClass();
                $row->teams = $val['teams'];
                $row->first_name = $val['first_name'];
                $row->last_name = $val['last_name'];
                $row->surname = '';
                $row->email = $val['email'];
                $row->password = 'jogo123';
                $row->ip = '192.0.2.245';
                $row->imei = '490154203237518';
                $row->mac_id = '490154203237518';
                $row->device_type = 'web';
                $row->add_explicitly = true;
                $row->nationality_id = 152;
                $otp_code = Helper::generateOtp();
                $row->verification_code = $otp_code;
                $user = new User();
                $user = $user->registerWebUser($row, false);
                $user->teams_trainers()->sync($val['teams']);
                $user->clubs_trainers()->sync($club->club_id);
                $users[] = $user;

                $mailData = [
                    'user' => $user
                ];
                
                try {
                    $sendEmail = Helper::sendMail('emails.send_reset_link', 'Welcome to JOGO', $mailData, $user);
                } catch (Exception $e) {
                    activity()->causedBy($user)->performedOn($user)->log($e->getMessage());
                }
            }
        } catch(\Exception $ex) {
            DB::rollBack();
            return Helper::apiErrorResponse(true, 'Error', $ex);
        }
        DB::commit();
//        $this->addContact($users,$club->club_id);
        return Helper::apiSuccessResponse(true, "Trainer has been created successfully.", $users);
    }


    /**
     * Dashboard Bulk Import Trainers
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
        $res = Excel::import(new DashboardTrainersImport(),$request->file('csv'));

        //dd($res);

        $res = \Session::get('response_team_csv');
        if($res == 'success') {
            return Helper::apiSuccessResponse(true, 'Imported Successfully', new \stdClass());
        } else {
            return Helper::apiErrorResponse(false, 'Validation Error', $res);
        }
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

        $event = $this->userModel->deleteTrainer($id, $apiType);

        return $event;
    }
}