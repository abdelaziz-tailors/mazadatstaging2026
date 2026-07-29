<?php

namespace App\Providers;

use App\Models\SellerSubmission;
use App\Models\UserSubscription;
use App\Support\AdminNotificationsFeed;
use App\Support\PartnerDashboardScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Real, live "items awaiting admin review" count for the header
        // notifications bell — shared with every dashboard page (not just
        // the home page) since the header itself is rendered by the shared
        // layout. Seller submissions are scoped to the current partner
        // admin the same way the Seller Submissions list page already is;
        // pending subscriptions aren't partner-scoped, matching how
        // UserSubscriptionController itself computes that count.
        View::composer('dashboard.layouts.app', function ($view) {
            $count = 0;
            $notifications = collect();
            if (Auth::guard('admin')->check()) {
                $submissions = SellerSubmission::query();
                PartnerDashboardScope::scopeSellerSubmissions($submissions);
                $count += $submissions->whereNotIn('status', ['approved', 'rejected'])->count();

                $count += UserSubscription::where('status', 'pending')->orWhereNull('status')->count();

                $notifications = AdminNotificationsFeed::latest(5);
            }

            $view->with('headerPendingReviewCount', $count);
            $view->with('headerNotifications', $notifications);
        });
    }
}
