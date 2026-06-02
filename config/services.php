<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'nafath' => [
        'APP_ID' => env('NAFATH_APP_ID'),
        'APP_KEY' => env('NAFATH_APP_KEY'),
        'SERVER_URL' => env('NAFATH_SERVER_URL', 'https://nafath.api.elm.sa/stg'),
        'CALLBACK_URL' => env('NAFATH_CALLBACK_URL'),
    ],

    'sms' => [
        'endpoint' => env('SMS_ENDPOINT', 'https://smsvas.vlserv.com/VLSMSPlatformResellerAPI/NewSendingAPI/api/SMSSender/SendSMS'),
        'username' => env('SMS_USERNAME'),
        'password' => env('SMS_PASSWORD'),
        'sender' => env('SMS_SENDER', 'Dacktra'),
        'lang' => env('SMS_LANG', 'e'),
        'country_code' => env('SMS_COUNTRY_CODE', '2'),
        'timeout' => env('SMS_TIMEOUT', 10),
    ],

    'infobip' => [
        'base_url' => env('INFOBIP_BASE_URL'),
        'api_key' => env('INFOBIP_API_KEY'),
        'whatsapp_from' => env('INFOBIP_WHATSAPP_FROM'),
        'whatsapp_template' => env('INFOBIP_WHATSAPP_TEMPLATE'),
        'whatsapp_lang' => env('INFOBIP_WHATSAPP_LANG', 'en'),
        'timeout' => env('INFOBIP_TIMEOUT', 10),
    ],

];
