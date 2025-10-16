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

    public function __construct($tokens, $title, $body, $data = [])
    {
        $this->tokens = $tokens;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    public function handle(FCMService $fcmService)
    {
        if (is_array($this->tokens)) {
            $fcmService->sendToMultipleDevices(
                $this->tokens,
                $this->title,
                $this->body,
                $this->data
            );
        } else {
            $fcmService->sendToDevice(
                $this->tokens,
                $this->title,
                $this->body,
                $this->data
            );
        }
    }
}
