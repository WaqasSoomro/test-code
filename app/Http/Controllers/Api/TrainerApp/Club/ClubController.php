<?php

namespace App\Http\Controllers\Api\TrainerApp\Club;

use App\Club;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User;
use App\SelectedClub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ParagonIE\Sodium\Core\Curve25519\H;

/**
 * @group TrainerApp V4 / Club
 * API FOR TRAINERAPP CLUB
 */
class ClubController extends Controller
{
    /**
     * Get All Trainer's Club
     *
    @response{
    "Response": true,
    "StatusCode": 200,
    "Message": "Records found",
    "Result": [
    {
    "id": 8,
    "name": "Club",
    },
    {
    "id": 3,
    "name": "Football Academy Finn Amsterdam",
    },
    {
    "id": 2,
    "name": "teaaamwqw",
    }
    ]
    }
     */
    public function get_trainer_clubs(Request $request){
        return (new Club())->myCLubs($request,"trainerApp");
    }

    /**
     * Save Selected Trainer Club
     *
     * @response
    {
    "Response": true,
    "StatusCode": 200,
    "Message": "Club Set As Active",
    "Result": {}
    }
     *
     * @bodyParam club_id integer required
     */
    public function save_selected_club(Request $request)
    {
        $request->validate([
           "club_id"=>"required"
        ]);

        // GET ALL THE TRAINER CLUBS
        $get_all_trainer_clubs = (new Club())->myCLubs($request);


        // IF CLUB COUNT IS ZERO
        if (count($get_all_trainer_clubs->original["Result"]) == 0)
        {
            return Helper::apiNotFoundResponse(false,"No Club Found", new \stdClass());
        }

        // EXTRACT ALL CLUB IDS
        $club_ids = [];
        foreach ($get_all_trainer_clubs->original["Result"] as $value){
            $club_ids[] = $value["id"];
        }

        // CHECK IF THE REQUEST CLUB ID IS IN THE CLUB IDS ARRAY. IF NOT THEN THE TRAINER IS NOT IN THE CLUB
        if (!in_array($request->club_id,$club_ids))
        {
            return Helper::apiNotFoundResponse(false,"Trianer Or Owner Not In The Selected Club", new \stdClass());
        }

        try{
            DB::transaction(function ()
            use($request)
            {
                // IF THE LOGGED IN TRAINER ALREADY SELECTED GROUP
                $select_club = SelectedClub::where("trainer_user_id",auth()->user()->id)->first();

                // IF TRUE THEN UPDATE THE NEW SELECTED CLUB
                if ($select_club){
                    $select_club->update([
                        "club_id"=>$request->club_id,
                        "trainer_user_id"=>auth()->user()->id
                    ]);
                    return Helper::apiSuccessResponse(true,"Club Saved As Active",$select_club);
                }

                DB::table("selected_clubs")->insert([
                    "club_id"=>$request->club_id,
                    "trainer_user_id"=>auth()->user()->id
                ]);
            });
        }catch (\Exception $e)
        {
            return Helper::apiErrorResponse(false,"Something Went Wrong", new \stdClass());
        }

        return Helper::apiSuccessResponse(true,"Club Saved As Active", new \stdClass());
    }
}