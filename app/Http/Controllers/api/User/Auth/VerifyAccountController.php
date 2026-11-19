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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VerifyAccountController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(VerifyAccountRequest $request) {
        if (\App\Helpers\MobileNumber::matching(UserOtp::query(), $request->phone)->where('type', 'role_access')->where('is_verified', false)->exists()) {
            return $this->verifyRoleAccess($request);
        }
        $pendingRegistration = UserOtp::where('phone', $request->phone)
            ->where('type', 'register')
            ->where('is_verified', false)
            ->first();
        $existingUser = User::where('phone', $request->phone)->first();

        // Backward compatibility for old accounts that were created before OTP verification.
        if (! $pendingRegistration && $existingUser) {
            if ((string) $existingUser->otp !== (string) $request->otp) {
                return $this->failed_response(TranslationHelper::translate('wrong_otp'));
            }

            $now = Carbon::now();
            if (! $existingUser->expire_at || $now->greaterThanOrEqualTo($existingUser->expire_at)) {
                return $this->failed_response(TranslationHelper::translate('otp expire'), 403);
            }

            $existingUser->is_verified = true;
            $existingUser->otp = null;
            $existingUser->expire_at = null;
            $existingUser->save();

            $existingUser['token'] = $existingUser->createToken('MyApp')->accessToken;
            $data = new UserResource($existingUser);

            return $this->success_response(TranslationHelper::translate('your_account_logged_in_successfully'), $data);
        }

        if (! $pendingRegistration) {
            return $this->failed_response(TranslationHelper::translate('wrong_phone_number'));
        }

        $now = Carbon::now();
        if (! $pendingRegistration->expire_at || $now->greaterThanOrEqualTo($pendingRegistration->expire_at)) {
            return $this->failed_response(TranslationHelper::translate('otp expire'), 403);
        }

        if ((string) $pendingRegistration->otp !== (string) $request->otp) {
            return $this->failed_response(TranslationHelper::translate('wrong_otp'));
        }

        if (\App\Helpers\MobileNumber::matching(User::withTrashed(), $pendingRegistration->phone)->exists()) {
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
                ->where('is_verified', false)->lockForUpdate()->first();
            if (! $pendingRegistration || (string) $pendingRegistration->otp !== (string) $request->otp
                || ! $pendingRegistration->expire_at || now()->greaterThanOrEqualTo($pendingRegistration->expire_at)) {
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

            $pendingRegistration->update([
                'is_verified' => true,
            ]);
            $pendingRegistration->delete();
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
    }
    private function verifyRoleAccess(VerifyAccountRequest $request)
    {
        try {
            $user = DB::transaction(function () use ($request) {
                $pending = \App\Helpers\MobileNumber::matching(UserOtp::query(), $request->phone)
                    ->where('type', 'role_access')->where('is_verified', false)->lockForUpdate()->first();
                if (! $pending || ! is_scalar($request->otp) || ! hash_equals((string) $pending->otp, (string) $request->otp)
                    || ! $pending->expire_at || now()->greaterThanOrEqualTo($pending->expire_at)) {
                    throw new \RuntimeException('Invalid role verification');
                }
                $matches = \App\Helpers\MobileNumber::matching(User::query(), $pending->phone)->lockForUpdate()->limit(2)->get();
                $user = $matches->count() === 1 ? $matches->first() : null;
                if (! $user || ! $user->is_verified || ! $user->is_active
                    || ! in_array($user->user_type, ['buyer', 'seller'], true)
                    || ! in_array($pending->user_type, ['buyer', 'seller'], true)
                    || ! hash_equals(hash('sha256', $user->id.':'.$user->password), (string) $pending->password)) {
                    throw new \RuntimeException('Account no longer eligible');
                }
                if (! $user->hasAccessRole($pending->user_type)) {
                    DB::table('user_access_roles')->insert([
                        'user_id' => $user->id, 'role' => $pending->user_type,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                $user->setAttribute('active_role', $pending->user_type);
                $pending->update(['is_verified' => true]);
                $pending->delete();
                return $user;
            });
        } catch (\Throwable $exception) {
            return $this->failed_response(TranslationHelper::translate('wrong_otp'), 403);
        }
        $user['token'] = $user->createToken('MyApp')->accessToken;
        return $this->success_response(TranslationHelper::translate('your_account_logged_in_successfully'), new UserResource($user));
    }

}
