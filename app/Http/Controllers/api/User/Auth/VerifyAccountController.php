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
            if (! $existingUser->expire_at || $now->isAfter($existingUser->expire_at)) {
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
        if (! $pendingRegistration->expire_at || $now->isAfter($pendingRegistration->expire_at)) {
            return $this->failed_response(TranslationHelper::translate('otp expire'), 403);
        }

        if ((string) $pendingRegistration->otp !== (string) $request->otp) {
            return $this->failed_response(TranslationHelper::translate('wrong_otp'));
        }

        if (User::where('phone', $pendingRegistration->phone)->exists()) {
            return $this->failed_response(TranslationHelper::translate('phone_number_exists'));
        }

        if (User::where('email', $pendingRegistration->email)->exists()) {
            return $this->failed_response(TranslationHelper::translate('email_address_exists'));
        }

        if (User::where('user_name', $pendingRegistration->user_name)->exists()) {
            return $this->failed_response(TranslationHelper::translate('User Name Exists'));
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $pendingRegistration->name,
                'email' => $pendingRegistration->email,
                'phone' => $pendingRegistration->phone,
                'user_name' => $pendingRegistration->user_name,
                'user_type' => $pendingRegistration->user_type ?? 'buyer',
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
                    'phone' => $pendingRegistration->phone,
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

            return $this->failed_response(TranslationHelper::translate('Something went wrong'));
        }

        $user['token'] = $user->createToken('MyApp')->accessToken;
        $data = new UserResource($user);

        return $this->success_response(TranslationHelper::translate('your_account_logged_in_successfully'), $data);
    }
}
