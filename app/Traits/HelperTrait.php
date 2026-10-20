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
        $user = User::where('phone', $phone)->lockForUpdate()->first();
        if ($user) {
            return $user;
        }

        // Preserve exact legacy matches; accept Saudi local/international variants.
        $digits = preg_replace('/[\s()+-]/', '', $phone);
        if (! preg_match('/^(?:00966|966|0)?(5[0-9]{8})$/D', $digits, $matches)) {
            return null;
        }
        $local = $matches[1];
        $users = User::whereIn('phone', [$local, '0'.$local, '966'.$local, '+966'.$local, '00966'.$local])
            ->lockForUpdate()->get();

        // Never choose between multiple accounts sharing a normalized number.
        return $users->count() === 1 ? $users->first() : null;
    }
}
