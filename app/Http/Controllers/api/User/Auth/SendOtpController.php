<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\SendOtpRequest;
use App\Helpers\TranslationHelper;

use App\Http\Requests\api\User\Auth\SendPhoneOtpRequest;
use App\Http\Resources\UserOTPResource;
use App\Models\Patient;
use App\Models\User\User;

use App\Http\Resources\UserResource;

use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SendOtpController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(SendOtpRequest $request) {
        $user_exists = Patient::where('phone', $request->phone)
            ->exists();

        if ($user_exists) {
            $client = Patient::where([['phone', $request->phone]])->first();

            $numbers =  mt_rand(1000, 9999);
            $uuid = \Str::uuid();


            $mobile="2".$request->phone;

            $client_sms = new Client();
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $body = '{
              "UserName": "DacktraAPI",
              "Password": "rsDdr|u:&&",
              "SMSText": "verification code is '.$numbers.'",
              "SMSLang": "e",
              "SMSSender": "Dacktra",
              "SMSReceiver": "'.$mobile.'",
              "SMSID": "'.$uuid.'"
            }';
//              dd($body);
            $request = new \GuzzleHttp\Psr7\Request('POST', 'https://smsvas.vlserv.com/VLSMSPlatformResellerAPI/NewSendingAPI/api/SMSSender/SendSMS', $headers, $body);
            $res = $client_sms->sendAsync($request)->wait();


            $expire_at=Carbon::now()->addMinutes(10);

            $client->update(['otp' => $numbers, 'expire_at' => $expire_at]);


            $data = new UserOTPResource($client);
                return $this->success_response(TranslationHelper::translate('new_otp_has_been_sent'), $data);

        }
        else {
            return $this->failed_response(TranslationHelper::translate('wrong_phone_number'));
        }
    }

    public function approvedMobile(SendPhoneOtpRequest $request)
    {


        $now = Carbon::now();
        $otp=Patient::where([['phone',$request->phone],['otp',$request->otp]])->first();

        if (isset($otp)) {
            if($now->isAfter($otp->expire_at)){


                return response()->json(['success'=>false,'code' => 403, 'message' =>TranslationHelper::translate('otp expire')], 403);



            }

            $otp->update([
                    'approved_mobile'=>'1',
                ]
            );
            return $this->success_response(TranslationHelper::translate('Successfully'), '');

        }

        return response()->json(['success'=>false,'code' => 422, 'message' =>TranslationHelper::translate('mobile not exist')], 422);

    }

}
