<?php

namespace App\Console\Commands;

use App\Helpers\TranslationHelper;
use App\Models\LiveVideo;
use App\Models\User\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendUpcomingAuctionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:upcoming-auction-reminders';

    /**
     * The console command description.
     *
     * @var string
     *
     * Notifies all three account roles: the "مشترك" partner who created this
     * specific auction (LiveVideo.user_id), the sellers who have items in it
     * (a real, targeted relationship), and every active buyer as a general
     * broadcast (there's no per-auction "interested buyers" list to target
     * more precisely — see docs/api/notifications.md).
     */
    protected $description = "Notify the organizer, sellers, and buyers one hour before an auction starts";

    public function handle()
    {
        $now = Carbon::now();
        $windowEnd = $now->copy()->addHour();

        $candidates = LiveVideo::query()
            ->whereNull('status')
            ->whereNull('upcoming_reminder_sent_at')
            ->whereNotNull('date_start_at')
            ->whereNotNull('time_start_at')
            ->get();

        $sent = 0;

        foreach ($candidates as $liveVideo) {
            try {
                $startsAt = Carbon::parse($liveVideo->date_start_at.' '.$liveVideo->time_start_at);
            } catch (\Throwable $e) {
                continue;
            }

            if ($startsAt->lessThan($now) || $startsAt->greaterThan($windowEnd)) {
                continue;
            }

            $notifiedIds = [];

            if ($liveVideo->user_id) {
                NotificationService::notify(
                    (int) $liveVideo->user_id,
                    'upcoming_auction',
                    TranslationHelper::translate('upcoming auction title'),
                    TranslationHelper::translate('upcoming auction body'),
                    ['live_video_id' => $liveVideo->id]
                );
                $notifiedIds[] = (int) $liveVideo->user_id;
                $sent++;
            }

            $sellerIds = $liveVideo->video_items()
                ->whereNotNull('seller_id')
                ->distinct()
                ->pluck('seller_id')
                ->diff($notifiedIds);

            foreach ($sellerIds as $sellerId) {
                NotificationService::notify(
                    (int) $sellerId,
                    'upcoming_auction',
                    TranslationHelper::translate('upcoming auction title'),
                    TranslationHelper::translate('upcoming auction body'),
                    ['live_video_id' => $liveVideo->id]
                );
                $sent++;
            }

            User::query()
                ->whereIn('user_type', ['buyer', 'buyer_vendor'])
                ->where('is_active', 1)
                ->whereNotIn('id', $notifiedIds)
                ->select('id')
                ->chunkById(200, function ($buyers) use ($liveVideo, &$sent) {
                    foreach ($buyers as $buyer) {
                        NotificationService::notify(
                            (int) $buyer->id,
                            'upcoming_auction',
                            TranslationHelper::translate('upcoming auction title'),
                            TranslationHelper::translate('upcoming auction body'),
                            ['live_video_id' => $liveVideo->id]
                        );
                        $sent++;
                    }
                });

            $liveVideo->update(['upcoming_reminder_sent_at' => $now]);
        }

        $this->info("Upcoming auction reminders sent: {$sent}.");
    }
}
