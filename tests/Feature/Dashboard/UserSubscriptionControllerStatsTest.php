<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\UserSubscriptionController;
use App\Models\Admin;
use App\Models\User\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Hits the controller directly rather than the HTTP route — see the note in
 * OrderControllerStatsTest for why (a pre-existing dashboard-locale redirect
 * quirk unrelated to this feature).
 */
class UserSubscriptionControllerStatsTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser(): User
    {
        return User::create([
            'name' => 'Sub User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ]);
    }

    private function createSubscription(?string $status): UserSubscription
    {
        return UserSubscription::create([
            'user_id' => $this->createUser()->id,
            'status' => $status,
        ]);
    }

    /**
     * The stats are a global count over a real, shared testing database, so
     * assertions compare before/after deltas instead of exact totals.
     */
    public function test_index_computes_correct_subscription_stats()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $baseline = UserSubscription::query()->selectRaw("
            count(*) as total,
            sum(status = 'approved') as approved,
            sum(status = 'pending' or status is null) as pending,
            sum(status = 'rejected') as rejected
        ")->first();

        $this->createSubscription('approved');
        $this->createSubscription('approved');
        $this->createSubscription('pending');
        $this->createSubscription('rejected');

        $view = (new UserSubscriptionController())->index();
        $stats = $view->getData()['stats'];

        $this->assertEquals((int) $baseline->total + 4, $stats['total']);
        $this->assertEquals((int) $baseline->approved + 2, $stats['approved']);
        $this->assertEquals((int) $baseline->pending + 1, $stats['pending']);
        $this->assertEquals((int) $baseline->rejected + 1, $stats['rejected']);
    }

    public function test_index_renders_the_stat_brief_with_the_total_subscriptions_label()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $view = (new UserSubscriptionController())->index();

        $this->assertArrayHasKey('stats', $view->getData());
        $this->assertStringContainsString(TranslationHelper::translate('total_subscriptions'), $view->render());
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
    }
}
