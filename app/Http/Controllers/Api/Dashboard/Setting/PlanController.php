<?php

namespace App\Http\Controllers\Api\Dashboard\Setting;

use App\Coupon;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\PricingPlan;
use App\TeamSubscription;
use App\User;
use App\UserPackage;
use App\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{


    /**
     * Get Plans
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Pricing plans found",
    "Result": [
    {
    "id": 1,
    "name": "Football Academy",
    "min_players": 10,
    "max_players": 100,
    "min_sensors": 10,
    "max_sensors": 100,
    "price_per_player": 10,
    "price_per_sensor": 15,
    "created_at": "2020-12-15 23:09:42",
    "updated_at": null,
    "is_purchased": 1
    }
    ]
    }
     *
     * @return JsonResponse
     */
    public function getPlans(){
        $plans = PricingPlan::orderBy('id', 'asc')->where('name','!=','Shinguard')->get();
        if($plans->count()){
             return Helper::apiSuccessResponse(true, 'Pricing plans found', $plans);
        }
        return Helper::apiErrorResponse(false, 'No plans found', []);
    }






    /**
     * RedeemCoupon
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Coupon applied",
    "Result": {
    "code": "MB40",
    "discount_unit": "percent",
    "discount": 12,
    "total_bill": "200",
    "discount_amount": 24
    }
    }
     *
     * @bodyParam club_id string required
     * @bodyParam coupon_code  string required
     * @bodyParam team_id  integer required
     * @return JsonResponse
     */
    public function redeemCoupon(Request $request){
        $this->validate($request,[
            'coupon_code'=>'required',
            'team_id'=>'required|exists:teams,id',
            'club_id'=>'required|exists:clubs,id',
//            'total_bill'=>'required'
        ]);

//        $total_bill = $request->total_bill;
//        $coupon = Coupon::where('code',$request->coupon_code)->first();
//        if(!$coupon){
//            return Helper::apiErrorResponse(false,'Invalid Coupon', new \stdClass());
//        }
//
//        $apply_coupon = $coupon->applyCoupon($total_bill);
//        if(gettype($apply_coupon)==='string'){
//            return Helper::apiErrorResponse(false,$apply_coupon, new \stdClass());
//        }
        $couponStatus = true;
        $msg = '';
        $coupon = Coupon::whereCode($request->coupon_code)->first();
        if(empty($coupon)){
            $couponStatus = false;
            $msg ='Coupon not found';
        }else{
            $today = date("Y-m-d");
            $expire = $coupon->valid_to_date; //from database
            $start = $coupon->valid_from_date; //from database

            $today_dt = new \DateTime($today);
            $start_dt = new \DateTime($start);
            $expire_dt = new \DateTime($expire);

            if($start_dt >= $today_dt){
                $couponStatus = false;
                $msg ='Coupon not started yet';
            }

            if ($expire_dt < $today_dt) {
                $couponStatus = false;
                $msg ='Coupon expire';
            }

            if($coupon->quantity == 0){
                $couponStatus = false;
                $msg ='Coupon limits exceded';
            }
        }

        if($couponStatus){
            $user = User::whereId(Auth::user()->id)->first();
            $coupon->quantity = $coupon->quantity - 1;
            $coupon->save();
            $team_subscription = new TeamSubscription();
            $team_subscription->team_id = $request->team_id;
            $team_subscription->plan_id = PricingPlan::whereName('Communication')->first()->id;
            $team_subscription->coupon_id = $coupon->id;
            $team_subscription->start_date = date('Y-m-d');
            $team_subscription->end_date = date('Y-m-d',strtotime('+ 30 days'));
            $team_subscription->save();

            $user->access_type = Helper::checkTeamUpgradation($request->club_id);
            $user->permissions = !empty($user->access_type) ? Helper::getPermissions($user->access_type) : [];

            return Helper::apiSuccessResponse(true, 'Coupon applied successfully', $user);
        }
        else
        {
            return Helper::apiErrorResponse(false, $msg, []);
        }
    }


    public function calculateDiscount($total_bill,$discount,$unit='amount'){
        $total_discount = 0;
        if($unit === 'amount'){
            $total_discount = $total_bill - $discount;
        }else if($unit === 'percent'){
            $total_discount = ($discount/$total_bill)*100;
        }
        return $total_discount;
    }


    //
}
