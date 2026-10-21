<?php

namespace App\Http\Requests\api\Video;

use App\Models\LiveVideo;
use Carbon\CarbonImmutable;

trait ValidatesAuctionSchedule
{
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $fields = ['date_start_at', 'time_start_at', 'date_end_at', 'time_end_at'];
            $updating = $this instanceof UpdateVideoRequest;
            if ($updating && ! $this->hasAny($fields)) {
                return;
            }
            $auction = null;
            if ($updating) {
                $auction = LiveVideo::where('user_id', auth('api')->id())->find($this->route('id'));
                // Preserve the controller's ownership and already-started status rules.
                if (! $auction || $auction->status !== 'pending') {
                    return;
                }
            }
            $values = [];
            foreach ($fields as $field) {
                if ($validator->errors()->has($field)) {
                    return;
                }
                $value = $this->exists($field) ? $this->input($field) : ($auction ? $auction->$field : null);
                $format = strpos($field, 'date_') === 0 ? 'Y-m-d' : 'H:i';
                // MySQL TIME columns return seconds even though clients submit HH:mm.
                if (! $this->exists($field) && $format === 'H:i' && is_string($value) && strlen($value) === 8) {
                    $format = 'H:i:s';
                }
                if (! is_string($value) || ! CarbonImmutable::canBeCreatedFromFormat($value, $format)) {
                    $validator->errors()->add($field, trans('validation.auction_schedule_format'));
                    return;
                }
                $values[$field] = $format === 'H:i' ? $value.':00' : $value;
            }
            // Offset-free API fields represent wall-clock time in the application timezone.
            $timezone = config('app.timezone');
            $start = CarbonImmutable::createFromFormat('!Y-m-d H:i:s', $values['date_start_at'].' '.$values['time_start_at'], $timezone);
            $end = CarbonImmutable::createFromFormat('!Y-m-d H:i:s', $values['date_end_at'].' '.$values['time_end_at'], $timezone);
            if ($start->lessThan(CarbonImmutable::now($timezone))) {
                $validator->errors()->add('time_start_at', trans('validation.auction_start_future'));
            }
            if (! $end->greaterThan($start)) {
                $validator->errors()->add('time_end_at', trans('validation.auction_end_after_start'));
            }
        });
    }
}
