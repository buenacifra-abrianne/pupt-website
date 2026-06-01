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

    'idp' => [
        'enabled' => env('IDP_ENABLED', true),
        'base_url' => env('IDP_BASE_URL'),
        'client_id' => env('IDP_CLIENT_ID'),
        'client_secret' => env('IDP_CLIENT_SECRET'),
        'api_key' => env('IDP_API_KEY'),
        'logout_url' => env('IDP_LOGOUT_URL'),
    ],

    'oneportal' => [
        'url' => env('ONEPORTAL_URL'),
    ],

    'flss' => [
            'base_url' => env('FLSS_API_BASE_URL'),
            'api_key'  => env('FLSS_API_KEY'),
            'timeout'  => env('FLSS_API_TIMEOUT', 15),
        ],

    'ocms' => [
        'base_url' => env('OCMS_API_BASE_URL'),
        'api_key' => env('OCMS_API_KEY'),
        'timeout' => env('OCMS_API_TIMEOUT', 15),
    ],

    'botpress' => [
        'webhook_secret' => env('BOTPRESS_WEBHOOK_SECRET'),
        'webhook_url' => env('BOTPRESS_WEBHOOK_URL'),
    ],

];
