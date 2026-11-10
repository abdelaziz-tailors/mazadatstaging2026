<?php

namespace Tests\Feature\Bugfixes;

use App\Jobs\SendStoredNotification;
use App\Models\Notification;
use App\Services\{FCMService, PushTokenOwnership};
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\Support\AuctionBugfixTestCase;

class PushTokenOwnershipTest extends AuctionBugfixTestCase
{
    public function test_registration_transfers_ownership_including_deleted_accounts_and_retains_one_receipt(): void
    {
        $seller = $this->user(['fcm_token' => 'device']); $old = $this->user(['fcm_token' => 'device']); $old->delete();
        $subscriber = $this->user(['user_type' => 'vendor']);
        Passport::actingAs($subscriber);
        $this->postJson('/api/user/update-fcm', ['fcm_token' => 'device'])->assertJsonPath('success', true);
        $this->assertNull($seller->fresh()->fcm_token);
        $this->assertNull($old->fresh()->fcm_token);
        $this->assertSame('device', $subscriber->fresh()->fcm_token);
        $this->assertSame($subscriber->id, (int) DB::table('push_token_owners')->where('token_hash', hash('sha256', 'device'))->value('user_id'));
        $request = $this->submission($seller, $subscriber); $request->update(['status' => 'approved']);
        $push = \Mockery::mock(FCMService::class)->shouldNotReceive('sendToDevice')->getMock();
        $this->app->instance(FCMService::class, $push);
        (new SendStoredNotification(Notification::sole()->id))->handle();
        (new SendStoredNotification(Notification::sole()->id))->handle();
        $this->getJson('/api/user/notifications')->assertJsonPath('pagination.total', 0);
        Passport::actingAs($seller);
        $this->getJson('/api/user/notifications')->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.type', 'seller_request_status_changed');
        $this->assertSame(1, Notification::count());
        $this->assertNotNull(Notification::sole()->push_completed_at);
    }

    public function test_historical_shared_token_is_suppressed_until_explicit_registration(): void
    {
        $seller = $this->user(['fcm_token' => 'shared']); $other = $this->user(['fcm_token' => 'shared']);
        $request = $this->submission($seller, $other); $request->update(['status' => 'approved']);
        $push = \Mockery::mock(FCMService::class)->shouldNotReceive('sendToDevice')->getMock();
        $this->app->instance(FCMService::class, $push);
        (new SendStoredNotification(Notification::sole()->id))->handle();
        $this->assertNotNull(Notification::sole()->push_completed_at);
        app(PushTokenOwnership::class)->register($seller->id, 'shared');
        $this->assertNull($other->fresh()->fcm_token);
    }

    public function test_token_rotation_and_invalid_values_preserve_existing_response_contract(): void
    {
        $user = $this->user(); Passport::actingAs($user);
        foreach ([null, [], ['token'], str_repeat('x', 4097)] as $invalid) {
            $this->postJson('/api/user/update-fcm', ['fcm_token' => $invalid])->assertOk()->assertJsonPath('success', false);
        }
        foreach (['old-device', 'new-device'] as $token) {
            $this->postJson('/api/user/update-fcm', ['fcm_token' => $token])->assertJsonPath('success', true);
        }
        $this->assertSame('new-device', $user->fresh()->fcm_token);
    }

    public function test_legacy_queued_push_is_generic_and_explicit_public_broadcast_keeps_its_content(): void
    {
        $generic = ['type' => 'inbox_updated', 'screen' => 'notification', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK'];
        $fcm = \Mockery::mock(FCMService::class);
        $fcm->shouldReceive('sendToMultipleDevices')->once()->with(['captured-device'], 'Mazadat',
            'Open the app to view your notifications.', $generic)->andReturn(['success' => true]);
        $legacy = new \App\Jobs\SendFCMNotification(['captured-device'], 'Private winner', 'Private animal and price', ['item_id' => '123']);
        // The newly added property is absent from jobs serialized by old code.
        $values = $legacy->__serialize();
        unset($values["\0*\0publicBroadcast"]);
        $restored = (new \ReflectionClass($legacy))->newInstanceWithoutConstructor();
        $restored->__unserialize($values);
        $restored->handle($fcm);
        $fcm->shouldReceive('sendToMultipleDevices')->once()->with(['public-device'], 'Public auction', 'Public schedule', [])
            ->andReturn(['success' => true]);
        (new \App\Jobs\SendFCMNotification(['public-device'], 'Public auction', 'Public schedule', [], true))->handle($fcm);
    }
}
