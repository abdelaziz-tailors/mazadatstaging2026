<?php

namespace Tests\Feature;

use App\Models\NafathVerificationRequest;
use App\Models\Notification;
use App\Models\User\User;
use App\Services\NafathService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NafathControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser(): User
    {
        return User::create([
            'name' => 'Test User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ]);
    }

    public function test_send_request_persists_a_verification_request_linked_to_the_user()
    {
        $user = $this->createUser();

        $this->mock(NafathService::class, function ($mock) {
            $mock->shouldReceive('sendMfaRequest')->once()->andReturn([
                'success' => true,
                'status_code' => 200,
                'request_id' => 'req-123',
                'data' => ['transId' => 'trans-1', 'random' => 'rand-1'],
            ]);
        });

        $response = $this->postJson('/api/nafath/request', [
            'nationalId' => '1010101010',
            'service' => 'login',
            'user_id' => $user->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('nafath_verification_requests', [
            'request_id' => 'req-123',
            'user_id' => $user->id,
            'status' => 'WAITING',
        ]);
    }

    public function test_callback_marks_user_verified_and_notifies_them_once()
    {
        $user = $this->createUser();
        NafathVerificationRequest::create([
            'request_id' => 'req-abc',
            'user_id' => $user->id,
            'national_id' => '1010101010',
            'status' => 'WAITING',
        ]);

        $this->mock(NafathService::class, function ($mock) {
            $mock->shouldReceive('verifyCallbackToken')->andReturn([
                'success' => true,
                'payload' => (object) ['status' => 'COMPLETED'],
            ]);
        });

        $response = $this->postJson('/api/nafath/callback', [
            'token' => 'fake-jwt',
            'transId' => 'trans-1',
            'requestId' => 'req-abc',
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($user->fresh()->nafath_verified_at);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'identity_verified',
        ]);

        // Calling the callback again (e.g. a retried webhook) must not duplicate the notification.
        $verifiedAt = $user->fresh()->nafath_verified_at;
        $this->postJson('/api/nafath/callback', [
            'token' => 'fake-jwt',
            'transId' => 'trans-1',
            'requestId' => 'req-abc',
        ])->assertStatus(200);

        $this->assertEquals($verifiedAt, $user->fresh()->nafath_verified_at);
        $this->assertSame(
            1,
            Notification::where('user_id', $user->id)->where('type', 'identity_verified')->count()
        );
    }

    public function test_callback_ignores_rejected_status()
    {
        $user = $this->createUser();
        NafathVerificationRequest::create([
            'request_id' => 'req-rejected',
            'user_id' => $user->id,
            'national_id' => '1010101010',
            'status' => 'WAITING',
        ]);

        $this->mock(NafathService::class, function ($mock) {
            $mock->shouldReceive('verifyCallbackToken')->andReturn([
                'success' => true,
                'payload' => (object) ['status' => 'REJECTED'],
            ]);
        });

        $this->postJson('/api/nafath/callback', [
            'token' => 'fake-jwt',
            'transId' => 'trans-1',
            'requestId' => 'req-rejected',
        ])->assertStatus(200);

        $this->assertNull($user->fresh()->nafath_verified_at);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'type' => 'identity_verified',
        ]);
    }

    public function test_callback_for_unknown_request_id_does_not_error()
    {
        $this->mock(NafathService::class, function ($mock) {
            $mock->shouldReceive('verifyCallbackToken')->andReturn([
                'success' => true,
                'payload' => (object) ['status' => 'COMPLETED'],
            ]);
        });

        $this->postJson('/api/nafath/callback', [
            'token' => 'fake-jwt',
            'transId' => 'trans-1',
            'requestId' => 'req-does-not-exist',
        ])->assertStatus(200);
    }

    public function test_verification_request_belongs_to_user()
    {
        $user = $this->createUser();
        $verificationRequest = NafathVerificationRequest::create([
            'request_id' => 'req-xyz',
            'user_id' => $user->id,
            'national_id' => '1010101010',
            'status' => 'WAITING',
        ]);

        $this->assertTrue($verificationRequest->user->is($user));
    }
}
