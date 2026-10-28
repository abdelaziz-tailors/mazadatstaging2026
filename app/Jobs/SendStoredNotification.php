<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\FCMService;
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
            // Resolve current owner/token; never send a private receipt to a supplied token.
            $user = $notification->user;
            if ($user && $user->is_active && $user->fcm_token) {
                $data = array_map(function ($value) {
                    return is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : (string) $value;
                }, array_merge($notification->data ?? [], [
                    'notification_id' => $notification->id, 'type' => $notification->type,
                ]));
                $result = app(FCMService::class)->sendToDevice(
                    $user->fcm_token, $notification->title, $notification->description, $data
                );
                if (! ($result['success'] ?? false)) {
                    throw new \RuntimeException('Notification push failed.');
                }
            }
            // No active device is terminal; history is still available in the inbox.
            DB::table('notifications')->where('id', $notification->id)->update(['push_completed_at' => now()]);
        });
    }
}
