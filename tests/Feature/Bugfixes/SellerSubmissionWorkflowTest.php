<?php

namespace Tests\Feature\Bugfixes;

use App\Http\Controllers\Dashboard\SellerSubmissionController;
use App\Jobs\SendStoredNotification;
use App\Models\Notification;
use App\Models\SellerSubmissionMedia;
use App\Services\FCMService;
use Illuminate\Support\Facades\{Auth, Queue};
use Laravel\Passport\Passport;
use Tests\Support\AuctionBugfixTestCase;

class SellerSubmissionWorkflowTest extends AuctionBugfixTestCase
{
    /** @dataProvider loginStates */
    public function test_approval_notifies_only_original_seller_with_or_without_login(bool $loggedOut): void
    {
        $seller = $this->user(['fcm_token' => 'seller-device']);
        $owner = $this->user(['user_type' => 'vendor', 'fcm_token' => 'approver-device']);
        $admin = $this->admin($owner);
        $request = $this->submission($seller, $owner);
        Passport::actingAs($seller);
        if ($loggedOut) {
            $seller->token()->shouldReceive('revoke')->once();
            $this->postJson('/api/user/logout')->assertOk()->assertJsonPath('success', true);
            $this->app['auth']->forgetGuards();
            Auth::guard('admin')->setUser($admin);
        } else {
            Passport::actingAs($owner); // Subscriber session must never pick the recipient.
        }
        $controller = app(SellerSubmissionController::class);
        $controller->approve($request->id);
        $controller->approve($request->id);
        $this->assertSame([$seller->id], Notification::pluck('user_id')->all());
        Queue::assertPushed(SendStoredNotification::class, 1);
        $push = \Mockery::mock(FCMService::class);
        $push->shouldReceive('sendToDevice')->once()->with('seller-device', \Mockery::any(), \Mockery::any(), \Mockery::any())
            ->andReturn(['success' => true]);
        $this->app->instance(FCMService::class, $push);
        (new SendStoredNotification(Notification::first()->id))->handle();
        $this->assertNotNull(Notification::first()->push_completed_at);
    }

    public static function loginStates(): array { return [[false], [true]]; }

    public function test_shared_device_token_never_receives_a_private_seller_approval(): void
    {
        $seller = $this->user(); $owner = $this->user(['user_type' => 'vendor']);
        $admin = $this->admin($owner);
        $request = $this->submission($seller, $owner);
        foreach ([$seller, $owner] as $account) {
            Passport::actingAs($account);
            $this->postJson('/api/user/update-fcm', ['fcm_token' => 'reused-device-token'])
                ->assertOk()->assertJsonPath('success', true);
        }
        Auth::guard('admin')->setUser($admin);
        app(SellerSubmissionController::class)->approve($request->id);
        $this->assertSame([$seller->id], Notification::pluck('user_id')->all());
        $push = \Mockery::mock(FCMService::class);
        $push->shouldReceive('sendToDevice')->andReturn(['success' => true]);
        $this->app->instance(FCMService::class, $push);
        (new SendStoredNotification(Notification::first()->id))->handle();
        $push->shouldNotHaveReceived('sendToDevice');
        $this->assertNotNull(Notification::first()->push_completed_at);
    }

    public function test_owner_resubmits_and_preserves_notes_media_and_multiple_review_cycles(): void
    {
        $seller = $this->user(); $owner = $this->user(['user_type' => 'vendor']);
        $this->admin($owner);
        $request = $this->submission($seller, $owner);
        SellerSubmissionMedia::create(['seller_submission_id' => $request->id, 'type' => 'image', 'path' => 'original.jpg']);
        $controller = app(SellerSubmissionController::class);
        $controller->request_edit($request->id, \Illuminate\Http\Request::create('/', 'POST', ['review_note' => 'First review']));
        Passport::actingAs($seller);
        $this->postJson('/api/user/seller-submissions/'.$request->id, ['sheep_type' => 'Updated', 'notes' => 'Seller note',
            'user_id' => $owner->id, 'seller_id' => $owner->id, 'status' => 'approved', 'review_note' => 'forged'])
            ->assertOk()->assertJsonPath('data.status', 'pending')->assertJsonPath('data.review_note', 'First review');
        $this->assertSame($seller->id, $request->fresh()->user_id);
        $this->assertSame(1, $request->media()->count());
        $this->postJson('/api/user/seller-submissions/'.$request->id, [])->assertStatus(409);
        $controller->request_edit($request->id, \Illuminate\Http\Request::create('/', 'POST', ['review_note' => 'Second review']));
        $this->putJson('/api/user/seller-submissions/'.$request->id, ['expected_price' => 200])->assertOk();
        $fresh = $request->fresh();
        $this->assertSame('Updated', $fresh->sheep_type);
        $this->assertSame('Seller note', $fresh->notes);
        $this->assertEquals(200, $fresh->expected_price);
        $this->assertSame(['needs edit', 'pending', 'needs edit', 'pending'], array_column($fresh->review_history, 'status'));
        $this->assertSame(['First review', 'Second review'], array_values(array_filter(array_column($fresh->review_history, 'note'))));
        $this->assertSame(4, Notification::count());
    }

    public function test_other_seller_partner_and_guest_cannot_resubmit_or_change_destination(): void
    {
        $seller = $this->user(); $owner = $this->user(['user_type' => 'vendor']);
        $request = $this->submission($seller, $owner, 'needs edit');
        $this->postJson('/api/user/seller-submissions/'.$request->id, [])->assertUnauthorized();
        foreach ([$owner, $this->user()] as $outsider) {
            Passport::actingAs($outsider);
            $this->postJson('/api/user/seller-submissions/'.$request->id, ['description' => 'forged'])->assertForbidden();
        }
        Passport::actingAs($seller);
        $this->postJson('/api/user/seller-submissions/'.$request->id, ['expected_price' => -1])
            ->assertUnprocessable()->assertJsonValidationErrors('expected_price');
        $this->postJson('/api/user/seller-submissions/'.$request->id, ['partner_id' => $seller->id])
            ->assertUnprocessable()->assertJsonValidationErrors('partner_id');
        $this->assertSame('needs edit', $request->fresh()->status);
        $this->assertSame(0, Notification::count());
    }

    public function test_other_states_are_not_editable_and_legacy_notes_survive(): void
    {
        $seller = $this->user(); $owner = $this->user(['user_type' => 'vendor']);
        Passport::actingAs($seller);
        foreach (['pending', 'approved', 'rejected'] as $status) {
            $request = $this->submission($seller, $owner, $status);
            $this->putJson('/api/user/seller-submissions/'.$request->id, ['expected_price' => 500])->assertStatus(409);
            $this->assertEquals(100, $request->fresh()->expected_price);
        }
        $legacy = $this->submission($seller, $owner, 'needs edit');
        $legacy->update(['review_note' => 'Legacy instruction']);
        $this->putJson('/api/user/seller-submissions/'.$legacy->id, ['description' => 'Revised'])->assertOk();
        $this->assertSame('Legacy instruction', $legacy->fresh()->review_history[0]['note']);
        $this->admin($owner);
        app(SellerSubmissionController::class)->request_edit($legacy->id,
            \Illuminate\Http\Request::create('/', 'POST', ['review_note' => 'New instruction']));
        $this->assertSame('Legacy instruction', $legacy->fresh()->review_history[0]['note']);
        $this->assertSame('New instruction', $legacy->fresh()->review_history[2]['note']);
    }
}
