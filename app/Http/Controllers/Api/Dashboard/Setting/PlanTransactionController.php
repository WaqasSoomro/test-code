<?php

namespace App\Http\Controllers\Api\Dashboard\Setting;

use App\Coupon;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\PlanTransaction;
use App\Helpers\Payment;
use App\PricingPlan;
use App\StripeCustomer;
use App\Team;
use App\TeamSubscription;
use App\UserCoupon;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\AdyenClient;
use Spatie\Permission\Models\Role;

class PlanTransactionController extends Controller
{
    //



    /**
     * Get Billings
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Billing records found",
    "Result": [
    {
    "id": 1,
    "plan_id": 1,
    "total_sensors": 15,
    "total_players": 20,
    "total_bill": 225,
    "discount": 0,
    "coupon": null,
    "grand_total": 225,
    "created_at": "2020-12-15 18:49:13",
    "updated_at": "2020-12-15 18:49:13",
    "payment_status": "unpaid",
    "payment_response": null,
    "user_id": 40,
    "club_id": 2,
    "user": {
    "id": 40,
    "first_name": "Umer Shaikh",
    "middle_name": null,
    "last_name": null,
    "email": "umer@jogo.ai",
    "profile_picture": "media/users/5Fa9RZ45odWg292sWOZEOm19S9HAAuWD5Iee6CBf.png"
    },
    "club": {
    "id": 2,
    "title": "JOGO"
    },
    "plan": {
    "id": 1,
    "name": "Football Academy"
    }
    },
    {
    "id": 2,
    "plan_id": 1,
    "total_sensors": 15,
    "total_players": 20,
    "total_bill": 225,
    "discount": 27,
    "coupon": "MB40",
    "grand_total": 198,
    "created_at": "2020-12-15 18:50:40",
    "updated_at": "2020-12-15 18:50:40",
    "payment_status": "unpaid",
    "payment_response": null,
    "user_id": 40,
    "club_id": 2,
    "user": {
    "id": 40,
    "first_name": "Umer Shaikh",
    "middle_name": null,
    "last_name": null,
    "email": "umer@jogo.ai",
    "profile_picture": "media/users/5Fa9RZ45odWg292sWOZEOm19S9HAAuWD5Iee6CBf.png"
    },
    "club": {
    "id": 2,
    "title": "JOGO"
    },
    "plan": {
    "id": 1,
    "name": "Football Academy"
    }
    }
    ]
    }
     *
     * @return JsonResponse
     */
    public function transactions(){
        $transactions = PlanTransaction::with('user:id,users.first_name,users.middle_name,users.last_name,users.email,users.profile_picture','club:id,clubs.title','plan:id,pricing_plans.name')->latest()->where('user_id',auth()->user()->id)->get();
        if($transactions->count()){
            return Helper::apiSuccessResponse(true, 'Billing records found', $transactions);
        }
        return Helper::apiErrorResponse(false, 'No records found', []);
    }



    /**
     * PurchasePlanOLD
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "Success",
    "Result": {}
    }
     *
     * @bodyParam plan_id integer required
     * @bodyParam total_players integer required
     * @bodyParam total_sensors integer required
     * @bodyParam card_number required
     * @bodyParam expiry required
     * @bodyParam cvc integer required
     * @bodyParam coupon string optional max 191 chars required
     * @return JsonResponse
     */

