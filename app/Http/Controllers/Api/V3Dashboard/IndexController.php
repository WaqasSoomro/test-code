<?php

namespace App\Http\Controllers\Api\V3Dashboard;

use App\Club;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


/**
 * @group Dashboard V3 / Settings
 * APIs for V3 dashboard settings
 */
class IndexController extends Controller
{
    /**
     * Get All Clubs
     * @response
     * {
     *
     * }
     */
    public function get_clubs(){

        // CHECK IF THE TRAINER EXISTS
        $trainer = User::where("id",auth()->user()->id)->first();
        if (!$trainer)
        {
            return Helper::apiNotFoundResponse(false,"Trainer Not Found",new \stdClass());
        }

        // CHECK IF THAT TRAINER IS IN ANY CLUB
        $clubs_ids = DB::table("club_trainers")->where("trainer_user_id",auth()->user()->id)->pluck("club_id");
        // IF TRAINER HAS NOT CLUB
        if (count($clubs_ids) == 0)
        {
            return Helper::apiNotFoundResponse(false,"No Clubs Found",new \stdClass());
        }
        // IF CLUBS FOUND THEN GET REQUIRED CLUB INFOs
        $clubs = Club::select(["id","title as club_name","image as club_logo"])->whereIn("id",$clubs_ids)->get();
        return Helper::apiSuccessResponse(true,"Success",$clubs);

    }

}
