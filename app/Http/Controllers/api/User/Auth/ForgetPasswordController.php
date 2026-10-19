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
            $user = $this->passwordResetUser($request->phone);

            if (! $user) {
                return $this->failed_response(TranslationHelper::translate('wrong_phone_number'));
            }

            if (! $user->is_verified) {
                return $this->failed_response(TranslationHelper::translate('verify_account_before_reset_password'));
            }

            $otp = $this->generate_password_otp();
            $expireAt = Carbon::now()->addMinutes(10);

            $user->update([
                'password_otp' => $otp,
                'expire_at' => $expireAt,
            ]);

            $otpResult = $smsService->sendWhatsappOtp(
                $user->phone,
                (string) $otp
            );

            if (! ($otpResult['success'] ?? false)) {
                \Log::warning('FORGET_PASSWORD_WHATSAPP_OTP_FAILED', [
                    'status' => $otpResult['status'] ?? null,
                ]);

                $user->update([
                    'password_otp' => null,
                    'expire_at' => null,
                ]);

                return $this->failed_response(
                    TranslationHelper::translate('Something went wrong')
                );
            }

            \Log::info('FORGET_PASSWORD_WHATSAPP_OTP_SENT', [
                'provider_message_id' => $otpResult['provider_message_id'] ?? null,
                'provider_status' => $otpResult['provider_status_name'] ?? null,
            ]);

            return $this->success_response(TranslationHelper::translate('new_otp_has_been_sent'), [
                'phone' => $user->phone,
                'expire_at' => $expireAt,
            ]);
        });
    }
}
