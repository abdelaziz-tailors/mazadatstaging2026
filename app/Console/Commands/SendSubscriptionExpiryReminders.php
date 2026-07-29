<?php

namespace App\Console\Commands;

use App\Helpers\TranslationHelper;
use App\Models\UserSubscription;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendSubscriptionExpiryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:subscription-expiry-reminders';

    /**
     * The console command description.
     *
     * @var string
     *
     * Note: the codebase has no separate "trial" concept — every subscription
     * (free or paid) is a `UserSubscription` row with an `expires_at`, so this
     * single job covers both cases the product asked for.
     */
    protected $description = "Notify users whose subscription expires within 24 hours";

    public function handle()
    {
        $now = Carbon::now();
        $windowEnd = $now->copy()->addDay();

        $subscriptions = UserSubscription::query()
            ->where('status', 'approved')
            ->whereNull('expiry_reminder_sent_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>=', $now)
            ->where('expires_at', '<=', $windowEnd)
            ->get();

        foreach ($subscriptions as $subscription) {
            NotificationService::notify(
                (int) $subscription->user_id,
                'payment_reminder',
                TranslationHelper::translate('payment reminder title'),
                TranslationHelper::translate('subscription expiry body'),
                ['user_subscription_id' => $subscription->id]
            );

            $subscription->update(['expiry_reminder_sent_at' => $now]);
        }

        $this->info("Subscription expiry reminders sent: {$subscriptions->count()}.");
    }
}
