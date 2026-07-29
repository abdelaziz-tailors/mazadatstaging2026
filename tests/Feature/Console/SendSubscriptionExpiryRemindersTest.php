<?php

namespace Tests\Feature\Console;

use App\Models\Notification;
use App\Models\User\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SendSubscriptionExpiryRemindersTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser(): User
    {
        return User::create([
            'name' => 'Vendor',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ]);
    }

    public function test_notifies_users_whose_subscription_expires_within_24_hours()
    {
        $user = $this->createUser();
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'status' => 'approved',
            'expires_at' => now()->addHours(12),
        ]);

        $this->artisan('notifications:subscription-expiry-reminders')->assertExitCode(0);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'payment_reminder',
        ]);
        $this->assertNotNull($subscription->fresh()->expiry_reminder_sent_at);
    }

    public function test_does_not_notify_twice_for_the_same_subscription()
    {
        $user = $this->createUser();
        UserSubscription::create([
            'user_id' => $user->id,
            'status' => 'approved',
            'expires_at' => now()->addHours(12),
        ]);

        $this->artisan('notifications:subscription-expiry-reminders');
        $this->artisan('notifications:subscription-expiry-reminders');

        $this->assertSame(
            1,
            Notification::where('user_id', $user->id)->where('type', 'payment_reminder')->count()
        );
    }

    public function test_ignores_subscriptions_expiring_more_than_24_hours_away()
    {
        $user = $this->createUser();
        UserSubscription::create([
            'user_id' => $user->id,
            'status' => 'approved',
            'expires_at' => now()->addDays(3),
        ]);

        $this->artisan('notifications:subscription-expiry-reminders');

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'type' => 'payment_reminder',
        ]);
    }

    public function test_ignores_pending_subscriptions()
    {
        $user = $this->createUser();
        UserSubscription::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'expires_at' => now()->addHours(12),
        ]);

        $this->artisan('notifications:subscription-expiry-reminders');

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'type' => 'payment_reminder',
        ]);
    }
}
