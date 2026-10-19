<?php

namespace Tests\Feature\Services;

use App\Http\Controllers\api\Live\AuctionReplayController;
use App\Models\AuctionRecording;
use App\Models\LiveVideo;
use App\Services\AgoraService;
use App\Services\AuctionRecordingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AuctionRecordingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Never use the repository's configured database or run its migrations.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'recording.enabled' => true, 'recording.driver' => 'mock',
            'recording.live_enabled' => false, 'cache.default' => 'array']);
        DB::purge('sqlite');
        Schema::create('live_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(1);
            $table->string('status');
            $table->string('type');
            $table->string('agora_channel_name');
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });
        require_once database_path('migrations/2026_10_18_000000_create_auction_recordings_table.php');
        (new \CreateAuctionRecordingsTable())->up();
        Storage::fake('local');
        Http::fake(function () {
            throw new \RuntimeException('Unexpected HTTP request');
        });
    }

    private function video(): LiveVideo
    {
        return LiveVideo::create(['status' => 'start', 'type' => 'live', 'agora_channel_name' => 'auction-test']);
    }

    private function tick(LiveVideo $video): AuctionRecording
    {
        app(AuctionRecordingService::class)->reconcile($video->id);
        return AuctionRecording::where('live_video_id', $video->id)->firstOrFail();
    }

    private function authenticate(int $userId): void
    {
        \Illuminate\Support\Facades\Auth::shouldReceive('guard')->with('api')->andReturn(new class($userId) {
            private $userId;
            public function __construct($id) { $this->userId = $id; }
            public function id() { return $this->userId; }
        });
    }

    public function test_mock_lifecycle_is_idempotent_and_never_calls_agora(): void
    {
        $video = $this->video();
        $this->assertSame('acquired', $this->tick($video)->status);
        $this->assertSame('recording', $this->tick($video)->status);
        $this->tick($video);
        $this->assertSame(1, AuctionRecording::count());
        $video->update(['status' => 'end', 'end_at' => now()]);
        $recording = $this->tick($video);
        $this->assertSame('processing', $recording->status);
        $this->assertSame(48 * 3600, $recording->expires_at->diffInSeconds($recording->completed_at));
        Storage::disk('local')->put($recording->prefix . '/replay.mp4', 'fixture');
        $this->assertSame('ready', $this->tick($video)->status);
        Http::assertNothingSent();
    }

    public function test_expiry_removes_media_and_late_uploads(): void
    {
        $video = $this->video();
        $recording = $this->tick($video);
        $recording->update(['status' => 'ready', 'completed_at' => now()->subHours(49), 'expires_at' => now()->subHour()]);
        $file = $recording->prefix . '/replay.mp4';
        Storage::disk('local')->put($file, 'fixture');
        $this->assertSame('deleted', $this->tick($video)->status);
        Storage::disk('local')->assertMissing($file);
        Storage::disk('local')->put($file, 'late upload');
        $this->tick($video);
        Storage::disk('local')->assertMissing($file);
    }

    public function test_lost_start_response_is_never_automatically_restarted(): void
    {
        $video = $this->video();
        $recording = $this->tick($video);
        $recording->update(['status' => 'starting']);
        $this->assertSame('start_unknown', $this->tick($video)->status);
        $this->assertSame('start_unknown', $this->tick($video)->status);
        Http::assertNothingSent();
    }

    public function test_invalid_storage_prefix_is_rejected_and_backed_off(): void
    {
        $video = $this->video();
        $recording = $this->tick($video);
        $recording->update(['status' => 'ready', 'prefix' => '../unrelated',
            'completed_at' => now()->subHours(49), 'expires_at' => now()->subHour()]);
        $recording = $this->tick($video);
        $this->assertSame(1, $recording->failures);
        $this->assertTrue($recording->retry_at->isFuture());
        $this->assertSame(1, $this->tick($video)->failures);
    }

    public function test_live_gate_prevents_all_network_operations(): void
    {
        config(['recording.driver' => 'agora']);
        $this->expectException(\RuntimeException::class);
        try {
            app(AgoraService::class)->recordingReady('test');
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_photos_and_disabled_recording_do_not_create_sessions(): void
    {
        $video = $this->video();
        $video->update(['type' => 'photo']);
        app(AuctionRecordingService::class)->reconcile($video->id);
        $video->update(['type' => 'live']);
        config(['recording.enabled' => false]);
        app(AuctionRecordingService::class)->reconcile($video->id);
        $this->assertSame(0, AuctionRecording::count());
    }

    public function test_lock_prevents_concurrent_reconciliation(): void
    {
        $video = $this->video();
        $lock = \Illuminate\Support\Facades\Cache::lock('auction-recording:' . $video->id, 180);
        $lock->get();
        app(AuctionRecordingService::class)->reconcile($video->id);
        $this->assertSame(0, AuctionRecording::count());
        $lock->release();
        $this->assertSame('acquired', $this->tick($video)->status);
    }

    public function test_stop_failure_retries_after_backoff(): void
    {
        $video = $this->video();
        $recording = $this->tick($video);
        $recording->update(['driver' => 'agora', 'disk' => 's3', 'status' => 'recording', 'sid' => 'test']);
        config(['recording.driver' => 'agora']);
        $agora = Mockery::mock(AgoraService::class);
        $agora->shouldReceive('recordingRequest')->once()->withArgs(function ($row, $action) {
            return $action === 'stop';
        })->andThrow(new \RuntimeException('private upstream failure'));
        $this->app->instance(AgoraService::class, $agora);
        $video->update(['status' => 'end', 'end_at' => now()]);
        $this->assertSame('stopping', $this->tick($video)->status);
        $this->assertSame('recording_operation_failed', $recording->fresh()->last_error);
        $this->tick($video); // backoff suppresses a second request
        $recording->update(['retry_at' => now()->subSecond()]);
        $agora->shouldReceive('recordingRequest')->once()->andReturn(['serverResponse' => ['uploadingStatus' => 'uploaded']]);
        Storage::fake('s3');
        config(['recording.live_enabled' => true]);
        // Correct persisted prefix before the storage verification.
        $recording->update(['prefix' => 'recordings/agora/' . \Illuminate\Support\Str::uuid(),
            'bucket' => config('filesystems.disks.s3.bucket')]);
        $this->assertSame('processing', $this->tick($video)->status);
    }

    /** @dataProvider deniedReplay */
    public function test_replay_authorization_and_expiry(int $userId, bool $expired, int $expected): void
    {
        $video = $this->video();
        $recording = $this->tick($video);
        $video->update(['status' => 'end']);
        $recording->update(['status' => 'ready', 'expires_at' => $expired ? now()->subSecond() : now()->addHour()]);
        $this->authenticate($userId);
        try {
            app(AuctionReplayController::class)->show($video->id, Request::create('/'), app(AuctionRecordingService::class));
            $this->fail('Replay should be denied');
        } catch (HttpException $exception) {
            $this->assertSame($expected, $exception->getStatusCode());
        }
    }

    public static function deniedReplay(): array
    {
        return [[2, false, 403], [1, true, 410], [0, false, 403]];
    }

    public function test_signed_playback_is_capped_by_replay_expiry_and_can_be_redeemed(): void
    {
        $video = $this->video();
        $recording = $this->tick($video);
        $video->update(['status' => 'end']);
        $recording->update(['status' => 'ready', 'expires_at' => now()->addMinute()]);
        Storage::disk('local')->put($recording->prefix . '/replay.mp4', 'fixture');
        $this->authenticate(1);
        $controller = app(AuctionReplayController::class);
        $service = app(AuctionRecordingService::class);
        $response = $controller->show($video->id, Request::create('/'), $service);
        $data = $response->getData(true);
        $this->assertCount(1, $data['urls']);
        parse_str(parse_url($data['urls'][0], PHP_URL_QUERY), $query);
        $this->assertSame($recording->fresh()->expires_at->timestamp, (int) $query['expires']);
        $playback = $controller->show($video->id, Request::create($data['urls'][0]), $service);
        $this->assertSame('video/mp4', $playback->headers->get('Content-Type'));
    }

    public function test_unsigned_file_request_is_denied(): void
    {
        $video = $this->video();
        $recording = $this->tick($video);
        $video->update(['status' => 'end']);
        $recording->update(['status' => 'ready', 'expires_at' => now()->addHour()]);
        $this->authenticate(1);
        $this->expectException(HttpException::class);
        app(AuctionReplayController::class)->show($video->id,
            Request::create('/?file=arbitrary.mp4'), app(AuctionRecordingService::class));
    }

    public function test_rest_actions_use_expected_paths_and_private_storage_prefix(): void
    {
        config(['recording.driver' => 'agora', 'recording.live_enabled' => true,
            'recording.customer_id' => 'test-id', 'recording.customer_secret' => 'test-secret',
            'recording.storage_region' => 0, 'services.agora.app_id' => str_repeat('a', 32),
            'services.agora.app_certificate' => str_repeat('b', 32),
            'filesystems.disks.s3.key' => 'test-key', 'filesystems.disks.s3.secret' => 'test-secret',
            'filesystems.disks.s3.bucket' => 'test-bucket', 'filesystems.disks.s3.region' => 'us-east-1']);
        Http::fake(['api.agora.io/*' => Http::response(['resourceId' => 'resource', 'sid' => 'session',
            'serverResponse' => ['uploadingStatus' => 'uploaded']], 200)]);
        $recording = new AuctionRecording(['driver' => 'agora', 'disk' => 's3', 'channel' => 'test-channel',
            'uid' => '12345', 'resource_id' => 'resource', 'sid' => 'session',
            'prefix' => 'recordings/agora/' . \Illuminate\Support\Str::uuid(),
            'bucket' => 'test-bucket', 'app_id' => str_repeat('a', 32)]);
        $service = new AgoraService();
        foreach (['acquire', 'start', 'query', 'stop'] as $action) {
            $service->recordingRequest($recording, $action);
        }
        Http::assertSentCount(4);
        Http::assertSent(function ($request) use ($recording) {
            return substr($request->url(), -6) === '/start'
                && $request['clientRequest']['storageConfig']['fileNamePrefix'] === explode('/', $recording->prefix)
                && $request['uid'] === '12345';
        });
        Http::assertSent(function ($request) {
            return $request->method() === 'GET' && substr($request->url(), -6) === '/query';
        });
    }

    public function test_partial_upload_is_not_treated_as_completed(): void
    {
        config(['recording.driver' => 'agora', 'recording.live_enabled' => true,
            'recording.customer_id' => 'test', 'recording.customer_secret' => 'test',
            'recording.storage_region' => 0, 'services.agora.app_id' => 'test-app',
            'services.agora.app_certificate' => 'test', 'filesystems.disks.s3.key' => 'test',
            'filesystems.disks.s3.secret' => 'test', 'filesystems.disks.s3.bucket' => 'test',
            'filesystems.disks.s3.region' => 'test']);
        Http::fake(['api.agora.io/*' => Http::response(['serverResponse' => ['uploadingStatus' => 'backuped']], 206)]);
        $this->expectException(\RuntimeException::class);
        (new AgoraService())->recordingRequest(new AuctionRecording(['driver' => 'agora',
            'resource_id' => 'r', 'sid' => 's', 'channel' => 'c', 'uid' => '123',
            'app_id' => 'test-app', 'bucket' => 'test']), 'stop');
    }
}
