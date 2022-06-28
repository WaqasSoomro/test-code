<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Helpers\Payment;
use App\PlanTransaction;
use App\UserNotification;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use stdClass;

/**
 * @authenticated
 * @group Dashboard / Notifications
 *
 * APIs to manage Trainer Notifications
 */
class TestPaymentController extends Controller
{
    public function makePayment(Request $request)
    {
        $curl = curl_init();

        $header = [
            "x-API-key: AQEhhmfuXNWTK0Qc+iSanWM3hu0U4sQqE3KWrwZH2Rlyt++XEMFdWw2+5HzctViMSCJMYAc=-r0Y102mjCFp9fERzIYlVRQyFsns4ADYujiaSG8i7y3Q=-SGU5)Wn]k:em}W*P",
            "content-type: application/json"
        ];

        $payload = [
            "merchantAccount" => "JogoAiECOM",
              "paymentMethod" => [
                  "type" => "scheme", // paysafecard (redirecting method)
                  "encryptedCardNumber" => "test_4111111111111111",
                  "encryptedExpiryMonth" => "test_03",
                  "encryptedExpiryYear" => "test_2030",
                  "encryptedSecurityCode" => "test_737"
              ],
              "amount" => [
                    "currency" => "USD",
                    "value" => "1000"
                ],
              "reference" => "123",
              "returnUrl" => url('/api/v1/dashboard/response')
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://checkout-test.adyen.com/v66/payments",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $header
        ));

        $response = curl_exec($curl);
        return $response;
        $err = curl_error($curl);

        curl_close($curl);
    }

    public function response(Request $request)
    {
        return $request->all();
        $params = ['payment_id' => $request->ec];
        $res = Payment::updatePayment($params);
        $rows = PlanTransaction::where('payment_status', 'unpaid')->where('payment_response', '!=', '')->get();
        foreach($rows as $row) {
            $params = ['payment_id' => "PLED7777CE6A2F31BB"];
            $res = Payment::updatePayment($params);
        }
        return $request->all();
    }

}
