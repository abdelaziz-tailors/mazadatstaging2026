<?php

namespace App\Services;

use App\Libraries\Agora\RtcTokenBuilder2;
use Illuminate\Support\Facades\Log;

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
}
