<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\UserSubscriptionController;
use App\Models\Admin;
use App\Models\Package;
use App\Models\User\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers two changes to /admin/user-subscriptions:
 *
 * 1. The "package" column was removed from the table — per explicit request
 *    ("الباقة هو هو نوع الاشتراك"), it's redundant with the already-present
 *    "subscription_type" column. Only the display column and its JS mapping
 *    were dropped; the underlying package_id column/relation are untouched
 *    (still used by the show page).
 *
 * 2. A filter panel was added on top of UserSubscriptionController::get_data():
 *    user name, subscription type, approval status, and date range — the
 *    same pattern already built for buyers/vendors/auctions/orders/partners/
 *    packages, built from real, already-stored columns, no new schema.
 */
class UserSubscriptionsFilterPanelTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        $permission = Permission::firstOrCreate(['name' => 'view user subscriptions', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);

        return $admin;
    }

    private function createSubscription(array $overrides = []): UserSubscription
    {
        $user = $overrides['user'] ?? User::create([
            'name' => 'Buyer ' . random_int(100000, 999999),
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ]);
        unset($overrides['user']);

        $package = Package::create([
            'name' => json_encode(['ar' => 'باقة']),
            'description' => json_encode(['ar' => '']),
            'features' => json_encode(['ar' => []]),
            'coin' => 0,
            'price' => 0,
        ]);

        return UserSubscription::create(array_merge([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'status' => 'pending',
        ], $overrides));
    }

    private function callGetData(array $params)
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $request = Request::create('/admin/user-subscriptions/getData', 'POST', array_merge([
            'draw' => 1, 'start' => 0, 'length' => 50,
        ], $params));
        app()->instance('request', $request);

        $response = (new UserSubscriptionController())->get_data($request);

        return collect(json_decode($response->getContent(), true)['data'])->pluck('id');
    }

    public function test_filter_user_matches_by_partial_name()
    {
        $matchUser = User::create(['name' => 'Ahmed Special Buyer', 'phone' => '01' . random_int(100000000, 999999999), 'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male']);
        $otherUser = User::create(['name' => 'Someone Else', 'phone' => '01' . random_int(100000000, 999999999), 'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male']);
        $match = $this->createSubscription(['user' => $matchUser]);
        $other = $this->createSubscription(['user' => $otherUser]);

        $ids = $this->callGetData(['filter_user' => 'Special Buyer']);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_filter_subscription_type_matches_exact_type()
    {
        $monthly = $this->createSubscription(['subscription_type' => 'monthly']);
        $annual = $this->createSubscription(['subscription_type' => 'annual']);

        $ids = $this->callGetData(['filter_subscription_type' => 'monthly']);

        $this->assertTrue($ids->contains($monthly->id));
        $this->assertFalse($ids->contains($annual->id));
    }

    public function test_filter_status_approved_excludes_pending_and_rejected()
    {
        $approved = $this->createSubscription(['status' => 'approved']);
        $pending = $this->createSubscription(['status' => 'pending']);
        $rejected = $this->createSubscription(['status' => 'rejected']);

        $ids = $this->callGetData(['filter_status' => 'approved']);

        $this->assertTrue($ids->contains($approved->id));
        $this->assertFalse($ids->contains($pending->id));
        $this->assertFalse($ids->contains($rejected->id));
    }

    public function test_filter_date_range_excludes_subscriptions_outside_the_range()
    {
        $inRange = $this->createSubscription();
        $inRange->created_at = now()->subDays(5);
        $inRange->save();

        $outOfRange = $this->createSubscription();
        $outOfRange->created_at = now()->subDays(30);
        $outOfRange->save();

        $ids = $this->callGetData([
            'filter_date_from' => now()->subDays(10)->toDateString(),
            'filter_date_to' => now()->subDays(1)->toDateString(),
        ]);

        $this->assertTrue($ids->contains($inRange->id));
        $this->assertFalse($ids->contains($outOfRange->id));
    }

    public function test_combined_filters_apply_together_as_an_intersection()
    {
        $match = $this->createSubscription(['subscription_type' => 'monthly', 'status' => 'approved']);
        $wrongType = $this->createSubscription(['subscription_type' => 'annual', 'status' => 'approved']);
        $wrongStatus = $this->createSubscription(['subscription_type' => 'monthly', 'status' => 'pending']);

        $ids = $this->callGetData([
            'filter_subscription_type' => 'monthly',
            'filter_status' => 'approved',
        ]);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($wrongType->id));
        $this->assertFalse($ids->contains($wrongStatus->id));
    }

    public function test_no_filters_returns_unfiltered_results()
    {
        $subscription = $this->createSubscription();

        $ids = $this->callGetData([]);

        $this->assertTrue($ids->contains($subscription->id));
    }

    public function test_filter_panel_markup_is_rendered_on_the_index_page()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new UserSubscriptionController())->index()->render();

        $this->assertStringContainsString('id="userSubscriptionsFilterPanel"', $html);
        $this->assertStringContainsString('id="filter_user"', $html);
        $this->assertStringContainsString('id="filter_subscription_type"', $html);
        $this->assertStringContainsString('id="filter_status"', $html);
        $this->assertStringContainsString('id="filter_date_from"', $html);
        $this->assertStringContainsString('id="filter_date_to"', $html);
        $this->assertStringContainsString('id="filter_reset"', $html);
        $this->assertStringContainsString('md-wide-search', $html);
    }

    /**
     * The "package" column must be gone from both the visible header and
     * the DataTables JS column mapping, since it duplicated
     * "subscription_type" — the underlying package_id DB column/relation
     * are untouched (still used by the show page), so the raw attribute may
     * still ride along in the JSON payload; what matters is it's no longer
     * mapped to a visible table column.
     */
    public function test_package_column_is_removed_from_the_table()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $indexHtml = (new UserSubscriptionController())->index()->render();

        $this->assertStringNotContainsString("data: 'package_id'", $indexHtml);
        $this->assertStringNotContainsString(TranslationHelper::translate('package'), $indexHtml);
    }
}
