<?php

namespace App\Console\Commands;

use App\Http\Controllers\FirebaseController;
use App\Models\LiveVideo;
use Illuminate\Console\Command;

class CheckRecordedVideoStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recordedvideostart:check';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update status of recorded videos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = now();

        // Only pending auctions that should transition to "start": current time is within
        // [date_start + time_start, date_end + time_end], and not already started/ended.
        $videos = LiveVideo::query()
            ->where('status', 'pending')
            ->whereDate('date_start_at', '<=', $now)
            ->whereDate('date_end_at', '>=', $now)
            ->where(function ($q) use ($now) {
                $q->where(function ($q2) use ($now) {
                    // Normal: daily window does not cross midnight (time_start <= time_end)
                    $q2->whereRaw('time_start_at <= time_end_at')
                        ->whereRaw('TIMESTAMP(date_start_at, time_start_at) <= ?', [$now])
                        ->whereRaw('TIMESTAMP(date_end_at, time_end_at) >= ?', [$now]);
                })
                    ->orWhere(function ($q2) use ($now) {
                        // Overnight: time_start > time_end (same calendar-day pair in DB)
                        $q2->whereRaw('time_start_at > time_end_at')
                            ->where(function ($q3) use ($now) {
                                $q3->whereTime('time_start_at', '<=', $now)
                                    ->orWhereTime('time_end_at', '>=', $now);
                            });
                    });
            })
            ->get();



        foreach ($videos as $video) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($video) {
                // Keep the item -> auction lock order used by bidding and closure.
                $items = \App\Models\LiveVideoItem::without('pieces')
                    ->where('live_video_id', $video->id)->orderBy('id')->lockForUpdate()->get();
                $video = LiveVideo::whereKey($video->id)->lockForUpdate()->first();
                if (! $video || $video->status !== 'pending') { return; }
                $video->update(['status' => 'start', 'start_at' => now()]);
                foreach ($items as $item) {
                    $item->update(['status' => 'working']);
                    \Illuminate\Support\Facades\DB::afterCommit(function () use ($item) {
                        try {
                            app(FirebaseController::class)->ChangeLiveItemStatus($item->live_video_id, $item->id, 'working');
                        } catch (\Exception $e) {}
                    });
                }
                \Illuminate\Support\Facades\DB::afterCommit(function () use ($video) {
                    try {
                        app(FirebaseController::class)->ChangeLiveStatus($video->id, 'start');
                    } catch (\Exception $e) {}
                });
            }, 3);
        }
        $this->info('Live video status check completed.');
    }
}
