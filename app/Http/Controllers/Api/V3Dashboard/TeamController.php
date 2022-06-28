<?php

namespace App\Http\Controllers\Api\V3Dashboard;

use App\Club;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User;
use App\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Dashboard V3 / Settings
 * APIs for V3 dashboard settings
 */
class TeamController extends Controller
{
    /**
     * Delete A Team
     *
     * @response
    {
    "Response": true,
    "StatusCode": 200,
    "Message": "Team Deleted Successfully",
    "Result": {}
    }
     *
     * @bodyParam team_id integer required
     * @bodyParam club_id integer required
     *
     */

    public function delete_team(Request $request)
    {
        $request->validate([
            "team_id"=>"required|integer",
            "club_id"=>"required|integer"
        ]);


        // CHECK IF THE TRAINER IS IN THE CLUB OR NOT.
        $myClubs = (new Club())->myCLubs($request)->original['Result'];
        
        if (!in_array($request->club_id, array_column($myClubs, 'id')))
        {
            return Helper::apiErrorResponse(false, 'Add Club First', new \stdClass());
        }

       $team = Team::whereHas("trainers",function ($query) use
       ($request)
       {
           $query->where("trainer_user_id",auth()->user()->id)->where("team_id",$request->team_id);
       })->first();


       if (!$team)
       {
           return Helper::apiNotFoundResponse(false,"Trainer Not Added In The Team", new \stdClass());
       }

       try{
           DB::transaction(function ()
               use ($team)
           {
               // IF THE TRAINER IS IN THE TEAM
               $team->trainers()->detach(); // delete trainer from that team
               $team->users()->detach(); // DELETE PLAYERS OF THAT TEAM
               $team->clubs()->detach(); // DELETE TEAM FROM THE CLUB
               $team->exercises()->detach(); // DELETE EXERCISES RELATED TO THAT TEAM
               $team->subscription()->delete(); // DELETE SUBSCRIPTION OF THAT TEAM
               $team->delete(); // DELETE THE TEAM ITSELF
           });
       }
       catch (\Exception $e)
       {
            return Helper::apiErrorResponse(false,"Something Went Wrong",new \stdClass());
       }

       return Helper::apiSuccessResponse(true,"Team Deleted Successfully", new \stdClass());

    }
}
