<?php

namespace App\Services\Agora;

use Illuminate\Support\Facades\Log;
use App\Services\Agora\RtcTokenBuilder2;


class AgoraService
{
    public static function generateRtcToken($channelName, $userId)
    {
        $appID               = env('AGORA_APP_ID');
        $appCertificate      = env('AGORA_APP_CERTIFICATE');
        
        if (empty($appID) || empty($appCertificate)) {
            Log::error("AGORA_APP_ID or AGORA_APP_CERTIFICATE is not set in the environment variables.");
            throw new \Exception("Please set AGORA_APP_ID and AGORA_APP_CERTIFICATE in the .env file.");
        }

        $role                = RtcTokenBuilder2::ROLE_PUBLISHER;
        $expireTimeInSeconds = 3600 * 24;
        $currentTimestamp    = now()->timestamp;
        $privilegeExpiredTs  = $currentTimestamp + $expireTimeInSeconds;

        $token = RtcTokenBuilder2::buildTokenWithUid(
            $appID,
            $appCertificate,
            $channelName,
            $userId,
            $role,
            $privilegeExpiredTs,
        );

        return $token;
    }
}
