<?php

namespace App\Services;

class AgoraService
{
    private $appId;
    private $appCertificate;

    public function __construct()
    {
        $this->appId = env('AGORA_APP_ID');
        $this->appCertificate = env('AGORA_APP_CERTIFICATE');
    }

    /**
     * Generate Agora RTC Token
     * 
     * @param string $channelName - Unique channel name for the live stream
     * @param int $uid - User ID (0 for any user)
     * @param string $role - 'publisher' or 'subscriber'
     * @param int $privilegeExpiredTs - Token expiration time (default 24 hours)
     * @return string|null
     */
    public function generateToken($channelName, $uid = 0, $role = 'publisher', $privilegeExpiredTs = 0)
    {
        if (!$this->appId || !$this->appCertificate) {
            return null;
        }

        // Set token expiration time (default 24 hours from now)
        if ($privilegeExpiredTs == 0) {
            $privilegeExpiredTs = time() + 86400; // 24 hours
        }

        // Role: 1 for publisher (broadcaster), 2 for subscriber (audience)
        $roleNum = ($role === 'publisher') ? 1 : 2;

        $token = $this->buildToken($channelName, $uid, $roleNum, $privilegeExpiredTs);
        
        return $token;
    }

    /**
     * Build Agora Token using the algorithm
     */
    private function buildToken($channelName, $uid, $role, $privilegeExpiredTs)
    {
        // Agora Token Building Algorithm
        $version = '006';
        $randomInt = rand(100000000, 999999999);
        $salt = time();
        
        $message = $this->appId . $channelName . $uid . $salt . $privilegeExpiredTs;
        $signature = hash_hmac('sha256', $message, $this->appCertificate);
        
        $content = [
            'version' => $version,
            'app_id' => $this->appId,
            'channel_name' => $channelName,
            'uid' => $uid,
            'salt' => $salt,
            'privilege_expired_ts' => $privilegeExpiredTs,
            'role' => $role,
            'signature' => $signature
        ];
        
        $token = $version . base64_encode(json_encode($content));
        
        return $token;
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

    /**
     * Generate tokens for both broadcaster and audience
     * Returns array with all necessary Agora credentials
     */
    public function generateLiveStreamCredentials($channelName, $userId)
    {
        return [
            'app_id' => $this->getAppId(),
            'channel_name' => $channelName,
            'token_publisher' => $this->generateToken($channelName, $userId, 'publisher'),
            'token_subscriber' => $this->generateToken($channelName, 0, 'subscriber'),
            'uid' => $userId,
            'expiration_time' => time() + 86400, // 24 hours
        ];
    }
}

