<?php

namespace App\Traits;
use Illuminate\Support\Facades\Auth;
use App\Models\User\User;

trait HelperTrait
{
    public function generate_otp(string $phone = '') {
        return app(\App\Services\OtpService::class)->generate($phone);
    }

    public function generate_password_otp(string $phone = '') {
        return app(\App\Services\OtpService::class)->generate($phone);
    }
    protected function passwordResetUser(string $phone, ?string $userType = null): ?User
    {
        $candidate = \App\Helpers\MobileNumber::matching(User::query(), $phone)->orderBy('id')->first();
        if ($candidate && $candidate->credential_group_id) {
            \Illuminate\Support\Facades\DB::table('credential_groups')->where('id', $candidate->credential_group_id)->lockForUpdate()->first();
        }
        $matches = \App\Helpers\MobileNumber::matching(User::query(), $phone)->orderBy('id')->lockForUpdate()->get();
        if ($userType !== null) {
            $selected = $matches->where('user_type', $userType);
            return $selected->count() === 1 ? $selected->first() : null;
        }
        if ($matches->count() === 1) { return $matches->first(); }
        // Only an explicitly trusted shared identity makes an untyped recovery unambiguous.
        $groups = $matches->pluck('credential_group_id')->unique();
        return $matches->isNotEmpty() && $groups->count() === 1 && $groups->first() !== null
            ? $matches->first() : null;
    }
}