    public function purchasePlan(Request $request){
        $this->validate($request,[
            'plan_id'=>'required',
//            'total_players'=>'required',
            'total_sensors'=>'required',
//            'card_number' => 'required',
//            'expiry' => 'required',
//            'cvc' => 'required'
        ]);
        $plan = PricingPlan::find($request->plan_id);
        if(!$plan){
            return Helper::apiErrorResponse(false,'Invalid Plan', new \stdClass());
        }
//        if($request->total_players > $plan->max_players){
//            return Helper::apiErrorResponse(false,'Max players for this plan should be '.$plan->max_players, new \stdClass());
//        }
        if($request->total_sensors > $plan->max_sensors){
            return Helper::apiErrorResponse(false,'Max sensors for this plan should be '.$plan->max_sensors, new \stdClass());
        }
//        if($request->total_players < $plan->min_players){
//            return Helper::apiErrorResponse(false,'Min players for this plan should be '.$plan->min_players, new \stdClass());
//        }
        if($request->total_sensors < $plan->min_sensors){
            return Helper::apiErrorResponse(false,'Min sensors for this plan should be '.$plan->min_sensors, new \stdClass());
        }
        $total_bill =( ($plan->price_per_sensor*$request->total_sensors)+($plan->price_per_month));
        $discount = 0;
        if(isset($request->coupon)){
            $response = $this->checkCoupon($request,$total_bill);
            if(!$response['status']){
                return Helper::apiErrorResponse(false,$response['msg'], new \stdClass());
            }
            $discount = $response['discount'];
            $coupon = $response['coupon'];
            $coupon->quantity = $coupon->quantity-1;
            $coupon->save();
        }
        $grand_total = ($total_bill-$discount);

        $transaction = new PlanTransaction();
        $transaction->plan_id = $plan->id;
        $transaction->total_players = $request->total_players;
        $transaction->total_sensors = $request->total_sensors;
        $transaction->discount = $discount;
        $transaction->coupon = $request->coupon;
        $transaction->total_bill = $total_bill;
        $transaction->grand_total = $grand_total;
        $transaction->user_id = auth()->user()->id;
        $club = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->first();
        $club_id = $club->club_id ?? 0;
        $transaction->club_id = $club_id;

        if($grand_total <= 0) {
            $transaction->payment_status = 'paid';
        }

        $transaction->save();

//        $params = [
//            'card_number' => $request->card_number,
//            'expiry' => $request->expiry,
//            'cvc' => $request->cvc,
//            'amount' => $transaction->grand_total,
//            'transaction_id' => $transaction->id
//        ];

//        $res = Payment::makePayment($params);
//        $res = (array) json_decode($res);
//        if(isset($res['id'])) {
//            if($res['resultCode'] == 'RedirectShopper') {
//                $transaction->payment_status = 'paid';
//                $transaction->payment_response = $res['id'];
//                $transaction->save();

        return Helper::apiSuccessResponse(true, 'Success', $transaction);
//            } else {
//                $transaction->payment_response = $res['resultCode'];
//                $transaction->save();
//
//                return Helper::apiErrorResponse(false, $res['resultCode'], $res);
//            }
//        }

        return Helper::apiErrorResponse(false, $res, new \stdClass());
    }

    /**
     * PurchasePlan
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "success",
    "Result": {
    "payment_session_id": "cs_test_b1Z66SrlfYZTOpT1AVcBeuAYJDLF0e4k9JRzTluj6nnABDzEhkrCl9j8gk"
    }
    }
     *
     * @bodyParam plan_id integer required
     * @bodyParam total_sensors integer required
     * @bodyParam subscription_period string monthly/yearly
     * @bodyParam coupon string optional max 191 chars required
     * @return JsonResponse
     */


