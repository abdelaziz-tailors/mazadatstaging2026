<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\PartnerFinanceController;
use App\Models\Admin;
use App\Models\Package;
use App\Models\User\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * /admin/partner-finance/wallet shows different data depending on who's
 * looking, per explicit request:
 *
 * - A partner (Admin.type === 'partner') sees their own real auction
 *   earnings — unchanged, still covered by PartnerWalletCardTest.
 * - The main super-admin (Admin.type === 'admin') sees subscription
 *   revenue instead: subscription payments never touch wallet_balance or
 *   wallet_transactions at all (UserSubscriptionController::approve() only
 *   flips the subscriber's user_type/is_verified), so this is the only way
 *   for that money to be visible anywhere in the finance section.
 *
 * The "current balance" figure for the super-admin view is the sum of
 * only APPROVED subscriptions' price — pending/rejected requests haven't
 * had their payment confirmed by an admin yet, so they aren't counted as
 * real collected revenue. The table below lists those same approved
 * subscriptions (date, subscriber name, subscription type, price).
 */
class SuperAdminSubscriptionsWalletTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Super Admin',
            'email' => 'super' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
    }

    private function createSubscriber(string $name = 'Subscriber'): User
    {
        return User::create([
            'name' => $name,
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ]);
    }

    private function createPackage(): Package
    {
        return Package::create([
            'name' => json_encode(['ar' => 'باقة', 'en' => 'Package']),
            'description' => json_encode(['ar' => 'وصف', 'en' => 'Description']),
            'price' => 100,
            'is_active' => 1,
        ]);
    }

    public function test_super_admin_sees_the_sum_of_approved_subscriptions_as_the_balance()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());
        $package = $this->createPackage();

        UserSubscription::create([
            'user_id' => $this->createSubscriber('Approved One')->id,
            'package_id' => $package->id,
            'price' => 300,
            'subscription_type' => 'monthly',
            'status' => 'approved',
        ]);
        UserSubscription::create([
            'user_id' => $this->createSubscriber('Approved Two')->id,
            'package_id' => $package->id,
            'price' => 500,
            'subscription_type' => 'annual',
            'status' => 'approved',
        ]);
        // Not counted: not yet confirmed as collected money.
        UserSubscription::create([
            'user_id' => $this->createSubscriber('Pending One')->id,
            'package_id' => $package->id,
            'price' => 1000,
            'subscription_type' => 'monthly',
            'status' => 'pending',
        ]);
        UserSubscription::create([
            'user_id' => $this->createSubscriber('Rejected One')->id,
            'package_id' => $package->id,
            'price' => 2000,
            'subscription_type' => 'annual',
            'status' => 'rejected',
        ]);

        $html = (new PartnerFinanceController())->wallet()->render();

        $this->assertStringContainsString('800.00', $html);
        $this->assertStringContainsString(TranslationHelper::translate('subscriptions_total_revenue'), $html);
        $this->assertStringNotContainsString('3,800.00', $html);
    }

    public function test_super_admin_wallet_table_lists_only_approved_subscriptions()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());
        $package = $this->createPackage();

        UserSubscription::create([
            'user_id' => $this->createSubscriber('Visible Subscriber')->id,
            'package_id' => $package->id,
            'price' => 300,
            'subscription_type' => 'monthly',
            'status' => 'approved',
        ]);
        UserSubscription::create([
            'user_id' => $this->createSubscriber('Hidden Pending Subscriber')->id,
            'package_id' => $package->id,
            'price' => 400,
            'subscription_type' => 'annual',
            'status' => 'pending',
        ]);

        $html = (new PartnerFinanceController())->wallet()->render();

        // Scoped to the subscriptions table itself: the shared dashboard
        // header's notification bell also surfaces "new subscription
        // request" items for pending subscriptions, so asserting against
        // the whole page would false-negative on that unrelated banner.
        $tableStart = strpos($html, '<table');
        $tableEnd = strrpos($html, '</table>') + strlen('</table>');
        $tableHtml = substr($html, $tableStart, $tableEnd - $tableStart);

        $this->assertStringContainsString('Visible Subscriber', $tableHtml);
        $this->assertStringNotContainsString('Hidden Pending Subscriber', $tableHtml);
        $this->assertStringContainsString(TranslationHelper::translate('Monthly'), $tableHtml);
    }

    public function test_super_admin_wallet_shows_zero_with_no_approved_subscriptions()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerFinanceController())->wallet()->render();

        $this->assertStringContainsString('0.00', $html);
        $this->assertStringContainsString(TranslationHelper::translate('nothing_found'), $html);
    }

    /**
     * The super-admin view must never show the wallet_transactions table
     * (auction commission data) — that's the partner-only view.
     */
    public function test_super_admin_wallet_does_not_show_the_partner_auction_transactions_table()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerFinanceController())->wallet()->render();

        $this->assertStringNotContainsString(TranslationHelper::translate('subscriber_wallet_balance_after'), $html);
        $this->assertStringNotContainsString(TranslationHelper::translate('order_number'), $html);
    }
}
