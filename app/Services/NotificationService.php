<?php

namespace App\Services;

use App\Models\Notification;
use App\Jobs\SendStoredNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /** Atomic recipient receipt plus durable push outbox, using the existing inbox. */
    public static function notifyOnce(int $userId, string $eventId, string $type, string $title, string $description, array $data): Notification
    {
        return DB::transaction(function () use ($userId, $eventId, $type, $title, $description, $data) {
            $key = hash('sha256', $eventId.':'.$userId);
            $now = now();
            $inserted = DB::table('notifications')->insertOrIgnore([
                'event_key' => $key, 'user_id' => $userId, 'type' => $type,
                'title' => $title, 'description' => $description, 'data' => json_encode($data, JSON_THROW_ON_ERROR),
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $notification = Notification::where('event_key', $key)->firstOrFail();
            if ($inserted) {
                DB::afterCommit(function () use ($notification) {
                    try {
                        dispatch(new SendStoredNotification($notification->id));
                    } catch (\Throwable $e) {
                        // The scheduler re-enqueues committed, unfinished receipts.
                        Log::warning('Notification push pending retry.', ['notification_id' => $notification->id]);
                    }
                });
            }
            return $notification;
        });
    }

    /**
     * Create a notification targeted at a single user.
     */
    public static function notify(int $userId, string $type, string $title, string $description, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'data' => $data,
        ]);
    }
}
