<?php

namespace App\Console\Commands;

use App\Jobs\SendStoredNotification;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryNotificationPushes extends Command
{
    protected $signature = 'notifications:retry-pushes';
    protected $description = 'Retry committed lifecycle notifications with unfinished push delivery';

    public function handle(): int
    {
        Notification::whereNotNull('event_key')->whereNull('push_completed_at')
            ->where('created_at', '<=', now()->subMinute())
            ->select('id')->chunkById(200, function ($notifications) {
                foreach ($notifications as $notification) {
                    try {
                        dispatch(new SendStoredNotification($notification->id));
                    } catch (\Throwable $e) {
                        // A failed synchronous send or enqueue must not starve later users.
                        Log::warning('Notification push recovery pending retry.', ['notification_id' => $notification->id]);
                    }
                }
            });
        return 0;
    }
}
