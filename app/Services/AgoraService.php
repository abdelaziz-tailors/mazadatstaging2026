<?php

namespace App\Services;

use App\Libraries\Agora\RtcTokenBuilder2;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\AuctionRecording;

class AgoraService
{
    private $appId;
    private $appCertificate;

    public function __construct()
    {
        $this->appId = config('services.agora.app_id');
        $this->appCertificate = config('services.agora.app_certificate');
    }

    public function generateToken($channelName, $uid = 0, $role = 'subscriber')
    {
        $tokenExpireSeconds = 86400; // 24 hours in seconds

        $rtcRole = RtcTokenBuilder2::ROLE_SUBSCRIBER;

        if ($role === 'publisher') {
            $rtcRole = RtcTokenBuilder2::ROLE_PUBLISHER;
        }

        // Use official Agora token builder
        try {
            $token = RtcTokenBuilder2::buildTokenWithUid(
                $this->appId,
                $this->appCertificate,
                $channelName,
                $uid,
                $rtcRole,
                $tokenExpireSeconds,
                $tokenExpireSeconds
            );

            return $token;
        } catch (\Exception $e) {
            Log::error('Exception while generating Agora token', [
                'error' => $e->getMessage(),
                'channel_name' => $channelName,
                'uid' => $uid,
                'role' => $role,
            ]);

            return null;
        }
    }

    /**
     * Generate a unique channel name for live video.
     */
    public function generateChannelName($liveVideoId)
    {
        return 'auction_' . $liveVideoId . '_' . time();
    }

    /**
     * Get Agora App ID.
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /** Guard every network operation, including query/readiness, independently. */
    private function recordingHttp()
    {
        if (config('recording.driver') !== 'agora' || !config('recording.live_enabled')) {
            throw new \RuntimeException('Live recording is disabled');
        }
        foreach (['services.agora.app_id', 'services.agora.app_certificate',
            'recording.customer_id', 'recording.customer_secret',
            'filesystems.disks.s3.key', 'filesystems.disks.s3.secret',
            'filesystems.disks.s3.bucket', 'filesystems.disks.s3.region'] as $key) {
            if (!config($key)) {
                throw new \RuntimeException('Recording configuration incomplete');
            }
        }
        if (config('recording.storage_region') === null) {
            throw new \RuntimeException('Agora storage region is required');
        }

        return Http::withBasicAuth(config('recording.customer_id'), config('recording.customer_secret'))
            ->acceptJson()->connectTimeout(3)->timeout(10);
    }

    public function recordingReady(string $channel): bool
    {
        $response = $this->recordingHttp()->get('https://api.agora.io/dev/v1/channel/user/'
            . rawurlencode($this->appId) . '/' . rawurlencode($channel));
        if (!$response->successful()) {
            throw new \RuntimeException('Agora readiness request failed');
        }
        return $response->json('success') === true
            && $response->json('data.channel_exist') === true
            && count($response->json('data.broadcasters', [])) > 0;
    }

    public function recordingRequest(AuctionRecording $recording, string $action): array
    {
        if ($recording->driver !== 'agora' || !in_array($action, ['acquire', 'start', 'query', 'stop'], true)) {
            throw new \RuntimeException('Invalid recording operation');
        }
        $http = $this->recordingHttp();
        if ($recording->app_id !== $this->appId || $recording->bucket !== config('filesystems.disks.s3.bucket')) {
            throw new \RuntimeException('Recording project or storage changed');
        }
        $url = 'https://api.agora.io/v1/apps/' . rawurlencode($this->appId) . '/cloud_recording/';
        $url .= $action === 'acquire' ? 'acquire' : 'resourceid/' . rawurlencode($recording->resource_id)
            . ($action === 'start' ? '' : '/sid/' . rawurlencode($recording->sid)) . '/mode/mix/' . $action;
        $request = [];
        if ($action === 'start') {
            $token = $this->generateToken($recording->channel, (int) $recording->uid);
            if (!$token) {
                throw new \RuntimeException('Recording token unavailable');
            }
            $request = [
                'token' => $token,
                'recordingConfig' => ['channelType' => 1, 'streamTypes' => 2, 'maxIdleTime' => 30,
                    'transcodingConfig' => ['width' => 640, 'height' => 360, 'fps' => 15,
                        'bitrate' => 500, 'mixedVideoLayout' => 1]],
                'recordingFileConfig' => ['avFileType' => ['hls', 'mp4']],
                'storageConfig' => ['vendor' => 1, 'region' => (int) config('recording.storage_region'),
                    'bucket' => config('filesystems.disks.s3.bucket'),
                    'accessKey' => config('filesystems.disks.s3.key'),
                    'secretKey' => config('filesystems.disks.s3.secret'),
                    'fileNamePrefix' => explode('/', $recording->prefix)],
            ];
        } elseif ($action === 'acquire') {
            $request = ['resourceExpiredHour' => 72, 'scene' => 0];
        }
        $response = $action === 'query' ? $http->get($url) : $http->post($url, [
            'cname' => $recording->channel, 'uid' => $recording->uid,
            'clientRequest' => $request ?: new \stdClass(),
        ]);
        // Never log responses or exception bodies: they can contain credentials or tokens.
        if ($response->status() === 404 && in_array($action, ['query', 'stop'], true)) {
            return ['workerAbsent' => true];
        }
        if (!$response->successful() || $response->status() === 206) {
            throw new \RuntimeException('Agora recording request failed');
        }
        $data = $response->json();
        if (!is_array($data)) {
            throw new \RuntimeException('Invalid Agora recording response');
        }
        return $data;
    }
}
