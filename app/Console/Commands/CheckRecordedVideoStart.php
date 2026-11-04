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
            ->where(function ($q) {
                $q->where('type', '!=', 'recorded')->orWhereNull('type');
            })
            ->where('status', 'pending')
            ->whereNull('schedule_start_utc')
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

        $nowSaudi = now('Africa/Cairo');

        $recordedVideos = LiveVideo::query()
            ->where('type', 'recorded')
            ->where('status', 'pending')
            ->whereNull('schedule_start_utc')
            ->whereDate('date_start_at', '<=', $nowSaudi)
            ->whereDate('date_end_at', '>=', $nowSaudi)
            ->where(function ($q) use ($nowSaudi) {
                $q->where(function ($q2) use ($nowSaudi) {
                    // Normal: daily window does not cross midnight (time_start <= time_end)
                    $q2->whereRaw('time_start_at <= time_end_at')
                        ->whereRaw('TIMESTAMP(date_start_at, time_start_at) <= ?', [$nowSaudi])
                        ->whereRaw('TIMESTAMP(date_end_at, time_end_at) >= ?', [$nowSaudi]);
                })
                    ->orWhere(function ($q2) use ($nowSaudi) {
                        // Overnight: time_start > time_end (same calendar-day pair in DB)
                        $q2->whereRaw('time_start_at > time_end_at')
                            ->where(function ($q3) use ($nowSaudi) {
                                $q3->whereTime('time_start_at', '<=', $nowSaudi)
                                    ->orWhereTime('time_end_at', '>=', $nowSaudi);
                            });
                    });
            })
            ->get();

        $videos = $videos->concat($recordedVideos);



        // Explicit UTC schedules are evaluated independently of legacy wall-clock columns.
        $nowUtc = now('UTC')->format('Y-m-d H:i:s');
        $utcVideos = LiveVideo::query()
            ->where('type', 'recorded')
            ->where('status', 'pending')
            ->whereNotNull('schedule_start_utc')
            ->whereNotNull('schedule_end_utc')
            ->where('schedule_start_utc', '<=', $nowUtc)
            ->where('schedule_end_utc', '>', $nowUtc)
            ->get();
        $videos = $videos->concat($utcVideos);

        foreach ($videos as $video) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($video) {
                // Keep the item -> auction lock order used by bidding and closure.
                $items = \App\Models\LiveVideoItem::without('pieces')
                    ->where('live_video_id', $video->id)->orderBy('id')->lockForUpdate()->get();
                $video = LiveVideo::whereKey($video->id)->lockForUpdate()->first();
                if (! $video || $video->status !== 'pending') { return; }
                if ($video->schedule_start_utc !== null) {
                    if (! $video->schedule_end_utc) { return; }
                    $actual = now('UTC');
                    $begin = \Carbon\CarbonImmutable::parse((string) $video->schedule_start_utc, 'UTC');
                    $finish = \Carbon\CarbonImmutable::parse((string) $video->schedule_end_utc, 'UTC');
                    if ($finish->lte($begin) || $actual->lt($begin) || $actual->gte($finish)) { return; }
                }

                $video->update(['status' => 'start']);
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
