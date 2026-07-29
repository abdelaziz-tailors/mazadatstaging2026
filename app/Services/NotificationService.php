<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
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
