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
        try {
            return DB::transaction(function () use ($request, $otpService) {
                $identity = app(\App\Services\RegistrationIdentity::class);
                $identity->lock($request->phone);
                $query = \App\Helpers\MobileNumber::matching(UserOtp::query(), $request->phone)
                    ->where('type', 'register')->where('is_verified', false);
                $pendingRegistration = $query->orderByDesc('created_at')->orderByDesc('id')->lockForUpdate()->first();
                if ($pendingRegistration && (($request->filled('user_type') && $request->user_type !== $pendingRegistration->user_type)
                    || ($request->filled('challenge_id') && ! hash_equals((string) $pendingRegistration->registration_challenge_hash, hash('sha256', $request->challenge_id))))) {
                    return $this->failed_response(TranslationHelper::translate('wrong_otp'));
                }
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

                if (User::where('user_name', $pendingRegistration->user_name)->exists()) {
                    return $this->failed_response(TranslationHelper::translate('User Name Exists'));
                }

                $groupId = $pendingRegistration->credential_group_id;
                if ($groupId) {
                    $group = DB::table('credential_groups')->where('id', $groupId)->lockForUpdate()->first();
                    if (! $group || $group->phone !== $pendingRegistration->phone
                        || $identity->email($group->email) !== $identity->email($pendingRegistration->email)
                        || ! hash_equals($group->password, $pendingRegistration->password)) {
                        return $this->failed_response(TranslationHelper::translate('wrong_otp'));
                    }
                } else {
                    $conflict = User::withTrashed()->where(function ($q) use ($pendingRegistration) {
                        \App\Helpers\MobileNumber::matching($q, $pendingRegistration->phone);
                        if ($pendingRegistration->email !== null) {
                            $q->orWhereRaw('LOWER(TRIM(email)) = ?', [mb_strtolower(trim($pendingRegistration->email), 'UTF-8')]);
                        }
                    })->exists();
                    if ($conflict) { return $this->failed_response(TranslationHelper::translate('email_address_exists')); }
                    $groupId = DB::table('credential_groups')->insertGetId([
                        'phone' => \App\Helpers\MobileNumber::normalize($pendingRegistration->phone),
                        'email' => $identity->email($pendingRegistration->email), 'password' => $pendingRegistration->password,
                    ]);
                }
                $user = User::create([
                    'credential_group_id' => $groupId,
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
                \App\Helpers\MobileNumber::matching(UserOtp::query(), $request->phone)
                    ->where('type', 'register')->where('is_verified', false)->update(['deleted_at' => now()]);

                $user['token'] = $user->createToken('MyApp')->accessToken;
                $data = new UserResource($user);

                return $this->success_response(TranslationHelper::translate('your_account_logged_in_successfully'), $data);
            }, 3);
        } catch (\Illuminate\Database\QueryException $exception) {
            if (\App\Exceptions\Handler::identityConflict($exception)) {
                return app(\App\Exceptions\Handler::class)->render($request, $exception);
            }
            return $this->failed_response(TranslationHelper::translate('Something went wrong'));
        }
    }
}
