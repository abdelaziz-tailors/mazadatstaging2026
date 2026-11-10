<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\FCMService;
use App\Services\PushTokenOwnership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SendStoredNotification implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 5;
    public $backoff = 60;
    public $notificationId;

    public function __construct(int $notificationId)
    {
        $this->notificationId = $notificationId;
        $this->afterCommit();
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $notification = Notification::whereKey($this->notificationId)->lockForUpdate()->first();
            if (! $notification || ! $notification->event_key || $notification->push_completed_at) {
                return;
            }
            app(PushTokenOwnership::class)->deliver((int) $notification->user_id, function ($token) {
                // FCM can deliver after account switching, even after our lock is
                // released. Only authenticated inbox retrieval exposes details.
                $result = app(FCMService::class)->sendToDevice(
                    $token, 'Mazadat', 'Open the app to view your notifications.',
                    ['type' => 'inbox_updated', 'screen' => 'notification', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']
                );
                if (! ($result['success'] ?? false)) {
                    throw new \RuntimeException('Notification push failed.');
                }
            });
            // No exclusively owned active device is terminal; the inbox remains available.
            DB::table('notifications')->where('id', $notification->id)->update(['push_completed_at' => now()]);
        });
    }
}
