<?php

namespace App\Jobs;

use App\Services\AuctionRecordingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReconcileAuctionRecording implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $auctionId;

    public function __construct(int $auctionId)
    {
        $this->auctionId = $auctionId;
        $this->onQueue(config('recording.queue'));
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(AuctionRecordingService $service)
    {
        $service->reconcile($this->auctionId);
    }
}