    public function subscribePlanTest(Request $request)
    {
        $this->validate($request,[
            'plan_id'=>'required',
            //'total_sensors'=>'required',
            'subscription_period'=>'required|in:monthly,yearly'
        ]);

        $plan = PricingPlan::find($request->plan_id);

        if (!$plan)
        {
            return Helper::apiErrorResponse(false,'Invalid Plan', new \stdClass());
        }

        if ($request->total_sensors > $plan->max_sensors)
        {
            return Helper::apiErrorResponse(false,'Max sensors for this plan should be '.$plan->max_sensors, new \stdClass());
        }

        $data = $this->getSubPriceAndDiscount($request,$plan);
        $sub_discount = $data['sub_discount'];
        $sub_price = $data['sub_price'];

        $total_bill = (($plan->price_per_sensor * $request->total_sensors) + ($sub_price));
        $discount = $sub_discount;
        $coupon = null;

        if (isset($request->coupon) && $request->coupon)
        {
            $response = $this->checkCoupon($request,$total_bill);
            if(!$response['status']){
                return Helper::apiErrorResponse(false,$response['msg'], new \stdClass());
            }

            $discount += $response['discount'];
        }

        try
        {
           $stripe_plan_id = $plan->stripe_prd_id_monthly;

           if ($request->subscription_period === 'yearly')
           {
               $stripe_plan_id = $plan->stripe_prd_id_yearly;
           }

           if (!$stripe_plan_id)
           {
               return Helper::apiErrorResponse(false,'Plan Not Added In Stripe', new \stdClass());
           }

           $stripe = \Stripe\Stripe::setApiKey(Helper::settings('stripe','STRIPE_SECRET'));

           $coupon_id = "";

           if ($discount && $discount > 0)
           {
               $coupon_id = Carbon::now()->timestamp;

               $coupon = \Stripe\Coupon::create([
                   'duration' => 'once',
                   'id' => $coupon_id,
                   'currency' => 'eur',
                   'amount_off' => round($discount) * 100,
               ]);
               
               $checkout_params = $this->failedCheckoutParams($request,$plan,$stripe_plan_id,$apply_coupon['code'] ?? '');
            }
            else
            {
                $checkout_params = $this->failedCheckoutParams($request,$plan,$stripe_plan_id,$apply_coupon['code'] ?? '');
            }

           if($request->total_sensors) {
               $checkout_params[] = [
                   'line_items' => [
                   [
                       'price_data' => [
                           'currency' => 'eur',
                           'unit_amount' => $plan->price_per_sensor*100,
                           'product_data' => [
                               'name' => $plan->name .' (Sensors)',
                           ],
                       ],
                       'quantity' => $request->total_sensors ?? 0,
                   ],
                   [
                       'price'=>$stripe_plan_id,
                       'quantity' => 1
                   ],
               ]
               ];
           } else {
               $checkout_params[] = [
                   'line_items' => [
                       [
                           'price'=>$stripe_plan_id,
                           'quantity' => 1
                       ],
                   ]
               ];
           }
           $checkout_session = \Stripe\Checkout\Session::create($checkout_params);
           return Helper::apiSuccessResponse(true,'success',['payment_session_id'=>$checkout_session->id, 'stripe_key' => Helper::settings('stripe','STRIPE_SECRET')]);
       }catch(\Exception $e){
           return  Helper::apiErrorResponse(false,$e->getMessage(), new \stdClass());
       }
    }

    /**
     * CreateCustomer
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "success",
    "Result": {
    "id": "cus_IqEBOtzE9yIN1T",
    "object": "customer",
    "address": null,
    "balance": 0,
    "created": 1611829647,
    "currency": null,
    "default_source": null,
    "delinquent": false,
    "description": null,
    "discount": null,
    "email": null,
    "invoice_prefix": "A0E17611",
    "invoice_settings": {
    "custom_fields": null,
    "default_payment_method": null,
    "footer": null
    },
    "livemode": false,
    "metadata": [],
    "name": "Khurram Munir",
    "phone": null,
    "preferred_locales": [],
    "shipping": null,
    "tax_exempt": "none"
    }
    }
     *
     * @return JsonResponse
     */

