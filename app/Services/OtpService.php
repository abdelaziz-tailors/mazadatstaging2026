<?php

namespace App\Services;

use App\Helpers\MobileNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OtpService
{
    public function usesFixedCode(string $phone): bool
    {
        if (app()->environment(['local', 'development', 'testing'])) {
            return true;
        }
        // Unknown environments fail closed too. Only production supports QA exceptions.
        if (! app()->environment('production') || ! MobileNumber::valid($phone)) {
            return false;
        }
        $phones = config('services.otp.qa_phones', []);
        if (! is_array($phones) || array_values($phones) !== $phones) {
            return false;
        }
        foreach ($phones as $allowed) {
            // A malformed configuration disables the whole list, rather than partially trusting it.
            if (! is_string($allowed) || ! MobileNumber::valid($allowed)) {
                return false;
            }
        }
        return in_array(MobileNumber::normalize($phone), array_map([MobileNumber::class, 'normalize'], $phones), true);
    }

    public function generate(string $phone): string
    {
        if ($this->usesFixedCode($phone)) {
            return '1111';
        }
        do {
            $code = (string) random_int(1000, 9999);
        } while ($code === '1111');
        return $code;
    }

    public function challenge(string $phone, string $column = 'otp'): array
    {
        return [$column => $this->generate($phone), 'expire_at' => now()->startOfSecond()->addMinutes(10)];
    }

    public function matches(string $phone, $stored, $provided): bool
    {
        if (! is_scalar($provided) || ! is_scalar($stored)
            || ! preg_match('/^[0-9]{4}$/D', (string) $provided)
            || ! preg_match('/^[0-9]{4}$/D', (string) $stored)) {
            return false;
        }
        // Retire previously persisted globally fixed production codes as well.
        return ((string) $stored !== '1111' || $this->usesFixedCode($phone))
            && hash_equals((string) $stored, (string) $provided);
    }

    public function expired($expiresAt): bool
    {
        if (! $expiresAt) {
            return true;
        }
        try {
            return now()->greaterThanOrEqualTo(Carbon::parse($expiresAt));
        } catch (\Throwable $exception) {
            return true;
        }
    }

    /** Call while holding the record lock, inside the caller's transaction. */
    public function consume(Model $record, string $column = 'otp', bool $pending = false): void
    {
        $record->setAttribute($column, null);
        $record->setAttribute('expire_at', null);
        if ($pending) {
            $record->setAttribute('is_verified', true);
        }
        $record->save();
        if ($pending) {
            $record->delete();
        }
    }
}
