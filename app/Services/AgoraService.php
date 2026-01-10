<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RtcTokenBuilder2;

// Include official Agora token builder
require_once app_path('Libraries/Agora/RtcTokenBuilder2.php');

class AgoraService
{
    private $appId;
    private $appCertificate;

    public function __construct()
    {
        $this->appId = env('AGORA_APP_ID');
        $this->appCertificate = env('AGORA_APP_CERTIFICATE');
    }

  
    public function generateToken($channelName, $uid = 0, $role = 'publisher', $tokenExpireSeconds = 0)
    {
        if (!$this->appId || !$this->appCertificate) {
            Log::error('Agora credentials missing', [
                'app_id_exists' => !empty($this->appId),
                'certificate_exists' => !empty($this->appCertificate)
            ]);
            return null;
        }

        // Set token expiration time (default 24 hours = 86400 seconds)
        if ($tokenExpireSeconds == 0) {
            $tokenExpireSeconds = 86400; // 24 hours in seconds
        }

      
        $rtcRole = RtcTokenBuilder2::ROLE_SUBSCRIBER;
            if ($role === 'publisher') {
                $rtcRole = RtcTokenBuilder2::ROLE_PUBLISHER;
            }
        // Use official Agora token builder
        try {
            $token =  RtcTokenBuilder2::buildTokenWithUid(
                $this->appId,
                $this->appCertificate,
                $channelName,
                (string)$uid, // Convert to string for compatibility
                $rtcRole,
                $tokenExpireSeconds,
                $tokenExpireSeconds // Privilege expiration same as token expiration
            );
            
            if (empty($token)) {
                Log::error('Failed to generate Agora token using official SDK', [
                    'channel_name' => $channelName,
                    'uid' => $uid,
                    'role' => $role
                ]);
                return null;
            }
            
            return $token;
        } catch (\Exception $e) {
            Log::error('Exception while generating Agora token', [
                'error' => $e->getMessage(),
                'channel_name' => $channelName,
                'uid' => $uid,
                'role' => $role
            ]);
            return null;
        }
    }


    /**
     * Generate a unique channel name for live video
     */
    public function generateChannelName($liveVideoId)
    {
        return 'auction_' . $liveVideoId . '_' . time();
    }

    /**
     * Get Agora App ID
     */
    public function getAppId()
    {
        return $this->appId;
    }


}