    public function createCustomer(Request $request)
    {
        $stripe = \Stripe\Stripe::setApiKey(Helper::settings('stripe','STRIPE_SECRET'));
        $stripe_customer = StripeCustomer::whereUserId(auth()->user()->id)->first();
        try{
            if(!empty($stripe_customer)){
                $customer_id = $stripe_customer->stripe_customer_id;
            }else{
                $customer = \Stripe\Customer::create([
                    'name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                    "phone" => auth()->user()->phone
                ]);
                $customer_id = $customer->id;
                $stripe_customer = new StripeCustomer;
                $stripe_customer->user_id = auth()->user()->id;
                $stripe_customer->stripe_customer_id = $customer_id;
                $stripe_customer->save();
            }

            if($request->payment_method == 'direct_debit') {
                $intent = \Stripe\SetupIntent::create([
                    'payment_method_types' => ['sepa_debit'],
                    'customer' => $customer_id
                ]);
            } elseif($request->payment_method == 'ideal') {
                $intent = \Stripe\SetupIntent::create([
                    'payment_method_types' => ['ideal'],
                    'customer' => $customer_id
                ]);
            } else {
                $intent = \Stripe\SetupIntent::create([
                    'customer' => $customer_id
                ]);
            }

            $intent->stripe_secret = Helper::settings('stripe','STRIPE_SECRET');
        }
        catch(\Exception $exception){
            return Helper::apiErrorResponse(false,$exception->getMessage(), new \stdClass());
        }

        return Helper::apiSuccessResponse(true,'success', $intent);
    }

    public function testPayment(Request $request)
    {
        return view('test-'.$request->payment_method.'payment', ['intent' => $request->intent]);
    }

