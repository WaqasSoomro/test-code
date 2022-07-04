<?php

namespace App;

use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    //



    public function applyCoupon($total_bill){
        if(Carbon::parse($this->valid_from_date)->gt(Carbon::today())){
            return  'Coupon is invalid';
        }
        if(Carbon::today()->gt(Carbon::parse($this->valid_to_date))){
            return  'Coupon expired';
        }
        if($this->quantity<1){
            return 'Coupon Expired';
        }
//        if($total_bill<$this->min_bill){
//            return 'Minimum bill to avail this coupon is '.$this->min_bill;
//        }
        $total_discount = 0;
        if($this->unit === 'amount'){
            $total_discount = $total_bill - $this->discount;
        }else if($this->unit === 'percent'){
            $total_discount = ($this->discount/100)*$total_bill;
        }
        return [
            'code'=>$this->code,
            'discount_unit'=>$this->unit,
            'discount'=>$this->discount,
            'total_bill'=>$total_bill,
            'discount_amount'=>$total_discount,
            'discount_duration'=>$this->discount_trial_days
        ];
    }
}
