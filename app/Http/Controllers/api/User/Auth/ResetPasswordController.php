<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\ResetPasswordRequest;
use App\Helpers\TranslationHelper;
use App\Http\Resources\User\UserResource;
use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(ResetPasswordRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $otpService = app(\App\Services\OtpService::class);
            $user = $this->passwordResetUser($request->phone);

            if (! $user || ! $user->is_verified || ! $user->password_otp
                || ! $otpService->matches($user->phone, $user->password_otp, $request->otp)) {
                return $this->failed_response(TranslationHelper::translate('wrong_otp'));
            }

            if ($otpService->expired($user->expire_at)) {
                return $this->failed_response(TranslationHelper::translate('otp expire'), 403);
            }

            if (Hash::check($request->password, $user->password)) {
                return $this->failed_response(
                    TranslationHelper::translate('new_password_must_differ_from_current_password'),
                    422
                );
            }

            $user->password = bcrypt($request->password);
            $otpService->consume($user, 'password_otp');

            $user->token = $user->createToken('MyApp')->accessToken;

            return $this->success_response(
                TranslationHelper::translate('your_password_changed_successfully'),
                new UserResource($user)
            );
        });
    }
}
