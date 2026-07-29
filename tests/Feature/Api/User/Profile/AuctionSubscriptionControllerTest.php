<?php

namespace Tests\Feature\Api\User\Profile;

use App\Models\Package;
use App\Models\UserSubscription;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuctionSubscriptionControllerTest extends TestCase
{
    use DatabaseTransactions;

    private const API_KEY = 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM';

    private function headers(): array
    {
        return [
            'x-api-key' => self::API_KEY,
            'Accept-Language' => 'en',
        ];
    }

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ], $overrides));
    }

    private function createPackage(array $overrides = []): Package
    {
        return Package::create(array_merge([
            'name' => json_encode(['ar' => 'الباقة الذهبية', 'en' => 'Gold Package']),
            'description' => json_encode(['ar' => 'وصف', 'en' => 'Description']),
            'features' => json_encode([
                'ar' => ['إنشاء عدد غير محدود من المزادات', 'بث مباشر عالي الجودة'],
                'en' => ['Unlimited auction creation', 'High quality live streaming'],
            ]),
            'auctions_limit' => 20,
            'monthly_price' => 300,
            'annual_price' => 2999,
            'is_active' => 1,
        ], $overrides));
    }

    public function test_get_plans_returns_active_packages_with_localized_features()
    {
        $this->createPackage();
        Package::create([
            'name' => json_encode(['ar' => 'غير نشطة', 'en' => 'Inactive']),
            'description' => json_encode(['ar' => '-', 'en' => '-']),
            'is_active' => 0,
        ]);

        $response = $this->withHeaders($this->headers())->getJson('/api/user/subscription-plans');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $plans = collect($response->json('data'));
        $this->assertTrue($plans->every(fn ($p) => $p['name'] !== 'غير نشطة'));
        $goldPlan = $plans->firstWhere('name', 'Gold Package');
        $this->assertNotNull($goldPlan);
        $this->assertEquals(['Unlimited auction creation', 'High quality live streaming'], $goldPlan['features']);
    }

    public function test_get_status_returns_no_subscription_when_user_never_subscribed()
    {
        $user = $this->createUser();
        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/subscription-status');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.has_subscription', false);
    }

    public function test_get_status_returns_current_subscription_with_expiry_and_package_features()
    {
        $user = $this->createUser();
        $package = $this->createPackage();

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'subscription_type' => 'annual',
            'auctions_limit' => 20,
            'remaining_auctions' => 15,
            'expires_at' => now()->addYear(),
            'price' => 2999,
            'status' => 'approved',
        ]);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/subscription-status');

        $response->assertStatus(200)
            ->assertJsonPath('data.has_subscription', true)
            ->assertJsonPath('data.subscription.id', $subscription->id)
            ->assertJsonPath('data.subscription.status', 'approved')
            ->assertJsonPath('data.subscription.price', 2999)
            ->assertJsonPath('data.subscription.started_at', now()->format('Y-m-d'))
            ->assertJsonPath('data.subscription.package.name', 'Gold Package')
            ->assertJsonPath('data.subscription.package.features', [
                'Unlimited auction creation',
                'High quality live streaming',
            ]);
    }

    public function test_subscribe_creates_a_pending_subscription()
    {
        $user = $this->createUser();
        $package = $this->createPackage();
        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/user/subscription', [
            'package_id' => $package->id,
            'subscription_type' => 'monthly',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'package_id' => $package->id,
            'subscription_type' => 'monthly',
            'status' => 'pending',
        ]);
    }

    public function test_renew_fails_when_user_has_no_subscription_to_renew()
    {
        $user = $this->createUser();
        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/user/subscription/renew');

        $response->assertStatus(200)->assertJson(['success' => false]);
        $this->assertDatabaseCount('user_subscriptions', 0);
    }

    public function test_renew_creates_a_new_pending_subscription_reusing_the_same_package_and_type()
    {
        $user = $this->createUser();
        $package = $this->createPackage();

        UserSubscription::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'subscription_type' => 'annual',
            'auctions_limit' => 20,
            'remaining_auctions' => 0,
            'expires_at' => now()->subDay(), // expired
            'price' => 2999,
            'status' => 'approved',
        ]);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/user/subscription/renew');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'package_id' => $package->id,
            'subscription_type' => 'annual',
            'status' => 'pending',
        ]);
        $this->assertEquals(2, UserSubscription::where('user_id', $user->id)->count());
    }

    public function test_renew_can_switch_to_a_different_package_and_billing_cycle()
    {
        $user = $this->createUser();
        $oldPackage = $this->createPackage();
        $newPackage = $this->createPackage(['monthly_price' => 500, 'annual_price' => 4999]);

        UserSubscription::create([
            'user_id' => $user->id,
            'package_id' => $oldPackage->id,
            'subscription_type' => 'monthly',
            'auctions_limit' => 20,
            'remaining_auctions' => 5,
            'expires_at' => now()->addWeek(),
            'price' => 300,
            'status' => 'approved',
        ]);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/user/subscription/renew', [
            'package_id' => $newPackage->id,
            'subscription_type' => 'annual',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'package_id' => $newPackage->id,
            'subscription_type' => 'annual',
            'price' => 4999,
            'status' => 'pending',
        ]);
    }

    /**
     * These routes sit behind the "auth:api" route-group middleware
     * (routes/api/user.php), so an unauthenticated request never reaches the
     * controller at all — it's rejected with Laravel's own 401 first.
     */
    public function test_get_status_requires_authentication()
    {
        $response = $this->withHeaders($this->headers())->getJson('/api/user/subscription-status');

        $response->assertStatus(401);
    }

    public function test_subscribe_requires_authentication()
    {
        $response = $this->withHeaders($this->headers())->postJson('/api/user/subscription', []);

        $response->assertStatus(401);
    }

    public function test_renew_requires_authentication()
    {
        $response = $this->withHeaders($this->headers())->postJson('/api/user/subscription/renew');

        $response->assertStatus(401);
    }

    /**
     * The route middleware makes the controller's own "!auth('api')->user()"
     * guard unreachable over HTTP (see the note above) — call it directly so
     * that defensive branch is still exercised.
     */
    public function test_controller_methods_return_failed_response_without_an_authenticated_user()
    {
        $controller = new \App\Http\Controllers\api\User\Profile\AuctionSubscriptionController();

        $this->assertFalse($controller->getStatus()->getData()->success);
        $this->assertFalse($controller->subscribe(new \Illuminate\Http\Request())->getData()->success);
        $this->assertFalse($controller->renew(new \Illuminate\Http\Request())->getData()->success);
        $this->assertFalse($controller->getHistory()->getData()->success);
    }

    public function test_get_history_returns_all_subscriptions_for_the_user_ordered_by_latest()
    {
        $user = $this->createUser();
        $package = $this->createPackage();

        $older = UserSubscription::create([
            'user_id' => $user->id, 'package_id' => $package->id, 'subscription_type' => 'monthly',
            'auctions_limit' => 20, 'remaining_auctions' => 0, 'expires_at' => now()->subMonth(),
            'price' => 300, 'status' => 'rejected', 'created_at' => now()->subDays(2),
        ]);
        $newer = UserSubscription::create([
            'user_id' => $user->id, 'package_id' => $package->id, 'subscription_type' => 'annual',
            'auctions_limit' => 20, 'remaining_auctions' => 20, 'expires_at' => now()->addYear(),
            'price' => 2999, 'status' => 'approved', 'created_at' => now(),
        ]);
        // Someone else's subscription must not appear in this user's history.
        UserSubscription::create([
            'user_id' => $this->createUser()->id, 'package_id' => $package->id, 'subscription_type' => 'monthly',
            'auctions_limit' => 20, 'remaining_auctions' => 20, 'expires_at' => now()->addMonth(),
            'price' => 300, 'status' => 'approved',
        ]);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/subscription-history');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertEquals([$newer->id, $older->id], $ids->toArray());
    }

    public function test_get_history_requires_authentication()
    {
        $response = $this->withHeaders($this->headers())->getJson('/api/user/subscription-history');

        $response->assertStatus(401);
    }

    public function test_get_status_message_reflects_pending_status()
    {
        $user = $this->createUser();
        $package = $this->createPackage();
        UserSubscription::create([
            'user_id' => $user->id, 'package_id' => $package->id, 'subscription_type' => 'monthly',
            'auctions_limit' => 20, 'remaining_auctions' => 20, 'expires_at' => now()->addMonth(),
            'price' => 300, 'status' => 'pending',
        ]);

        Passport::actingAs($user, [], 'api');
        $response = $this->withHeaders($this->headers())->getJson('/api/user/subscription-status');

        $response->assertJsonPath('data.message', 'Your subscription is pending approval');
    }

    public function test_get_status_message_reflects_rejected_status()
    {
        $user = $this->createUser();
        $package = $this->createPackage();
        UserSubscription::create([
            'user_id' => $user->id, 'package_id' => $package->id, 'subscription_type' => 'monthly',
            'auctions_limit' => 20, 'remaining_auctions' => 20, 'expires_at' => now()->addMonth(),
            'price' => 300, 'status' => 'rejected', 'rejection_reason' => 'Invalid payment proof',
        ]);

        Passport::actingAs($user, [], 'api');
        $response = $this->withHeaders($this->headers())->getJson('/api/user/subscription-status');

        $response->assertJsonPath('data.message', 'Your subscription was rejected');
        $response->assertJsonPath('data.subscription.rejection_reason', 'Invalid payment proof');
    }

    public function test_get_status_message_reflects_active_status()
    {
        $user = $this->createUser();
        $package = $this->createPackage();
        UserSubscription::create([
            'user_id' => $user->id, 'package_id' => $package->id, 'subscription_type' => 'monthly',
            'auctions_limit' => 20, 'remaining_auctions' => 5, 'expires_at' => now()->addMonth(),
            'price' => 300, 'status' => 'approved',
        ]);

        Passport::actingAs($user, [], 'api');
        $response = $this->withHeaders($this->headers())->getJson('/api/user/subscription-status');

        $response->assertJsonPath('data.message', 'Your subscription is active');
    }
}
