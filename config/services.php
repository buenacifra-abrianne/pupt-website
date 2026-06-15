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

    'cloudwatch' => [
        'region' => env('AWS_CLOUDWATCH_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
        'version' => env('AWS_CLOUDWATCH_VERSION', 'latest'),
        'instance_id' => env('AWS_CLOUDWATCH_INSTANCE_ID', env('EC2_INSTANCE_ID')),
        'cpu_namespace' => env('AWS_CLOUDWATCH_CPU_NAMESPACE', env('AWS_CLOUDWATCH_EC2_NAMESPACE', 'AWS/EC2')),
        'cpu_metric' => env('AWS_CLOUDWATCH_CPU_METRIC', 'CPUUtilization'),
        'cpu_dimensions' => env('AWS_CLOUDWATCH_CPU_DIMENSIONS', ''),
        'cwagent_namespace' => env('AWS_CLOUDWATCH_CWAGENT_NAMESPACE', 'CWAgent'),
        'memory_metric' => env('AWS_CLOUDWATCH_MEMORY_METRIC', 'mem_used_percent'),
        'memory_dimensions' => env('AWS_CLOUDWATCH_MEMORY_DIMENSIONS', ''),
        'period' => (int) env('AWS_CLOUDWATCH_PERIOD', 300),
        'lookback_minutes' => (int) env('AWS_CLOUDWATCH_LOOKBACK_MINUTES', 10),
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
        'logout_url_get' => env('IDP_LOGOUT_URL_GET'),
        'logout_mode' => env('IDP_LOGOUT_MODE', 'post'),
        'logout_redirect_url' => env('IDP_LOGOUT_REDIRECT_URL'),
        'logout_redirect_parameter' => env('IDP_LOGOUT_REDIRECT_PARAMETER'),
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
