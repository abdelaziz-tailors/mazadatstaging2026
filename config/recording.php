<?php

return [
    'enabled' => env('AGORA_RECORDING_ENABLED', false),
    'driver' => env('AGORA_RECORDING_DRIVER', 'mock'),
    'live_enabled' => env('AGORA_RECORDING_LIVE_ENABLED', false),
    'customer_id' => env('AGORA_CUSTOMER_ID'),
    'customer_secret' => env('AGORA_CUSTOMER_SECRET'),
    'uid' => env('AGORA_RECORDING_UID', '4294967294'),
    'storage_region' => env('AGORA_RECORDING_STORAGE_REGION'),
    'queue' => env('AGORA_RECORDING_QUEUE', 'recordings'),
];
