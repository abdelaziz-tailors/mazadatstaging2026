<?php

namespace App\Jobs;

use App\Services\FCMService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendFCMNotification implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue;

    protected $tokens;
    protected $title;
    protected $body;
    protected $data;
    // Old serialized jobs have no marker and must not expose captured private content.
    protected $publicBroadcast = false;

    public function __construct($tokens, $title, $body, $data = [], bool $publicBroadcast = false)
    {
        $this->tokens = $tokens;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->publicBroadcast = $publicBroadcast;
    }

    public function handle(FCMService $fcmService)
    {
        $title = $this->publicBroadcast ? $this->title : 'Mazadat';
        $body = $this->publicBroadcast ? $this->body : 'Open the app to view your notifications.';
        $data = $this->publicBroadcast ? $this->data : [
            'type' => 'inbox_updated', 'screen' => 'notification', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];
        if (is_array($this->tokens)) {
            $fcmService->sendToMultipleDevices(
                $this->tokens,
                $title,
                $body,
                $data
            );
        } else {
            $fcmService->sendToDevice(
                $this->tokens,
                $title,
                $body,
                $data
            );
        }
    }
}