    public function subscribePlan(Request $request){

        $stripe = \Stripe\Stripe::setApiKey(Helper::settings('stripe','STRIPE_SECRET'));
        $this->validate($request,[
            'plan_id'=>'required',
            'customer_id'=>'required',
            'team_id'=>'required|exists:teams,id',
            'subscription_period'=>'required|in:monthly,yearly',
//            "payment_token"=>'required'
        ]);

//        if($request->payment_method == 'direct_debit') {
        $intent = \Stripe\SetupIntent::retrieve($request->intent);
//            $payment_id = \Stripe\PaymentMethod::all([
//                'customer' => $request->customer_id,
//                'type' => 'sepa_debit',
//            ]);
//        } else {
//            $payment_id = \Stripe\PaymentMethod::all([
//                'customer' => $request->customer_id,
//                'type' => 'card',
//            ]);
//        }
//        \Stripe\Customer::retrieve($request->customer_id);
//        $customer = \Stripe\Customer::create([
////            "source" => 'tok_bypassPending' // for testing
//            "source" => $request->payment_token,
//        ]);
        $customer = ['id' => $request->customer_id];
        $plan = PricingPlan::find($request->plan_id);
        if(!$plan){
            return Helper::apiErrorResponse(false,'Invalid Plan', new \stdClass());
        }
//        if($request->total_sensors > $plan->max_sensors){
//            return Helper::apiErrorResponse(false,'Max sensors for this plan should be '.$plan->max_sensors, new \stdClass());
//        }
        $grand_total=0;
        $data = $this->getSubPriceAndDiscount($request,$plan);
        $sub_discount = $data['sub_discount'];
        $sub_price = $data['sub_price'];

        $free_trial_discount_duration_in_days = 0;
        $total_bill = $sub_price;
//        $discount = $sub_discount;
        $coupon = null;
        $userCoupon = UserCoupon::whereUserId(auth()->user()->id)->first();
        if(!empty($userCoupon)){
            $coupon = Coupon::where('code',$userCoupon->coupon)->first();
            if($coupon){
                $apply_coupon = $coupon->applyCoupon($total_bill);
                if(gettype($apply_coupon)==='string'){
                    return Helper::apiErrorResponse(false,$apply_coupon, new \stdClass());
                }
                //            $discount += $apply_coupon['discount_amount'];
                $free_trial_discount_duration_in_days = $apply_coupon['discount_duration'];
            }
        }
        try {
            $stripe_plan_id = $plan->stripe_prd_id_monthly;
            if ($request->subscription_period === 'yearly') {
                $stripe_plan_id = $plan->stripe_prd_id_yearly;
            }
            if (!$stripe_plan_id) {
                return Helper::apiErrorResponse(false, 'Plan Not Added In Stripe', new \stdClass());
            }
            //JOGO Basic plans (Pro/Fremium/etc) Recurring payment
            $subscription_plan = [
                'customer' => $customer['id'],
                'items' => [
                    [
                        'price' => $stripe_plan_id,
                    ],
                ],
                'default_payment_method' => $intent->payment_method
            ];
            if($free_trial_discount_duration_in_days>1){
                $subscription_plan['trial_end']=Carbon::now()->addDays($free_trial_discount_duration_in_days)->timestamp;
            }

            $subscribe_jogo_plan= \Stripe\Subscription::create($subscription_plan);
            if ($request->total_sensors) {
                //Jogo shinguard sensors purchase (one time payment)
                $shinguard_charge_one_time = \Stripe\PaymentIntent::create([
                    "customer" => $customer['id'],
                    'amount' => $request->total_sensors*($plan->price_per_sensor*100),
                    'currency' => 'eur',
                    'description' => $plan->name. '(Sensors)',
                    'payment_method' => $intent->payment_method,
                    'off_session' => true,
                    'confirm' => true,
                    'payment_method_types' => ['sepa_debit', 'card']
                ]);
                //shinguard monthly price for sensors
                $shinguard_monthly_plan = PricingPlan::where('name','Shinguard')->first();
                if($shinguard_monthly_plan){
                    $shinguard_subscription =[
                        "customer" => $customer['id'],
                        "items" => [
                            [
                                "price" => $shinguard_monthly_plan->stripe_prd_id_monthly,
                                'quantity' => $request->total_sensors
                            ],
                        ],
                        'default_payment_method' => $intent->payment_method
                    ];
                    $subscription_shinguard = \Stripe\Subscription::create($shinguard_subscription);
                }
            }
            $invoice = \Stripe\Invoice::all(["customer" => $request->customer_id]);
            $invoice_url = [];
            if($invoice && isset($invoice->data) && count($invoice->data) > 0) {
                foreach($invoice->data as $index => $row) {
                    if(($row->amount_paid / 100) == (10 * $request->total_sensors)) {
                        $invoice_url[] = ['name' => 'Sensor Subscription Invoice', 'url' => $row->hosted_invoice_url];
                    } else {
                        $invoice_url[] = ['name' => 'Trainer Plan Invoice', 'url' => $row->hosted_invoice_url];
                    }
                }
                if(isset($shinguard_charge_one_time) && $shinguard_charge_one_time) {
                    $invoice_url[] = ['name' => 'Sensor Invoice', 'url' => $shinguard_charge_one_time->charges->data[0]->receipt_url];
                }
            }
            //save transaction
            $transaction = new PlanTransaction();
            $transaction->plan_id = $plan->id;
            $transaction->total_sensors = $request->total_sensors;
            $transaction->discount = $free_trial_discount_duration_in_days;
            $transaction->coupon = !empty($userCoupon) ? $userCoupon->code : '';
            $transaction->total_bill = $total_bill;
            $transaction->grand_total = $total_bill;
            $transaction->user_id = auth()->user()->id;
            $transaction->payment_status = 'paid';
            $transaction->invoice_url = json_encode($invoice_url);
            $transaction->subscription_type = $request->subscription_period;
            $club = DB::table('club_trainers')->where('trainer_user_id', auth()->user()->id)->first();
            $club_id = $club->club_id ?? 0;
            $transaction->club_id = $club_id;
            $transaction->team_id = $request->team_id;
            $transaction->save();

            $team_subscription = new TeamSubscription;
            $team_subscription->team_id = $request->team_id;
            $team_subscription->plan_id = $plan->id;
            $team_subscription->coupon_id = 0;
            $team_subscription->start_date = date('Y-m-d');
            $team_subscription->end_date = date('Y-m-d',strtotime('+ 30 days'));
            $team_subscription->save();

            $transaction->access_type = Helper::checkTeamUpgradation();
            $transaction->permissions = !empty($transaction->access_type) ? Helper::getPermissions($transaction->access_type) : [];

            return Helper::apiSuccessResponse(true, 'success',$transaction);
        }catch (\Exception $e){
            return Helper::apiErrorResponse(false, $e->getMessage(),new \stdClass());
        }
    }

