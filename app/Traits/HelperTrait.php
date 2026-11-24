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
        return \App\Helpers\MobileNumber::find(User::class, $phone, true, $userType);
    }
}
