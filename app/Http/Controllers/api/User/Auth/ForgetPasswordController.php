<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\ForgetPasswordRequest;
use App\Helpers\TranslationHelper;
use App\Models\User\User;
use App\Services\SmsService;
use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;

class ForgetPasswordController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(ForgetPasswordRequest $request, SmsService $smsService)
    {
        $user = User::where('phone', $request->phone)->first();

        if (! $user) {
            return $this->failed_response(TranslationHelper::translate('wrong_phone_number'));
        }

        if (! $user->is_verified) {
            return $this->failed_response(TranslationHelper::translate('verify_account_before_reset_password'));
        }

        $otp = config('services.otp.testing')
            ? (string) config('services.otp.test_code', '1111')
            : (string) $this->generate_password_otp();
        $expireAt = Carbon::now()->addMinutes(10);

        $user->update([
            'password_otp' => $otp,
            'expire_at' => $expireAt,
        ]);

        if (! config('services.otp.testing')) {
            $otpResult = $smsService->sendWhatsappOtp(
                $request->phone,
                (string) $otp
            );

            if (! ($otpResult['success'] ?? false)) {
                \Log::warning('FORGET_PASSWORD_WHATSAPP_OTP_FAILED', [
                    'error' => $otpResult['error'] ?? null,
                    'status' => $otpResult['status'] ?? null,
                    'response' => $otpResult['response'] ?? null,
                ]);

                $user->update([
                    'password_otp' => null,
                    'expire_at' => null,
                ]);

                return $this->failed_response(
                    TranslationHelper::translate('Something went wrong')
                );
            }
        } else {
            \Log::info('FORGET_PASSWORD_TEST_OTP', [
                'phone' => $request->phone,
            ]);
        }

        return $this->success_response(TranslationHelper::translate('new_otp_has_been_sent'), [
            'phone' => $request->phone,
            'expire_at' => $expireAt,
        ]);
    }
}
