<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Helpers\Helper;
use stdClass;
class PricingPlan extends Model
{
    //
    protected $table='pricing_plans';

    protected $appends = ['is_purchased', 'monthly_discount_amount', 'yearly_discount_amount','days_left'];

    public function getIsPurchasedAttribute() {
//        $plan = PlanTransaction::where('user_id', \Auth::user()->id ?? 0)->where('payment_status', 'paid')->orderBy('created_at', 'desc')->first();

//        if(empty($plan) && $this->name == 'Freemium') {
//            return 1;
//        }
//
//        if(!empty($plan) && $plan->plan_id == $this->id) {
//            return 1;
//        }

        $team_package = Helper::checkTeamUpgradation();

        if(strtolower($this->name) == $team_package){
            return 1;
        }

        return 0;
    }

    public function getDaysLeftAttribute()
    {
        if(Auth::user()->hasrole('lite') && strtolower($this->name) == 'lite user'){
            $subscription = UserSubscription::whereUserId(Auth::user()->id)->orderBy('id','DESC')->first();
            $now = time(); // or your date as well
            $your_date = strtotime($subscription->end_date);
            $datediff = $your_date - $now;
            return round($datediff / (60 * 60 * 24));
        }

        return 0;
    }

    public function getMonthlyDiscountAmountAttribute()
    {
        if($this->monthly_discount) {
            $discount = ($this->price_per_month / 100) * $this->monthly_discount;

            return $discount ?? 0;
        }

        return 0;
    }

    public function getYearlyDiscountAmountAttribute()
    {
        if($this->yearly_discount) {
            $discount = ($this->price_per_year / 100) * $this->yearly_discount;

            return $discount ?? 0;
        }

        return 0;
    }
    public static function checkLimit($team_id,$content,$type){
        
        $team_detail = TeamSubscription::whereTeamIdAndStatus($team_id,'1')->first();
        
        if(!$team_detail)
        {
            $team_plan = '1';
        }else{
            $team_plan = $team_detail->plan_id;
        }

        $plan = PricingPlan::whereId($team_plan)->first();
        $arr = [];
        if($type == 'trainer')
        {
           
            $number_of_trainers = DB::table('team_trainers')
            ->where('team_id',$team_id)
            ->count();                   
            $flag = 0;
            $exceed  = new stdClass();                 
    
            if($number_of_trainers > ($plan->max_trainers - 1))
            {   $exceed->firstname = $content->first_name;
                $exceed->lastname= $content->last_name;
                $exceed->email = $content->email;
                $exceed->team_id = $team_id;
                $arr[] = $exceed;
                return $arr;
            }
            return 0;
        }
        else
        {   
            $exceed  = new stdClass();                 

            $number_of_players = DB::table('player_team')->where('team_id',$team_id)->count();
            if($number_of_players > ($plan->max_players - 1))
            {   $exceed->firstname = $content->first_name;
                $exceed->lastname= $content->last_name;
                $exceed->gender = $content->gender;
                $exceed->date_of_birth = $content->date_of_birth;
                $exceed->team_id = $team_id;
                $arr[] = $exceed;
                
                return $arr;
            }
            return 0;
            

        }
       
    }

    public static function checkAvailability($count_type, $type) {
        return 0;
        $transaction = PlanTransaction::where('user_id', \Auth::user()->id)->where('payment_status', 'paid')->orderBy('created_at', 'desc')->first();
        if(!$transaction) {
            $plan = PricingPlan::where('name', 'Freemium')->first();
            if(!$plan) {
                return 1;
            }
        }

        if($transaction && $transaction->created_at) {
            $fdate = date('Y-m-d H:i:s', strtotime($transaction->created_at));
            $tdate = date('Y-m-d H:i:s');
            $datetime1 = new \DateTime($fdate);
            $datetime2 = new \DateTime($tdate);
            $interval = $datetime1->diff($datetime2);
            $days = $interval->format('%a');
            if($days > 30) {
                $plan = PricingPlan::where('name', 'Freemium')->first();
                if(!$plan) {
                    return 1;
                }
            } else {
                $plan = $transaction->plan;
                if(!$plan) {
                    $plan = PricingPlan::where('name', 'Freemium')->first();
                    if(!$plan) {
                        return 1;
                    }
                }
            }
        } else {
            $plan = PricingPlan::where('name', 'Freemium')->first();
            if(!$plan) {
                return 1;
            }
        }

        $clubs = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->get()->pluck('club_id');

        if($type == 'players') {
            $players = User::role('player')
                ->whereHas('clubs_players', function ($q) use ($clubs) {
                    $q->whereIn('club_id', $clubs);
                })->count();
            $remaining = $plan->max_players - $players;
            if($remaining >= $count_type) {
                return 0;
            } else {
                return 1;
            }
        } else {
            $trainers = User::whereHas('clubs_trainers', function ($q) use ($clubs) {
                    $q->whereIn('club_id', $clubs);
                })->count();
            $remaining = $plan->max_trainers - $trainers;
            if($remaining >= $count_type) {
                return 0;
            } else {
                return 1;
            }
        }
    }

    public function role(){
        return $this->belongsTo(Role::class,'role_id','id');
    }
}
