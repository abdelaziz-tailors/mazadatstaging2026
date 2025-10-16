<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\ForgetPasswordRequest;
use App\Helpers\TranslationHelper;
use App\Models\Patient;
use App\Models\User\User;

use App\Http\Resources\UserResource;

use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use GuzzleHttp\Client;

class ForgetPasswordController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke (ForgetPasswordRequest $request) {

        $patient_exists = Patient::where('phone', $request->phone)
            ->exists();

        if ($patient_exists) {
            $password = random_int(100000, 999999);
            $patient = Patient::where('phone', $request->phone)->first();

            $patient->update([
                'password' => bcrypt($password)
            ]);

            $uuid = \Str::uuid();

            $mobile="2".$patient->phone;

            $client_sms = new Client();
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $body = '{
              "UserName": "DacktraAPI",
              "Password": "rsDdr|u:&&",
              "SMSText": "hello  your new password is :  '.$password.'",
              "SMSLang": "e",
              "SMSSender": "Dacktra",
              "SMSReceiver": "'.$mobile.'",
              "SMSID": "'.$uuid.'"
            }';
//              dd($body);
            $request = new \GuzzleHttp\Psr7\Request('POST', 'https://smsvas.vlserv.com/VLSMSPlatformResellerAPI/NewSendingAPI/api/SMSSender/SendSMS', $headers, $body);
            $res = $client_sms->sendAsync($request)->wait();






            return $this->success_response(TranslationHelper::translate('New Password Send to Your Phone'), '');

        } else {
            return $this->failed_response(TranslationHelper::translate('wrong_login_credintails'));
        }

        }
}
