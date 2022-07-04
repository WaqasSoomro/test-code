<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    public function plan(){
        return $this->belongsTo(PricingPlan::class,'package_id','id')->with('role');
    }
}
