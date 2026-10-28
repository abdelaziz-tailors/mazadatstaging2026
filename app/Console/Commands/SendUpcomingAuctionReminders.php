<?php

namespace App\Console\Commands;

use App\Models\LiveVideo;
use App\Services\LifecycleNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendUpcomingAuctionReminders extends Command
{
    protected $signature = 'notifications:upcoming-auction-reminders';
    protected $description = 'Notify eligible auction users one hour before the start';

    public function handle(): int
    {
        $sent = 0;
        LiveVideo::where(function ($query) {
            $query->whereNull('status')->orWhere('status', 'pending');
        })->whereNull('upcoming_reminder_sent_at')
            ->whereNotNull('date_start_at')->whereNotNull('time_start_at')
            ->select('id')->chunkById(200, function ($candidates) use (&$sent) {
                foreach ($candidates as $candidate) {
                    $sent += DB::transaction(function () use ($candidate) {
                        $auction = LiveVideo::whereKey($candidate->id)->lockForUpdate()->first();
                        if (! $auction || $auction->upcoming_reminder_sent_at || ! in_array($auction->status, [null, 'pending'], true)) {
                            return 0;
                        }
                        try {
                            $startsAt = Carbon::parse($auction->date_start_at.' '.$auction->time_start_at);
                        } catch (\Throwable $e) {
                            return 0;
                        }
                        if ($startsAt->lt(now()) || $startsAt->gt(now()->addHour())) { return 0; }
                        $count = app(LifecycleNotificationService::class)->upcoming($auction);
                        $auction->update(['upcoming_reminder_sent_at' => now()]);
                        return $count;
                    }, 3);
                }
            });
        $this->info("Upcoming auction reminders sent: {$sent}.");
        return 0;
    }
}
