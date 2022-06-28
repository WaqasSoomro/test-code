<?php
namespace App\Http\Controllers\Api\V3Dashboard;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Clubs\Trainers\DeleteRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * @group Dashboard V3 / Settings
 * APIs for V3 dashboard settings
 */
class TrainerController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = User::class;
    }

    /**
        Delete A Trainer

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "You've successfully deleted your trainer",
            "Result": {}
        }

        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid trainer id",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @bodyParam clubId integer required .Example: 1
        @bodyParam trainer_id integer required .Example: 1
    */

    public function deleteTrainer(DeleteRequest $request)
    {
        $response = (new $this->userModel)->remove($request, $request->trainer_id);

        return $response;
    }

    /**
     * Add / Remove Trainer
     * @response
     * {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Successfully Remove Trainers",
     * "Result": {}
     * }
     * @bodyParam trainer_ids[] array required
     * @bodyParam team_id integer required
     */
    public function add_or_remove_trainer(Request $request){

        $request->validate([
            "trainer_ids"=>"required|array",
            "team_id"=>"required|integer"
        ]);

        $trainer_ids = array_map('intval', $request->trainer_ids); // EXTRACTING TRAINER IDS
        $team_id = $request->team_id; // EXTRACTING TEAM ID

        DB::beginTransaction();
        $team_trainer = DB::table("team_trainers")->where("team_id",$team_id)->whereNotIn("trainer_user_id",$trainer_ids)->pluck("trainer_user_id")->toArray();

        try{
            foreach ($team_trainer as $tid){
                //FIRST FIND THE TRAINER
                $trainer = User::find($tid);

                // IF TRAINER NOT FOUND
                if (!$trainer)
                {
                    DB::rollBack();
                    return Helper::apiNotFoundResponse(false,"Trainer not found",new \stdClass());
                }

                // IF TRAINER FOUND
                $trainer->teams_trainers()->detach($team_id); // REMOVE THAT TRAINER FROM THE TEAM
            }
            // IF SUCCESS THEN
            DB::commit();
            return Helper::apiSuccessResponse(true,"Successfully Remove Trainers", new \stdClass());
        }catch (\Exception $e){
            DB::rollBack();
            return Helper::apiErrorResponse(false,"Something Went Wrong",new \stdClass());
        }
    }
}
