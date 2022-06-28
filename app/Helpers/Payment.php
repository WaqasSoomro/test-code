<?php

namespace App\Helpers;

use App\Http\AdyenClient;

class Payment {

    protected $checkout;

    function __construct(AdyenClient $checkout) {
        $this->checkout = $checkout->service;
    }

    public function initiatePayment(Request $request){
        error_log("Request for initiatePayment $request");

        $orderRef = uniqid();
        $params = array(
            "merchantAccount" => env('MERCHANT_ACCOUNT'),
            "channel" => "Web", // required
            "amount" => array(
                "currency" => $this->findCurrency(($request->paymentMethod)["type"]),
                "value" => 1000 // value is 10€ in minor units
            ),
            "reference" => $orderRef, // required
            // required for 3ds2 native flow
            "additionalData" => array(
                "allow3DS2" => "true"
            ),
            "origin" => "http://localhost:8080", // required for 3ds2 native flow
            "shopperIP" => $request->ip(),// required by some issuers for 3ds2
            // we pass the orderRef in return URL to get paymentData during redirects
            // required for 3ds2 redirect flow
            "returnUrl" => "http://localhost:8080/api/handleShopperRedirect?orderRef=${orderRef}",
            "paymentMethod" => $request->paymentMethod,
            "browserInfo" => $request->browserInfo // required for 3ds2
        );

        $response = $this->checkout->payments($params);

        if (isset($response["action"])) {
            \Cache::put($orderRef, $response["action"]["paymentData"]);
        }

        return $response;
    }

    public static function makePayment($data) {
        $card_number = $data['card_number'] ?? '';
        $expiry = $data['expiry'] ?? '';
        if($expiry) {
            $expiry = explode('/', $expiry);
            $month = $expiry[1] ?? '';
            $year = $expiry[0] ?? '';
        } else {
            $month = '';
            $year = '';
        }
        $cvc = $data['cvc'] ?? '';
        $amount = $data['amount'] * 100 ?? '';
        $transaction_id = $data['transaction_id'] ?? '';

        $curl = curl_init();

        $header = [
            "x-API-key: AQEhhmfuXNWTK0Qc+iSanWM3hu0U4sQqE3KWrwZH2Rlyt++XEMFdWw2+5HzctViMSCJMYAc=-r0Y102mjCFp9fERzIYlVRQyFsns4ADYujiaSG8i7y3Q=-SGU5)Wn]k:em}W*P",
            "content-type: application/json"
        ];

        $payload = [
            "merchantAccount" => "JogoAiECOM",
//            "paymentMethod" => [
//                "type" => "paysafecard",
//    //                "encryptedCardNumber" => $card_number,
//    //                "encryptedExpiryMonth" => $month,
//    //                "encryptedExpiryYear" => $year,
//    //                "encryptedSecurityCode" => $cvc
//            ],
            "amount" => [
                "currency" => "EUR",
                "value" => $amount
            ],
            "reference" => $transaction_id,
            "countryCode" => "NL",
            "shopperLocale" => "en-US", // nl-NL
            "returnUrl" => url('/api/v1/dashboard/response')
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://checkout-test.adyen.com/v66/paymentLinks", //payments
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $header
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            activity()->log($err);
        } else {
            activity()->log($response);
            return $response;
        }
    }

    public static function updatePayment($data)
    {
        $curl = curl_init();

        $header = [
            "x-API-key: AQEhhmfuXNWTK0Qc+iSanWM3hu0U4sQqE3KWrwZH2Rlyt++XEMFdWw2+5HzctViMSCJMYAc=-r0Y102mjCFp9fERzIYlVRQyFsns4ADYujiaSG8i7y3Q=-SGU5)Wn]k:em}W*P",
            "content-type: application/json"
        ];

        $payment_id = $data['payment_id'];

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://checkout-test.adyen.com/v66/paymentLinks/' . $payment_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $header
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        dd($response);
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            activity()->log($err);
        } else {
            activity()->log($response);
            return $response;
        }
    }
}
