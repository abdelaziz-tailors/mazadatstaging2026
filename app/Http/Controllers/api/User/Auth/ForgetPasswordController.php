<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\ForgetPasswordRequest;
use App\Helpers\TranslationHelper;
use App\Services\SmsService;
use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ForgetPasswordController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(ForgetPasswordRequest $request, SmsService $smsService)
    {
        return DB::transaction(function () use ($request, $smsService) {
            $user = $this->passwordResetUser($request->phone, $request->input('user_type'));

            if (! $user) {
                return $this->failed_response(TranslationHelper::translate('wrong_phone_number'));
            }

            if (! $user->is_verified) {
                return $this->failed_response(TranslationHelper::translate('verify_account_before_reset_password'));
            }

            $otpService = app(\App\Services\OtpService::class);
            $sendNotification = $otpService->shouldSendNotification($user->phone);
            $challenge = $otpService->challenge($user->phone, 'password_otp');
            $otp = $challenge['password_otp'];
            $expireAt = $challenge['expire_at'];

            $user->update([
                'password_otp' => $otp,
                'expire_at' => $expireAt,
            ]);

            if ($sendNotification) {
                try {
                    $otpResult = $smsService->sendWhatsappOtp($user->phone, (string) $otp);
                } catch (\Throwable $exception) {
                    $otpResult = ['success' => false];
                }

                if (! ($otpResult['success'] ?? false)) {
                    \Log::warning('FORGET_PASSWORD_OTP_DELIVERY_FAILED');

                    $user->update([
                        'password_otp' => null,
                        'expire_at' => null,
                    ]);

                    return $this->failed_response(
                        TranslationHelper::translate('Something went wrong')
                    );
                }

                \Log::info('FORGET_PASSWORD_OTP_SENT');
            }

            return $this->success_response(TranslationHelper::translate('new_otp_has_been_sent'), [
                'phone' => $user->phone,
                'expire_at' => $expireAt,
            ]);
        });
    }
}