    public function generateStripSessionID($plan,$total_sensors=1,$discount=0,$coupon=null){
        $stripe = \Stripe\Stripe::setApiKey(Helper::settings('stripe','STRIPE_SECRET'));
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => $plan->price_per_sensor*100,
                        'product_data' => [
                            'name' => $plan->name .' (Sensors)',
                        ],
                    ],
                    'quantity' => $total_sensors,
                ],
                [
                    'price'=>'plan_Ikk2U7L21z6bmK',
                    'quantity' => 1,
                ],
            ],
            'mode' => 'subscription',
            'success_url' => url("/payment-success?session_id={CHECKOUT_SESSION_ID}"),
            'cancel_url' =>  route('payment-failed'),
            "metadata" => ["user_id" => auth()->user()->id,'total_sensors'=>$total_sensors,'plan_id'=>$plan->id,'subscription_plan'=>'plan_Ikk2U7L21z6bmK','coupon'=>$coupon]
        ]);
        return $checkout_session->id;
    }

    public function testPayments(){
        $stripe = new \Stripe\StripeClient(Helper::settings('stripe','STRIPE_SECRET'));
        $source = $stripe->sources->create([
            "type" => "ideal",
            "currency" => "eur",
            "amount" => "50000",
            "owner" => [
                "email" => "jenny.rosen@example.com"
            ],
            'redirect' => [
                'return_url' => route('success')
            ]
        ]);
        return \Illuminate\Support\Facades\Redirect::to($source->redirect->url);
    }

    public function testPaymentSuccess(Request $request){
        echo "<pre>".print_r($request->all(),1)."</pre>";exit;
    }

    public function getSubPriceAndDiscount($request,$plan){
        $sub_discount = 0;
        if ($request->subscription_period == 'monthly') {
            if ($plan->monthly_discount){
                $sub_discount = ($plan->price_per_month / 100) * $plan->monthly_discount;
                $sub_price = $plan->price_per_month;
            }else {
                $sub_price = $plan->price_per_month;
            }
        }else{
            if ($plan->yearly_discount){
                $sub_discount = ($plan->price_per_year / 100) * $plan->yearly_discount;
                $sub_price = $plan->price_per_year;
            }else{
                $sub_price = $plan->price_per_year;
            }
        }

        return ['sub_discount' => $sub_discount,'sub_price' => $sub_price];
    }

    public function checkCoupon($request,$total_bill){
        $coupon = Coupon::where('code',$request->coupon)->first();
        if(!$coupon){
            return ['status' => false, 'msg' => 'Invalid Coupon'];
        }
        $apply_coupon = $coupon->applyCoupon($total_bill);
        if(gettype($apply_coupon)==='string'){
            return ['status' => false, 'msg' => $apply_coupon];
        }
        $discount = $apply_coupon['discount_amount'];

        return ['status' => true, 'discount' => $discount, 'coupon' => $coupon ];
    }

    public function failedCheckoutParams($request,$plan,$stripe_plan_id,$apply_coupon){
        $checkout_params = [
            'payment_method_types' => ['card'],
            'mode' => 'subscription',
            'success_url' => url("/payment-success?session_id={CHECKOUT_SESSION_ID}"),
            'cancel_url' =>  route('payment-failed'),
            "metadata" => [
                "user_id" => auth()->user()->id,
                'total_sensors'=>$request->total_sensors,
                'plan_id'=>$plan->id,
                'subscription_plan'=>$stripe_plan_id,
                'coupon'=>$apply_coupon,
                'subscription_type'=>$request->subscription_period
            ]
        ];
        return $checkout_params;
    }
}
