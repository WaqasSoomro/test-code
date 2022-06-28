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
//class AdyenController extends Controller
//{
//    protected $checkout;
//
//    function __construct(AdyenClient $checkout) {
//        $this->checkout = $checkout->service;
//    }
//
//    /**
//     * Checkout to Payment
//     *
//     * @response {
//    "Response": true,
//    "StatusCode": 200,
//    "Message": "Success",
//    "Result": {}
//    }
//     *
//     * @bodyParam transaction_id integer required
//     * @bodyParam type optional
//     * @return JsonResponse
//     */
//
//    public function checkout(Request $request)
//    {
//        $transaction = PlanTransaction::find($request->transaction_id);
//        $redirect_url = $request->redirect_url;
//
//        if(!$transaction) {
//            return Helper::apiErrorResponse(false, "Invalid Transaction ID", new \stdClass());
//        }
//
//        if($transaction->payment_status == 'paid') {
//            return Helper::apiErrorResponse(false, "Already Paid", new \stdClass());
//        }
//
//        $params = array(
//            'type' => $request->type ?? 'ideal',
//            'clientKey' => env('CLIENT_KEY'),
//            'amount' => $transaction->grand_total,
//            'reference' => $transaction->id,
//            'redirect_url' => $redirect_url
//        );
//
//        return view('adyen.payment')->with($params);
//    }
//
//    public function getPaymentMethods(Request $request){
//        error_log("Request for getPaymentMethods $request");
//
//        $params = array(
//            "merchantAccount" => env('MERCHANT_ACCOUNT'),
//            "channel" => "Web"
//        );
//
//        $response = $this->checkout->paymentMethods($params);
//
//        return $response;
//    }
//
//    public function initiatePayment(Request $request){
//        error_log("Request for initiatePayment $request");
//
//        $orderRef = $request->reference;
//        $redirect_url = $request->redirect_url;
//        $params = array(
//            "merchantAccount" => env('MERCHANT_ACCOUNT'),
//            "channel" => "Web", // required
//            "amount" => array(
//                "currency" => $this->findCurrency(($request->paymentMethod)["type"]),
//                "value" => $request->amount ?? "" // value is 10€ in minor units
//            ),
//            "reference" => $orderRef, // required
//            // required for 3ds2 native flow
//            "additionalData" => array(
//                "allow3DS2" => "true"
//            ),
//            "origin" => $request->root(), // required for 3ds2 native flow
//            "shopperIP" => $request->ip(),// required by some issuers for 3ds2
//            // we pass the orderRef in return URL to get paymentData during redirects
//            // required for 3ds2 redirect flow
//            "returnUrl" => route("handleShopperRedirect", ['redirect_url' => $redirect_url, 'orderRef' => $orderRef]),
//            "paymentMethod" => $request->paymentMethod,
//            "browserInfo" => $request->browserInfo // required for 3ds2
//        );
//
//        $response = $this->checkout->payments($params);
//
//        if (isset($response["action"])) {
//            \Cache::put($orderRef, $response["action"]["paymentData"]);
//        }
//
//        return $response;
//    }
//
//
//    public function submitAdditionalDetails(Request $request){
//        error_log("Request for submitAdditionalDetails $request");
//
//        $payload = array("details" => $request->details, "paymentData" => $request->paymentData);
//
//        $response = $this->checkout->paymentsDetails($payload);
//
//        return $response;
//    }
//
//    public function handleShopperRedirect(Request $request){
//        error_log("Request for handleShopperRedirect $request");
//        $redirect = $request->all();
//
//        $transaction = PlanTransaction::find($request->orderRef);
//
//        $details = array();
//        if (isset($redirect["payload"])) {
//            $details["payload"] = $redirect["payload"];
//        } else if (isset($redirect["redirectResult"])) {
//            $details["redirectResult"] = $redirect["redirectResult"];
//        } else {
//            $details["MD"] = $redirect["MD"];
//            $details["PaRes"] = $redirect["PaRes"];
//        }
//        $orderRef = $request->orderRef;
//
//        $payload = array("details" => $details, "paymentData" => \Cache::pull($orderRef));
//
//        $response = $this->checkout->paymentsDetails($payload);
//
//        switch ($response["resultCode"]) {
//            case "Authorised":
//                if($transaction) {
//                    $transaction->payment_status = 'paid';
//                    $transaction->payment_response = json_encode($request->all());
//                    $transaction->save();
//                }
//                return redirect($request->redirect_url . '?payment_status=paid');
////                return view('adyen.response', ['type' => 'success']);
////                return redirect()->route('result', ['type' => 'success']);
//            case "Pending":
//            case "Received":
//                if($transaction) {
//                    $transaction->payment_response = json_encode($request->all());
//                    $transaction->save();
//                }
//                return redirect($request->redirect_url . '?payment_status=unpaid');
////                return view('adyen.response', ['type' => 'pending']);
////                return redirect()->route('result', ['type' => 'pending']);
//            case "Refused":
//                if($transaction) {
//                    $transaction->payment_response = json_encode($request->all());
//                    $transaction->save();
//                }
//                return redirect($request->redirect_url . '?payment_status=unpaid');
////                return view('adyen.response', ['type' => 'failed']);
////                return redirect()->route('result', ['type' => 'failed']);
//            default:
//                if($transaction) {
//                    $transaction->payment_response = json_encode($request->all());
//                    $transaction->save();
//                }
//                return redirect($request->redirect_url . '?payment_status=unpaid');
////                return view('adyen.response', ['type' => 'error']);
////                return redirect()->route('result', ['type' => 'error']);
//        }
//
////        return $response["resultCode"];
//    }
//
//    /* ################# end API ENDPOINTS ###################### */
//
//    // Util functions
//    public function findCurrency($type){
//        switch ($type) {
//            case "ach":
//                return "USD";
//            case "wechatpayqr":
//            case "alipay":
//                return "CNY";
//            case "dotpay":
//                return "PLN";
//            case "boletobancario":
//            case "boletobancario_santander":
//                return "BRL";
//            default:
//                return "EUR";
//        }
//    }

//}
