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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'places_api_key' => env('GOOGLE_PLACES_API_KEY', env('GOOGLE_MAPS_API_KEY')),
        'place_id' => env('GOOGLE_PLACE_ID', 'ChIJbWsIEIVEdEER4uHEhb2dbcQ'),
    ],

    'tripadvisor' => [
        'api_key' => env('TRIPADVISOR_API_KEY'),
        'location_id' => env('TRIPADVISOR_LOCATION_ID', '29026644'),
    ],

    'ziina' => [
        'webhook_secret' => env('ZIINA_WEBHOOK_SECRET', ''),
    ],

    'cron' => [
        'secret_token' => env('CRON_SECRET_TOKEN', 'dunes_cron_2026'),
    ],

    'meta' => [
        'graph_version' => env('META_GRAPH_VERSION', 'v21.0'),
    ],

    'admin' => [
        'recovery_password' => env('ADMIN_PASSWORD'),
    ],

];
