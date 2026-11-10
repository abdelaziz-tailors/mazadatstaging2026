<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/** Legacy date/time columns remain unchanged; new schedule columns are UTC. */
final class AuctionScheduleUtc
{
    public static function mobileTimezone(?string $requested, ?string $stored = null, string $type = 'live'): string
    {
        // Existing live clients submit Cairo wall-clock fields without a zone.
        return $requested ?: ($stored ?: ($type === 'live' ? 'Africa/Cairo' : config('app.timezone')));
    }

    public static function fromFields(array $fields, ?string $timezone): array
    {
        // No timezone from a legacy mobile client means NO timezone assumption.
        if (! $timezone) {
            return ['schedule_start_utc' => null, 'schedule_end_utc' => null,
                'schedule_timezone' => null];
        }
        try {
            new \DateTimeZone($timezone);
            foreach (['date_start_at', 'time_start_at', 'date_end_at', 'time_end_at'] as $name) {
                if (! isset($fields[$name])) {
                    throw new \InvalidArgumentException('Incomplete auction schedule.');
                }
            }
            $startText = $fields['date_start_at'].' '.substr($fields['time_start_at'], 0, 5).':00';
            $endText = $fields['date_end_at'].' '.substr($fields['time_end_at'], 0, 5).':00';
            $start = CarbonImmutable::createFromFormat('!Y-m-d H:i:s', $startText, $timezone);
            $end = CarbonImmutable::createFromFormat('!Y-m-d H:i:s', $endText, $timezone);
            // Reject DST gaps and dates normalized by PHP.
            if (! $start || ! $end || $start->format('Y-m-d H:i:s') !== $startText
                || $end->format('Y-m-d H:i:s') !== $endText || ! $end->greaterThan($start)) {
                throw new \InvalidArgumentException('Invalid auction schedule.');
            }
            return ['schedule_start_utc' => $start->utc()->format('Y-m-d H:i:s'),
                'schedule_end_utc' => $end->utc()->format('Y-m-d H:i:s'),
                'schedule_timezone' => $timezone];
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'timezone' => ['Invalid timezone or auction schedule.'],
            ]);
        }
    }

    public static function isoUtc($value): ?string
    {
        if (! $value) { return null; }
        return CarbonImmutable::parse((string) $value, 'UTC')->utc()->format('Y-m-d\TH:i:s\Z');
    }
}
