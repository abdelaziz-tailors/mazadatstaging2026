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
    'base_url' => env('DEEP_LINK_BASE_URL') ?: env('APP_URL'),

    // Optional templates, confirmed against the mobile router: {id}, {auction_id}.
    'auction_app_url' => env('DEEP_LINK_AUCTION_APP_URL'),
    'lot_app_url' => env('DEEP_LINK_LOT_APP_URL'),

    'ios_store_url' => env('DEEP_LINK_IOS_STORE_URL'),

    'android_store_url' => env('DEEP_LINK_ANDROID_STORE_URL'),

    // Optional web fallback profile URL, e.g. https://example.com/profile/{id}
    'fallback_profile_url' => env('DEEP_LINK_FALLBACK_PROFILE_URL'),

    // Preserved former public files: Laravel now serves these alongside configured apps.
    'legacy_android' => json_decode(file_get_contents(__DIR__ . '/deep_link_associations/assetlinks.json'), true),
    'legacy_ios' => json_decode(file_get_contents(__DIR__ . '/deep_link_associations/apple-app-site-association.json'), true)['applinks']['details'],

    'android' => [
        // Additional verified apps, each with package_name and sha256_cert_fingerprints.
        'apps' => json_decode((string) env('DEEP_LINK_ANDROID_APPS', '[]'), true) ?: [],
        'package_name' => env('DEEP_LINK_ANDROID_PACKAGE_NAME'),
        'sha256_cert_fingerprints' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('DEEP_LINK_ANDROID_SHA256_CERT_FINGERPRINTS', ''))
        ))),
    ],

    'ios' => [
        // Additional verified apps, each with appID and its own paths.
        'apps' => json_decode((string) env('DEEP_LINK_IOS_APPS', '[]'), true) ?: [],
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
            explode(',', (string) env('DEEP_LINK_PATHS', '/u/*,/share/auction/*,/share/lot/*'))
        ))),
    ],
];
