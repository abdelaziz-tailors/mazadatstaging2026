<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\ForgetPasswordrRequest;
use App\Http\Requests\api\User\Auth\RegisterRequest;
use App\Helpers\TranslationHelper;
use App\Services\SmsService;

use App\Http\Resources\User\UserResource;
use App\Models\Admin;
use App\Models\Patient;
use App\Models\User\User;


use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(RegisterRequest $request, SmsService $smsService)
    {

        DB::beginTransaction();
        try {
            $numbers = mt_rand(1000, 9999);
            $expire_at = Carbon::now()->addMinutes(10);

            $commercialRegisterName = null;
            if (($request->user_type ?? 'buyer') === 'vendor' && $request->hasFile('commercial_register')) {
                $file = $request->file('commercial_register');
                $commercialRegisterName = 'vendor-commercial-files/'. mt_rand(11111, 99999).'_'.$file->getClientOriginalName();
                $file->move(public_path('../storage/app/public/vendor-commercial-files'), $commercialRegisterName);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'user_name' => $request->user_name,
                // 'account_type' => $request->account_type,
                'user_type' => $request->user_type ?? 'buyer',
                'commercial_register' => $commercialRegisterName,
                'password' => bcrypt($request->password),
                'otp' => $numbers,
                'expire_at' => $expire_at,
            ]);


            if ($request->user_type == 'vendor') {
                $admin = Admin::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'type' => 'partner',
                    'user_id' => $user->id,
                    'password' => bcrypt($request->password),
                ]);
                $user->update([
                    'admin_id' => $admin->id,
                ]);
            }

            // $token = $user->createToken('MyApp')->accessToken;

            $user['token'] = $token ?? null;

            $smsResult = $smsService->sendRegistrationOtp($request->phone, (string) $numbers);

            if (! ($smsResult['success'] ?? false)) {
                throw new \RuntimeException($smsResult['error'] ?? 'Otp sending failed');
            }
            DB::commit();
            return $this->success_response(TranslationHelper::translate('new_otp_has_been_sent'), new UserResource($user));

        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->failed_response(TranslationHelper::translate('Something went wrong'),);
        }
    }
    public function UserCheckData(RegisterRequest $request)
    {


        return $this->success_response(TranslationHelper::translate('successfully'), '');
    }

    public function forgetPassword(ForgetPasswordrRequest $request)
    {

        $user = User::where('phone', $request->phone)->first();

        if (empty($user)) {
            return $this->failed_response(TranslationHelper::translate('wrong_phone_number'));
        }

        $otp = $this->generate_password_otp();
        $user->password_otp = $otp;
        $user->save();

        try {
            $mobile = '2'.$request->phone;
            $uuid = Str::uuid();
            $client_sms = new Client();
            $headers = [
                'Content-Type' => 'application/json',
            ];
            $body = '{
              "UserName": "DacktraAPI",
              "Password": "rsDdr|u:&&",
              "SMSText": "Your password reset code is: '.$otp.'",
              "SMSLang": "e",
              "SMSSender": "Dacktra",
              "SMSReceiver": "'.$mobile.'",
              "SMSID": "'.$uuid.'"
            }';
            $smsRequest = new \GuzzleHttp\Psr7\Request('POST', 'https://smsvas.vlserv.com/VLSMSPlatformResellerAPI/NewSendingAPI/api/SMSSender/SendSMS', $headers, $body);
            $client_sms->sendAsync($smsRequest)->wait();
        } catch (\Throwable $e) {
            $user->password_otp = null;
            $user->save();

            return $this->failed_response(TranslationHelper::translate('Something went wrong'));
        }

        return $this->success_response(TranslationHelper::translate('new_otp_has_been_sent'), $otp  );
    }
}
