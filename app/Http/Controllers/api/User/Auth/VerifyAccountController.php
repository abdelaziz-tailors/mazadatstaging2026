<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\VerifyAccountRequest;
use App\Helpers\TranslationHelper;
use App\Models\Admin;
use App\Models\UserOtp;
use App\Models\User\User;

use App\Http\Resources\User\UserResource;

use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;

class VerifyAccountController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(VerifyAccountRequest $request) {
        $otpService = app(\App\Services\OtpService::class);
        return DB::transaction(function () use ($request, $otpService) {
            $query = \App\Helpers\MobileNumber::matching(UserOtp::query(), $request->phone)
                ->where('registration_challenge_hash', hash('sha256', $request->challenge_id))
                ->where('type', 'register')->where('is_verified', false);
            if ($request->filled('user_type')) { $query->where('user_type', $request->user_type); }
            $pendingRegistration = $query->lockForUpdate()->first();
            if (! $pendingRegistration) {
                return $this->failed_response(TranslationHelper::translate('wrong_otp'));
            }
            if ($otpService->expired($pendingRegistration->expire_at)) {
                return $this->failed_response(TranslationHelper::translate('otp expire'), 403);
            }

            if (! $otpService->matchesRegistration($pendingRegistration->otp, $request->otp)) {
                return $this->failed_response(TranslationHelper::translate('wrong_otp'));
            }

            if (\App\Helpers\MobileNumber::matching(User::withTrashed(), $pendingRegistration->phone)->where('user_type', $pendingRegistration->user_type)->exists()) {
                return $this->failed_response(TranslationHelper::translate('phone_number_exists'));
            }

            if ($pendingRegistration->email !== null && User::where('email', $pendingRegistration->email)->exists()) {
                return $this->failed_response(TranslationHelper::translate('email_address_exists'));
            }

            if (User::where('user_name', $pendingRegistration->user_name)->exists()) {
                return $this->failed_response(TranslationHelper::translate('User Name Exists'));
            }

            DB::beginTransaction();
            try {
                $pendingRegistration = UserOtp::whereKey($pendingRegistration->id)
                    ->where('registration_challenge_hash', hash('sha256', $request->challenge_id))
                    ->where('type', 'register')->where('is_verified', false)->lockForUpdate()->first();
                if (! $pendingRegistration || ! $otpService->matchesRegistration($pendingRegistration->otp, $request->otp)
                    || $otpService->expired($pendingRegistration->expire_at)) {
                    throw new \RuntimeException('Registration OTP is no longer valid');
                }
                $user = User::create([
                    'name' => $pendingRegistration->name,
                    'email' => $pendingRegistration->email,
                    'phone' => \App\Helpers\MobileNumber::normalize($pendingRegistration->phone),
                    'user_name' => $pendingRegistration->user_name,
                    'national_id' => $pendingRegistration->national_id,
                    'user_type' => $pendingRegistration->user_type ?? 'buyer',
                    'city_id' => $pendingRegistration->city_id,
                    'commercial_register' => $pendingRegistration->commercial_register,
                    'password' => $pendingRegistration->password,
                    'is_verified' => true,
                    'otp' => null,
                    'expire_at' => null,
                ]);

                if (($pendingRegistration->user_type ?? 'buyer') === 'vendor') {
                    $admin = Admin::create([
                        'name' => $pendingRegistration->name,
                        'email' => $pendingRegistration->email,
                        'phone' => \App\Helpers\MobileNumber::normalize($pendingRegistration->phone),
                        'national_id' => $pendingRegistration->national_id,
                        'type' => 'partner',
                        'user_id' => $user->id,
                        'password' => $pendingRegistration->password,
                    ]);
                    $user->update([
                        'admin_id' => $admin->id,
                    ]);
                }

                $otpService->consume($pendingRegistration, 'otp', true);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                if ($th instanceof \Illuminate\Database\QueryException && \App\Exceptions\Handler::identityConflict($th)) {
                    return app(\App\Exceptions\Handler::class)->render($request, $th);
                }

                return $this->failed_response(TranslationHelper::translate('Something went wrong'));
            }

            $user['token'] = $user->createToken('MyApp')->accessToken;
            $data = new UserResource($user);

            return $this->success_response(TranslationHelper::translate('your_account_logged_in_successfully'), $data);
        });
    }
}
