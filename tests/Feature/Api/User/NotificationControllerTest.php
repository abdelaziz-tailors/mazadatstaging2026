<?php

namespace Tests\Feature\Api\User;

use App\Models\Notification;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
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
            'user_type' => 'buyer',
            'gender' => 'male',
        ], $overrides));
    }

    public function test_index_requires_authentication()
    {
        $response = $this->withHeaders($this->headers())->getJson('/api/user/notifications');

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_unread_count_requires_authentication()
    {
        $response = $this->withHeaders($this->headers())->getJson('/api/user/notifications/unread-count');

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_mark_as_read_requires_authentication()
    {
        $response = $this->withHeaders($this->headers())->postJson('/api/user/notifications/1/mark-read');

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_mark_all_as_read_requires_authentication()
    {
        $response = $this->withHeaders($this->headers())->postJson('/api/user/notifications/mark-all-read');

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_notification_belongs_to_user()
    {
        $user = $this->createUser();
        $notification = Notification::create(['user_id' => $user->id, 'title' => 'A', 'description' => 'd']);

        $this->assertTrue($notification->user->is($user));
    }

    public function test_index_returns_own_and_broadcast_notifications_only()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $own = Notification::create(['user_id' => $user->id, 'type' => 'identity_verified', 'title' => 'Mine', 'description' => 'd']);
        $broadcast = Notification::create(['user_id' => null, 'title' => 'Broadcast', 'description' => 'd']);
        Notification::create(['user_id' => $otherUser->id, 'title' => 'Not mine', 'description' => 'd']);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/notifications');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($own->id));
        $this->assertTrue($ids->contains($broadcast->id));
        $this->assertCount(2, $ids);
    }

    public function test_index_reports_unread_count()
    {
        $user = $this->createUser();
        Notification::create(['user_id' => $user->id, 'title' => 'A', 'description' => 'd']);
        Notification::create(['user_id' => $user->id, 'title' => 'B', 'description' => 'd', 'read_at' => now()]);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/notifications');

        $response->assertStatus(200)->assertJsonPath('unread_count', 1);
    }

    public function test_unread_count_endpoint()
    {
        $user = $this->createUser();
        Notification::create(['user_id' => $user->id, 'title' => 'A', 'description' => 'd']);
        Notification::create(['user_id' => $user->id, 'title' => 'B', 'description' => 'd']);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/notifications/unread-count');

        $response->assertStatus(200)->assertJsonPath('data.unread_count', 2);
    }

    public function test_mark_as_read_marks_only_the_given_notification()
    {
        $user = $this->createUser();
        $target = Notification::create(['user_id' => $user->id, 'title' => 'A', 'description' => 'd']);
        $other = Notification::create(['user_id' => $user->id, 'title' => 'B', 'description' => 'd']);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson("/api/user/notifications/{$target->id}/mark-read");

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNotNull($target->fresh()->read_at);
        $this->assertNull($other->fresh()->read_at);
    }

    public function test_mark_as_read_fails_for_another_users_notification()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $notification = Notification::create(['user_id' => $otherUser->id, 'title' => 'A', 'description' => 'd']);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson("/api/user/notifications/{$notification->id}/mark-read");

        $response->assertStatus(200)->assertJson(['success' => false]);
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_marks_every_unread_notification()
    {
        $user = $this->createUser();
        $first = Notification::create(['user_id' => $user->id, 'title' => 'A', 'description' => 'd']);
        $second = Notification::create(['user_id' => $user->id, 'title' => 'B', 'description' => 'd']);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/user/notifications/mark-all-read');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNotNull($first->fresh()->read_at);
        $this->assertNotNull($second->fresh()->read_at);
    }
}
