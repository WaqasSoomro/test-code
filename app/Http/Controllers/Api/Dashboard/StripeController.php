<?php

//namespace App\Http\Controllers\Api\Dashboard;
//
//use App\Helpers\Payment;
//use App\Http\AdyenClient;
//use App\PlanTransaction;
//use App\UserNotification;
//use App\Helpers\Helper;
//use App\Http\Controllers\Controller;
//use Illuminate\Http\JsonResponse;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\Storage;
//use Illuminate\Support\Facades\Validator;
//use stdClass;
//use \Cache;
//
///**
// * @authenticated
// * @group Dashboard / Notifications
// *
// * APIs to manage Trainer Notifications
// */
//class StripeController extends Controller
//{
//
//    public function callback(Request $request) {
//
//        $stripe = \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
//        $intent = \Stripe\SetupIntent::retrieve(
//            $request->setup_intent,
//            []
//        );
//        $stripe = new \Stripe\StripeClient(
//            env('STRIPE_SECRET')
//        );
//        return $stripe->setupIntents->confirm(
//            $request->setup_intent,
//            ['payment_method' => $intent->payment_method]
//        );
//        /*return $shinguard_charge_one_time = \Stripe\PaymentIntent::create([
//            "customer" => $intent->customer,
//            'amount' => 10*(15*100),
//            'currency' => 'eur',
//            'description' => 'Test'. '(Sensors)',
//            'payment_method' => $intent->payment_method,
//            'off_session' => true,
//            'confirm' => true
//        ]);
//        return $request->all();*/
//    }
//}
