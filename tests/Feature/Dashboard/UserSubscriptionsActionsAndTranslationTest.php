<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\UserSubscriptionController;
use App\Models\Admin;
use App\Models\Package;
use App\Models\User\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The user-subscriptions list (/admin/user-subscriptions) had a generic
 * cogs-icon dropdown for its row actions instead of the round icon-button
 * component used elsewhere. First fixed to a standalone "view" icon + kebab
 * for the status-dependent approve/reject/delete actions; per a later,
 * explicit request (matching the seller-submissions page's pattern), the
 * kebab was removed entirely too — approve/reject/delete are now all
 * standalone icons alongside view, with no dropdown at all. Also covers a
 * pre-existing bug: its "Expires At" column header was left in raw English
 * — a self-healed translation that leaked English text into the Arabic
 * locale file (the same recurring bug pattern seen on several other pages
 * this session).
 */
class UserSubscriptionsActionsAndTranslationTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithPermissions(): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        foreach (['view user subscriptions', 'delete user subscription'] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    private function createSubscription(string $status = 'pending'): UserSubscription
    {
        $user = User::create([
            'name' => 'Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);
        $package = Package::create([
            'name' => json_encode(['ar' => 'باقة']), 'description' => json_encode(['ar' => '']),
            'features' => json_encode(['ar' => []]), 'coin' => 0, 'price' => 0,
        ]);

        return UserSubscription::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'status' => $status,
        ]);
    }

    public function test_actions_partial_has_standalone_icons_not_a_dropdown()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $item = $this->createSubscription('pending');

        $html = view('dashboard.pages.user-subscriptions.actions', ['item' => $item])->render();

        $this->assertStringContainsString('md-icon-btn', $html);
        $this->assertStringContainsString(route('admin.user-subscriptions.show', $item->id), $html);
        $this->assertStringContainsString('fa-eye', $html);
        $this->assertStringNotContainsString('fa-ellipsis-vertical', $html);
        $this->assertStringNotContainsString('dropdown-menu', $html);
        $this->assertStringNotContainsString('btn-group', $html);
        $this->assertStringNotContainsString('fa-cogs', $html);
    }

    public function test_pending_subscription_actions_offer_standalone_approve_and_reject_icons()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $item = $this->createSubscription('pending');

        $html = view('dashboard.pages.user-subscriptions.actions', ['item' => $item])->render();

        $this->assertStringContainsString(route('admin.user-subscriptions.approve', $item->id), $html);
        $this->assertStringContainsString('#rejectSubscriptionModal-' . $item->id, $html);
        $this->assertStringContainsString('md-icon-btn-success', $html);
        $this->assertStringContainsString('fa-check', $html);
        $this->assertStringContainsString('md-icon-btn-danger', $html);
        $this->assertStringContainsString('fa-times', $html);
    }

    public function test_approved_subscription_actions_only_offer_reject()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $item = $this->createSubscription('approved');

        $html = view('dashboard.pages.user-subscriptions.actions', ['item' => $item])->render();

        $this->assertStringNotContainsString(route('admin.user-subscriptions.approve', $item->id), $html);
        $this->assertStringContainsString('#rejectSubscriptionModal-' . $item->id, $html);
    }

    public function test_rejected_subscription_actions_offer_approve_again()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $item = $this->createSubscription('rejected');

        $html = view('dashboard.pages.user-subscriptions.actions', ['item' => $item])->render();

        $this->assertStringContainsString(route('admin.user-subscriptions.approve', $item->id), $html);
    }

    public function test_delete_action_appears_when_permitted()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $item = $this->createSubscription('pending');

        $html = view('dashboard.pages.user-subscriptions.actions', ['item' => $item])->render();

        $this->assertStringContainsString('#deleteSubscriptionModal-' . $item->id, $html);
    }

    public function test_delete_action_is_hidden_without_delete_permission()
    {
        $admin = Admin::create([
            'name' => 'No Delete Admin',
            'email' => 'nodelete' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
        $permission = Permission::firstOrCreate(['name' => 'view user subscriptions', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $item = $this->createSubscription('pending');

        $html = view('dashboard.pages.user-subscriptions.actions', ['item' => $item])->render();

        $this->assertStringNotContainsString('#deleteSubscriptionModal-' . $item->id, $html);
    }

    /**
     * Regression guard: 'expires_at' translated to the literal English
     * string "Expires At" even in the Arabic locale (a self-healed
     * fallback that was never given a real Arabic value).
     */
    public function test_expires_at_column_is_translated_to_arabic_not_left_in_english()
    {
        $this->assertEquals('تاريخ الانتهاء', TranslationHelper::translate('expires_at', 'ar'));
        $this->assertNotEquals('Expires At', TranslationHelper::translate('expires_at', 'ar'));
    }

    public function test_index_page_renders_the_translated_expires_at_header()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new UserSubscriptionController())->index()->render();

        $this->assertStringContainsString(TranslationHelper::translate('expires_at'), $html);
    }

    public function test_index_page_has_a_descriptive_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new UserSubscriptionController())->index()->render();

        $this->assertStringContainsString('searchPlaceholder', $html);
        $this->assertStringContainsString(TranslationHelper::translate('search_user_subscriptions_placeholder'), $html);
    }

    public function test_index_page_toolbar_has_horizontal_padding()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new UserSubscriptionController())->index()->render();

        $this->assertStringContainsString(
            'd-flex flex-wrap justify-content-between align-items-center mb-3 px-2',
            $html
        );
    }
}
