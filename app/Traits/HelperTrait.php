<?php

namespace App\Traits;
use Illuminate\Support\Facades\Auth;
use App\Models\User\User;

trait HelperTrait
{
    public function generate_otp () {
        $generator = "0123456789";
        $result = "";
        for ($i = 0; $i < 4; $i++) {
            $result .= substr($generator, (rand()%(strlen($generator))), 1);
        }
        if(User::where('otp', $result)->count() > 0) {
            $result = $this->generate_otp();
        }
        return $result;
    }

    public function generate_password_otp () {
        $generator = "0123456789";
        $result = "";
        for ($i = 0; $i < 4; $i++) {
            $result .= substr($generator, random_int(0, strlen($generator) - 1), 1);
        }
        if(User::where('password_otp', $result)->count() > 0) {
            $result = $this->generate_password_otp();
        }
        return $result;
    }

    protected function passwordResetUser(string $phone): ?User
    {
        return \App\Helpers\MobileNumber::find(User::class, $phone, true);
    }
}
