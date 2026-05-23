<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mobile Deep Link Settings
    |--------------------------------------------------------------------------
    |
    | These values control how shareable profile links behave when opened in a
    | browser. The app scheme is used for opening the app directly, while the
    | store URLs are used as a fallback when the app is not installed.
    |
    */
    'app_scheme' => env('DEEP_LINK_APP_SCHEME', 'mazadat://'),

    // Base web domain used to generate share URLs, e.g. https://example.com
    'base_url' => env('DEEP_LINK_BASE_URL', env('APP_URL')),

    'ios_store_url' => env('DEEP_LINK_IOS_STORE_URL'),

    'android_store_url' => env('DEEP_LINK_ANDROID_STORE_URL'),

    // Optional web fallback profile URL, e.g. https://example.com/profile/{id}
    'fallback_profile_url' => env('DEEP_LINK_FALLBACK_PROFILE_URL'),

    'android' => [
        'package_name' => env('DEEP_LINK_ANDROID_PACKAGE_NAME'),
        'sha256_cert_fingerprints' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('DEEP_LINK_ANDROID_SHA256_CERT_FINGERPRINTS', ''))
        ))),
    ],

    'ios' => [
        'team_id' => env('DEEP_LINK_IOS_TEAM_ID'),
        'bundle_ids' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('DEEP_LINK_IOS_BUNDLE_IDS', ''))
        ))),
    ],

    // Comma separated deep-link paths allowed in app links, e.g. /u/*,/products/*
    'applinks' => [
        'paths' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('DEEP_LINK_PATHS', '/u/*'))
        ))),
    ],
];
