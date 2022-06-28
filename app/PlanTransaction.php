<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlanTransaction extends Model
{
    protected $appends = ['is_expired'];

    public function getIsExpiredAttribute(){
        if($this->created_at) {
            $fdate = date('Y-m-d H:i:s', strtotime($this->created_at));
            $tdate = date('Y-m-d H:i:s');
            $datetime1 = new \DateTime($fdate);
            $datetime2 = new \DateTime($tdate);
            $interval = $datetime1->diff($datetime2);
            $days = $interval->format('%a');
            if($days > 30) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    }

    public function getInvoiceUrlAttribute($value){
        try {
            return (array) json_decode($value);
        } catch(\Exception $ex) {
            return [];
        }
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function club(){
        return $this->belongsTo(Club::class,'club_id');
    }


    public function plan(){
        return $this->belongsTo(PricingPlan::class,'plan_id','id');
    }
    //
}
