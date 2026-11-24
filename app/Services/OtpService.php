<?php

namespace App\Services;

use App\Helpers\MobileNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OtpService
{
    private const FIXED_CODE = '1111';

    // Every OTP workflow intentionally uses the same public fixed code.
    public function registrationChallenge(): array
    {
        return $this->challenge('');
    }

    public function matchesRegistration($stored, $provided): bool
    {
        return $this->matches('', $stored, $provided);
    }

    public function usesFixedCode(string $phone): bool
    {
        return true;
    }

    // Preserve existing non-registration delivery independently of the fixed code.
    public function shouldSendNotification(string $phone): bool
    {
        if (app()->environment(['local', 'development', 'testing'])) {
            return false;
        }
        // Unknown environments fail closed too. Only production supports QA exceptions.
        if (! app()->environment('production') || ! MobileNumber::valid($phone)) {
            return true;
        }
        $phones = config('services.otp.qa_phones', []);
        if (! is_array($phones) || array_values($phones) !== $phones) {
            return true;
        }
        foreach ($phones as $allowed) {
            // A malformed configuration disables the whole list, rather than partially trusting it.
            if (! is_string($allowed) || ! MobileNumber::valid($allowed)) {
                return true;
            }
        }
        return ! in_array(MobileNumber::normalize($phone), array_map([MobileNumber::class, 'normalize'], $phones), true);
    }

    public function generate(string $phone): string
    {
        return self::FIXED_CODE;
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
        return hash_equals(self::FIXED_CODE, (string) $stored)
            && hash_equals(self::FIXED_CODE, (string) $provided);
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
