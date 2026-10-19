<?php

namespace App\Services;

use App\Models\AuctionRecording;
use App\Models\LiveVideo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuctionRecordingService
{
    public function reconcile(int $auctionId): void
    {
        if (!config('recording.enabled')) {
            return;
        }
        // Use a shared atomic cache in multi-worker deployments; TTL exceeds job timeout.
        $lock = Cache::lock('auction-recording:' . $auctionId, 180);
        if (!$lock->get()) {
            return;
        }
        try {
            $video = LiveVideo::find($auctionId);
            $recording = AuctionRecording::where('live_video_id', $auctionId)->first();
            if (!$recording) {
                if (!$video || $video->status !== 'start' || $video->type !== 'live' || !$video->agora_channel_name) {
                    return;
                }
                $driver = config('recording.driver');
                if (!in_array($driver, ['mock', 'agora'], true)) {
                    throw new \RuntimeException('Unknown recording driver');
                }
                // Readiness checks happen before acquire; mock never reaches Agora.
                if ($driver === 'agora' && !app(AgoraService::class)->recordingReady($video->agora_channel_name)) {
                    return;
                }
                $recording = AuctionRecording::firstOrCreate(['live_video_id' => $auctionId], [
                    'driver' => $driver, 'disk' => $driver === 'mock' ? 'local' : 's3',
                    'app_id' => $driver === 'agora' ? config('services.agora.app_id') : null,
                    'bucket' => $driver === 'agora' ? config('filesystems.disks.s3.bucket') : null,
                    'channel' => $video->agora_channel_name, 'uid' => (string) config('recording.uid'),
                    'prefix' => 'recordings/' . $driver . '/' . Str::uuid(), 'status' => 'pending',
                ]);
            }
            if ($recording->retry_at && $recording->retry_at->isFuture()) {
                return;
            }
            try {
                $this->advance($recording, $video);
                $recording->update(['failures' => 0, 'retry_at' => null,
                    'last_error' => $recording->status === 'start_unknown' ? 'operator_recovery_required' : null]);
            } catch (\Throwable $exception) {
                // Persist a safe error code, never upstream response text.
                $recording->update(['failures' => $recording->failures + 1,
                    'retry_at' => now()->addSeconds(min(3600, 60 * (2 ** min(6, $recording->failures)))),
                    'last_error' => 'recording_operation_failed']);
            }
        } finally {
            $lock->release();
        }
    }

    private function advance(AuctionRecording $recording, ?LiveVideo $video): void
    {
        if ((!$video || $video->status === 'end') && !$recording->completed_at) {
            $completed = $video && $video->end_at ? \Carbon\Carbon::parse($video->end_at) : now();
            $recording->update(['completed_at' => $completed, 'expires_at' => $completed->copy()->addHours(48)]);
        }
        // Expired media is denied immediately and swept even while stop recovery is failing.
        if ($recording->expires_at && !$recording->expires_at->isFuture()) {
            $disk = Storage::disk($recording->disk);
            $prefix = $this->prefix($recording);
            if (count($disk->allFiles($prefix)) && !$disk->deleteDirectory($prefix)) {
                throw new \RuntimeException('Recording deletion failed');
            }
            if (count($disk->allFiles($prefix))) {
                throw new \RuntimeException('Recording deletion verification failed');
            }
            if (in_array($recording->status, ['processing', 'ready', 'deleted'], true)) {
                $recording->update(['status' => 'deleted']);
                return;
            }
        }
        if (in_array($recording->status, ['starting', 'start_unknown'], true)) {
            // A lost start response cannot safely be retried without its sid.
            $recording->update(['status' => 'start_unknown', 'last_error' => 'operator_recovery_required']);
            return;
        }
        if ($recording->completed_at && in_array($recording->status, ['pending', 'acquired'], true)) {
            $recording->update(['status' => 'deleted']);
        }
        if (in_array($recording->status, ['recording', 'stopping'], true)) {
            $action = $recording->completed_at ? 'stop' : 'query';
            if ($action === 'stop') {
                $recording->update(['status' => 'stopping']);
            }
            $result = $this->request($recording, $action);
            if ($action === 'query' && !empty($result['workerAbsent'])) {
                $recording->update(['status' => 'processing']);
            }
            if ($action === 'stop') {
                // Upload completion and playable object presence are verified separately.
                if (empty($result['workerAbsent']) && data_get($result, 'serverResponse.uploadingStatus') !== 'uploaded') {
                    throw new \RuntimeException('Recording upload is incomplete');
                }
                $recording->update(['status' => 'processing']);
            }
        }
        if ($recording->expires_at && !$recording->expires_at->isFuture()
            && $recording->status === 'processing') {
            $recording->update(['status' => 'deleted']);
            return;
        }
        if ($recording->status === 'processing' && count($this->playbackFiles($recording))) {
            $recording->update(['status' => 'ready']);
        }
        if ($recording->completed_at || in_array($recording->status, ['ready', 'deleted', 'processing'], true)) {
            return;
        }
        if ($recording->status === 'pending') {
            $result = $this->request($recording, 'acquire');
            if (empty($result['resourceId'])) {
                throw new \RuntimeException('Missing recording resource');
            }
            $recording->update(['resource_id' => $result['resourceId'], 'acquired_at' => now(), 'status' => 'acquired']);
            return;
        }
        if ($recording->status === 'acquired') {
            if ($recording->acquired_at->lt(now()->subMinutes(4))) {
                $recording->update(['status' => 'pending', 'resource_id' => null]);
                return;
            }
            if ($recording->driver === 'agora' && !app(AgoraService::class)->recordingReady($recording->channel)) {
                return;
            }
            $recording->update(['status' => 'starting']);
            $result = $this->request($recording, 'start');
            if (empty($result['sid'])) {
                throw new \RuntimeException('Missing recording sid');
            }
            $recording->update(['sid' => $result['sid'], 'status' => 'recording']);
        }
    }

    private function request(AuctionRecording $recording, string $action): array
    {
        if ($recording->driver === 'mock') {
            // No HTTP, token generation, S3 reads/writes or fake playable media.
            return ['resourceId' => 'mock-resource-' . $recording->id,
                'sid' => 'mock-sid-' . $recording->id,
                'serverResponse' => ['uploadingStatus' => 'uploaded']];
        }
        if ($recording->driver !== config('recording.driver') || $recording->disk !== 's3') {
            throw new \RuntimeException('Recording driver mismatch');
        }
        return app(AgoraService::class)->recordingRequest($recording, $action);
    }

    public function prefix(AuctionRecording $recording): string
    {
        if (!in_array($recording->driver, ['mock', 'agora'], true)
            || $recording->disk !== ($recording->driver === 'mock' ? 'local' : 's3')
            || !preg_match('#^recordings/' . $recording->driver . '/[a-f0-9-]{36}$#D', $recording->prefix)) {
            throw new \RuntimeException('Unsafe recording storage prefix');
        }
        if ($recording->driver === 'agora' &&
            (config('recording.driver') !== 'agora' || !config('recording.live_enabled')
                || $recording->bucket !== config('filesystems.disks.s3.bucket'))) {
            throw new \RuntimeException('Live recording storage is disabled');
        }
        return $recording->prefix;
    }

    public function playbackFiles(AuctionRecording $recording): array
    {
        return array_values(array_filter(Storage::disk($recording->disk)->allFiles($this->prefix($recording)),
            function ($file) use ($recording) {
                return strpos($file, $recording->prefix . '/') === 0
                    && strpos($file, '..') === false && substr($file, -4) === '.mp4';
            }));
    }
}
