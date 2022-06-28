<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamSubscription extends Model
{
    public function plan(){
        return $this->belongsTo(PricingPlan::class,'plan_id','id')->with('role');
    }
}
