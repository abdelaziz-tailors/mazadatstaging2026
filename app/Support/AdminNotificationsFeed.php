<?php

namespace App\Support;

use App\Helpers\TranslationHelper;
use App\Models\LiveVideo;
use App\Models\Order;
use App\Models\SellerSubmission;
use App\Models\UserSubscription;
use Illuminate\Support\Collection;

/**
 * Real, live "recent activity" feed for the admin header's notification
 * bell — there is no persisted admin-notifications table, so this merges
 * the latest rows from the four real event sources the platform already
 * tracks (seller submissions, subscription requests, orders, auctions),
 * scoped the same way the header's pending-review badge count already is,
 * and returns the newest N across all of them combined.
 */
class AdminNotificationsFeed
{
    public static function latest(int $limit = 5): Collection
    {
        $items = collect();

        $submissions = SellerSubmission::query()->with('user')->latest()->limit($limit);
        PartnerDashboardScope::scopeSellerSubmissions($submissions);
        foreach ($submissions->get() as $submission) {
            $items->push([
                'type' => 'seller_submission',
                'icon' => 'fa-solid fa-clipboard-list',
                'color' => 'purple',
                'title' => TranslationHelper::translate('new_seller_submission'),
                'description' => trim(($submission->sheep_type ?? '-') . ' — ' . ($submission->user->name ?? '-'), ' —'),
                'url' => route('admin.seller-submissions.show', $submission->id),
                'created_at' => $submission->created_at,
            ]);
        }

        // Not partner-scoped, matching AppServiceProvider's badge-count
        // composer and UserSubscriptionController's own pending count.
        foreach (UserSubscription::query()->with('user')->latest()->limit($limit)->get() as $subscription) {
            $items->push([
                'type' => 'user_subscription',
                'icon' => 'fa-solid fa-crown',
                'color' => 'warning',
                'title' => TranslationHelper::translate('new_subscription_request'),
                'description' => $subscription->user->name ?? '-',
                'url' => route('admin.user-subscriptions.show', $subscription->id),
                'created_at' => $subscription->created_at,
            ]);
        }

        $orders = Order::query()->with('buyer')->latest()->limit($limit);
        PartnerDashboardScope::scopeOrders($orders);
        foreach ($orders->get() as $order) {
            $items->push([
                'type' => 'order',
                'icon' => 'fa-solid fa-cart-shopping',
                'color' => 'info',
                'title' => TranslationHelper::translate('new_order'),
                'description' => '#' . $order->id . ' — ' . ($order->buyer->name ?? '-'),
                'url' => route('admin.orders.show', $order->id),
                'created_at' => $order->created_at,
            ]);
        }

        $auctions = LiveVideo::query()->latest()->limit($limit);
        PartnerDashboardScope::scopeLiveVideos($auctions);
        foreach ($auctions->get() as $auction) {
            $items->push([
                'type' => 'auction',
                'icon' => 'fa-solid fa-video',
                'color' => 'success',
                'title' => TranslationHelper::translate('new_auction'),
                'description' => $auction->title_ar ?? $auction->title ?? '-',
                'url' => route('admin.auctions.show', $auction->id),
                'created_at' => $auction->created_at,
            ]);
        }

        return $items->sortByDesc('created_at')->take($limit)->values();
    }
}
