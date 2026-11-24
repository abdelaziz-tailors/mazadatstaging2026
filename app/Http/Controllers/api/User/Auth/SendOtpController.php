<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Helpers\MobileNumber;
use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\SendOtpRequest;
use App\Http\Requests\api\User\Auth\SendPhoneOtpRequest;
use App\Models\User\User;
use App\Services\OtpService;
use App\Services\SmsService;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;

/** Legacy entry points; no public routes currently reference this controller. */
class SendOtpController extends Controller
{
    use ResponseTrait;

    public function __invoke(SendOtpRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = is_string($request->phone) ? MobileNumber::find(User::class, $request->phone, true) : null;
            if (! $user || $user->is_verified) {
                return $this->failed_response(TranslationHelper::translate('wrong_phone_number'));
            }
            $policy = app(OtpService::class);
            $user->update($policy->challenge($user->phone));
            if ($policy->shouldSendNotification($user->phone)) {
                try {
                    $result = app(SmsService::class)->sendWhatsappOtp($user->phone, $user->otp);
                } catch (\Throwable $exception) {
                    $result = ['success' => false];
                }
                if (! ($result['success'] ?? false)) {
                    $policy->consume($user);
                    return $this->failed_response(TranslationHelper::translate('Something went wrong'));
                }
            }
            return $this->success_response(TranslationHelper::translate('new_otp_has_been_sent'), [
                'phone' => $user->phone, 'expire_at' => $user->expire_at,
            ]);
        });
    }

    public function approvedMobile(SendPhoneOtpRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = is_string($request->phone) ? MobileNumber::find(User::class, $request->phone, true) : null;
            $policy = app(OtpService::class);
            if (! $user || $user->is_verified || ! $policy->matches($user->phone, $user->otp, $request->otp)) {
                return response()->json(['success' => false, 'code' => 422,
                    'message' => TranslationHelper::translate('wrong_phone_number')], 422);
            }
            if ($policy->expired($user->expire_at)) {
                return response()->json(['success' => false, 'code' => 403,
                    'message' => TranslationHelper::translate('otp expire')], 403);
            }
            $user->is_verified = true;
            $policy->consume($user);
            return $this->success_response(TranslationHelper::translate('Successfully'), '');
        });
    }
}
