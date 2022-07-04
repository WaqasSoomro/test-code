<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use App\User;
class CountryCodeController extends Controller
{
    //
    public function country_code()
    {
        $phone_numbers = User::pluck('phone')->toArray();
        
        $arr = [];
        $country_code = '';
        for($i=0;$i<count($phone_numbers);$i++)
        {
            if(!empty($phone_numbers[$i]))
            {
                $arr[$i] = substr($phone_numbers[$i],0,1);

                if($arr[$i]==='0')                     
                {   
                    $new_num = substr($phone_numbers[$i],1);
                    User::where('phone',$phone_numbers[$i])->update(['phone'=>$new_num]);
                }
                for($j=4;$j>=1;$j--)
                {
                    $country_code = substr($phone_numbers[$i],1,$j);
                    $check = DB::table('countries')->select('phone_code','id')->where('phone_code',$country_code)->first();
                    
                    if($check != null)
                    {   
                        $country_code = substr($phone_numbers[$i],0,$j+1);
                        User::where('phone',$phone_numbers[$i])->update(['country_code_id'=>$check->id]);
                        $new_num = substr($phone_numbers[$i],strlen($country_code));
                        User::where('phone',$phone_numbers[$i])->update(['phone'=>$new_num]);
                    }
                }

                
                
            }
        
        }

        return Helper::apiSuccessResponse(true, 'Success', []);
    }

}
