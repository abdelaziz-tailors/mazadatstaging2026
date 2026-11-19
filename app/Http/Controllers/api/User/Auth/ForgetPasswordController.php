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
        $testing = app()->environment(['local', 'testing']) && config('services.otp.password_recovery_testing', false);
        $issuance = DB::transaction(function () use ($request, $testing) {
            $user = $this->passwordResetUser($request->phone);

            if (! $user) {
                return $this->failed_response(TranslationHelper::translate('wrong_phone_number'));
            }

            if (! $user->is_verified) {
                return $this->failed_response(TranslationHelper::translate('verify_account_before_reset_password'));
            }

            $otp = $testing ? '1111' : $this->generate_password_otp();
            $expireAt = Carbon::now()->startOfSecond()->addMinutes(10);

            $user->update([
                'password_otp' => $otp,
                'expire_at' => $expireAt,
            ]);

            return [$user, (string) $otp, $expireAt];
        });

        if (! is_array($issuance)) {
            return $issuance;
        }

        [$user, $otp, $expireAt] = $issuance;
        if (! $testing) {
            try {
                $otpResult = $smsService->sendWhatsappOtp(
                    $user->phone,
                    $otp,
                    $expireAt
                );
            } catch (\Throwable $th) {
                $otpResult = ['success' => false];
            }

            if (! ($otpResult['success'] ?? false)) {
                // Match the persisted code and expiry before invalidating this attempt.
                $user->newQuery()->whereKey($user->id)->where('password_otp', $otp)
                    ->where('expire_at', $expireAt)->update([
                        'password_otp' => null,
                        'expire_at' => null,
                    ]);

                \Log::warning('FORGET_PASSWORD_WHATSAPP_OTP_FAILED', [
                    'http_status' => isset($otpResult['status']) && is_int($otpResult['status']) ? $otpResult['status'] : null,
                    'transport_success' => ($otpResult['transport_success'] ?? false) === true,
                ]);

                return $this->failed_response(TranslationHelper::translate('Something went wrong'));
            }

            \Log::info('FORGET_PASSWORD_WHATSAPP_OTP_ACCEPTED');
        }

        return $this->success_response(TranslationHelper::translate('new_otp_has_been_sent'), [
            'phone' => $user->phone,
            'expire_at' => $expireAt,
        ]);
    }
}
