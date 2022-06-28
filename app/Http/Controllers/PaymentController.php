<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\Helpers\Helper;
use App\PlanTransaction;
use App\PricingPlan;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{

    public function payment()
    {


//        $stripe = new \Stripe\StripeClient(
//            env('STRIPE_SECRET')
//        );
//
//        $plan = $stripe->plans->create([
//            'amount' => 109.99,
//            'currency' => 'eur',
//            'interval' => 'month',
//            'product' => 'prod_IkoBzuOajW9sK2',
//        ]);
//        dd($plan);
        /*$availablePlans =[
            'plan_Ikk2U7L21z6bmK' => "Monthly",
        ];*/
//        $user = User::find(2);
//        $data = [
//            'intent' => $user->createSetupIntent(),
//            'plans'=> $availablePlans
//        ];
//        return view('payment')->with($data);
    }



    public function processPayment(){
//        $user = User::find(2);
//        $paymentMethod = $request->payment_method;
//        $planId = $request->plan;
//        $user->newSubscription('main', $planId)->create($paymentMethod);
//        $stripeCharge = $user->charge(
//            100, $paymentMethod,[
//                'amount' => 100,
//                'currency' => 'eur',
//                'source' => $paymentMethod,
//                'description' => 'My First Test Charge (created for API docs)',
//            ]
//        );
//        return response([
//            'success_url'=> redirect()->intended('/')->getTargetUrl(),
//            'message'=>'success'
//        ]);
    }

    function pay(){
        return view('payment1');
    }
    public function createSession(){
//        $stripe = \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
//        $checkout_session = \Stripe\Checkout\Session::create([
//            'payment_method_types' => ['card'],
//            'line_items' => [
//                [
//                'price_data' => [
//                    'currency' => 'eur',
//                    'unit_amount' => 3.5* 100,
//                    'product_data' => [
//                        'name' => 'Football Academy',
//                    ],
//                ],
//                'quantity' => 3,
//                ],
//                [
//                    'price'=>'price_1I9IZ2FMayMS0ajCA9JMhD02',
//                    'quantity' => 1,
//                ],
//            ],
//            'mode' => 'subscription',
//            'success_url' => route('home'),
//            'cancel_url' =>  route('home'),
//        ]);
//        echo json_encode(['id' => $checkout_session->id]);
    }


//    public function paymentSuccess(Request $request){
//        $stripe = \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
//
//        $intent = \Stripe\SetupIntent::retrieve($request->setup_intent);
//        $customer = ['id' => $intent->customer];
//        $plan = PricingPlan::find($request->plan_id);
//
//        $payment_id = \Stripe\PaymentMethod::all([
//            'customer' => $intent->customer,
//            'type' => 'sepa_debit',
//        ]);
//        $intent->payment_method = $payment_id->data[0]->id;
//        if(!$plan){
//            return Helper::apiErrorResponse(false,'Invalid Plan', new \stdClass());
//        }
//        if($request->subscription_period == 'monthly') {
//            if($plan->monthly_discount) {
//                $sub_discount = ($plan->price_per_month / 100) * $plan->monthly_discount;
//                $sub_price = $plan->price_per_month;
//            } else {
//                $sub_price = $plan->price_per_month;
//            }
//        } else {
//            if($plan->yearly_discount) {
//                $sub_discount = ($plan->price_per_year / 100) * $plan->yearly_discount;
//                $sub_price = $plan->price_per_year;
//            } else {
//                $sub_price = $plan->price_per_year;
//            }
//        }
//
//        $free_trial_discount_duration_in_days = 0;
//        $total_bill =( ($plan->price_per_sensor*$request->total_sensors)+($sub_price));
////        $discount = $sub_discount;
//        $coupon = null;
//        if(isset($request->coupon) && $request->coupon){
//            $coupon = Coupon::where('code',$request->coupon)->first();
//            if(!$coupon){
//                return Helper::apiErrorResponse(false,'Invalid Coupon', new \stdClass());
//            }
//            $apply_coupon = $coupon->applyCoupon($total_bill);
//            if(gettype($apply_coupon)==='string'){
//                return Helper::apiErrorResponse(false,$apply_coupon, new \stdClass());
//            }
////            $discount += $apply_coupon['discount_amount'];
//            $free_trial_discount_duration_in_days = $apply_coupon['discount_duration'];
//        }try {
//            $stripe_plan_id = $plan->stripe_prd_id_monthly;
//            if ($request->subscription_period === 'yearly') {
//                $stripe_plan_id = $plan->stripe_prd_id_yearly;
//            }
//            if (!$stripe_plan_id) {
//                return Helper::apiErrorResponse(false, 'Plan Not Added In Stripe', new \stdClass());
//            }
//            //JOGO Basic plans (Pro/Fremium/etc) Recurring payment
//            $subscription_plan = [
//                'customer' => $customer['id'],
//                'items' => [
//                    [
//                        'price' => $stripe_plan_id,
//                    ],
//                ],
//                'default_payment_method' => $intent->payment_method
//            ];
//            if($free_trial_discount_duration_in_days>1){
//                $subscription_plan['trial_end']=Carbon::now()->addDays($free_trial_discount_duration_in_days)->timestamp;
//            }
//
//            $subscribe_jogo_plan= \Stripe\Subscription::create($subscription_plan);
//            if ($request->total_sensors) {
//                //Jogo shinguard sensors purchase (one time payment)
//                $shinguard_charge_one_time = \Stripe\PaymentIntent::create([
//                    "customer" => $customer['id'],
//                    'amount' => $request->total_sensors*($plan->price_per_sensor*100),
//                    'currency' => 'eur',
//                    'description' => $plan->name. '(Sensors)',
//                    'payment_method' => $intent->payment_method,
//                    'off_session' => true,
//                    'confirm' => true,
//                    'payment_method_types' => ['sepa_debit', 'card', 'ideal']
//                ]);
//                //shinguard monthly price for sensors
//                $shinguard_monthly_plan = PricingPlan::where('name','Shinguard')->first();
//                if($shinguard_monthly_plan){
//                    $shinguard_subscription =[
//                        "customer" => $customer['id'],
//                        "items" => [
//                            [
//                                "price" => $shinguard_monthly_plan->stripe_prd_id_monthly,
//                                'quantity' => $request->total_sensors
//                            ],
//                        ],
//                        'default_payment_method' => $intent->payment_method
//                    ];
//                    $subscription_shinguard = \Stripe\Subscription::create($shinguard_subscription);
//                }
//            }
//            $invoice = \Stripe\Invoice::all(["customer" => $customer['id']]);
//            $invoice_url = [];
//            if($invoice && isset($invoice->data) && count($invoice->data) > 0) {
//                foreach($invoice->data as $index => $row) {
//                    if(($row->amount_paid / 100) == (10 * $request->total_sensors)) {
//                        $invoice_url[] = ['name' => 'Sensor Subscription Invoice', 'url' => $row->hosted_invoice_url];
//                    } else {
//                        $invoice_url[] = ['name' => 'Trainer Plan Invoice', 'url' => $row->hosted_invoice_url];
//                    }
//                }
//                if(isset($shinguard_charge_one_time) && $shinguard_charge_one_time) {
//                    $invoice_url[] = ['name' => 'Sensor Invoice', 'url' => $shinguard_charge_one_time->charges->data[0]->receipt_url];
//                }
//            }
//            //save transaction
//            $transaction = new PlanTransaction();
//            $transaction->plan_id = $plan->id;
//            $transaction->total_sensors = $request->total_sensors;
//            $transaction->discount = $free_trial_discount_duration_in_days;
//            $transaction->coupon = $request->coupon;
//            $transaction->total_bill = $total_bill;
//            $transaction->grand_total = $total_bill;
//            $transaction->user_id = auth()->user()->id ?? $request->user_id;
//            $transaction->payment_status = 'paid';
//            $transaction->invoice_url = json_encode($invoice_url);
//            $transaction->subscription_type = $request->subscription_period;
//            $club = DB::table('club_trainers')->where('trainer_user_id', auth()->user()->id ?? $request->user_id)->first();
//            $club_id = $club->club_id ?? 0;
//            $transaction->club_id = $club_id;
//            $transaction->save();
//            return Helper::apiSuccessResponse(true, 'success',$transaction);
//        }catch (\Exception $e){
//            return Helper::apiErrorResponse(false, $e->getMessage(),new \stdClass());
//        }
////        return view('payment-success');
//    }


    public function paymentFailed(){
        return redirect(env('FRONTEND_REDIRECT_URL'));
//        return view('payment-failed');
    }
    //
}
