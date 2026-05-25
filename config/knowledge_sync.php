<?php

return [
    'base_url' => env('KNOWLEDGE_SYNC_BASE_URL', env('APP_URL', 'http://localhost')),

    'crawl' => [
        'max_depth' => (int) env('KNOWLEDGE_SYNC_MAX_DEPTH', 2),
        'max_pages' => (int) env('KNOWLEDGE_SYNC_MAX_PAGES', 80),
        'timeout_seconds' => (int) env('KNOWLEDGE_SYNC_INTERNAL_TIMEOUT', 10),
    ],

    'template_scan' => [
        'paths' => [
            resource_path('views'),
            resource_path('js'),
            public_path('assets/components'),
        ],
        'extensions' => ['html', 'blade.php', 'php', 'jsx', 'tsx', 'vue', 'js', 'ts'],
        'max_file_bytes' => (int) env('KNOWLEDGE_SYNC_TEMPLATE_MAX_BYTES', 1_500_000),
    ],

    'url' => [
        'strip_query_parameters' => ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'fbclid', 'gclid'],
    ],

    'fetch' => [
        'timeout_seconds' => (int) env('KNOWLEDGE_SYNC_FETCH_TIMEOUT', 15),
        'connect_timeout_seconds' => (int) env('KNOWLEDGE_SYNC_FETCH_CONNECT_TIMEOUT', 8),
        'max_redirects' => (int) env('KNOWLEDGE_SYNC_FETCH_MAX_REDIRECTS', 3),
        'max_response_bytes' => (int) env('KNOWLEDGE_SYNC_FETCH_MAX_BYTES', 5_000_000),
        'max_text_bytes' => (int) env('KNOWLEDGE_SYNC_MAX_TEXT_BYTES', 200_000),
        'allowed_content_types' => [
            'text/html',
            'text/plain',
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'blocked_hosts' => [
            'localhost',
            '127.0.0.1',
            '::1',
            '0.0.0.0',
            '169.254.169.254',
        ],
        'allow_private_ips_for_internal_base_host' => true,
    ],

    'botpress' => [
        'api_base_url' => env('BOTPRESS_API_BASE_URL', 'https://api.botpress.cloud'),
        'token' => env('BOTPRESS_PAT'),
        'bot_id' => env('BOTPRESS_BOT_ID'),
        'knowledge_base_id' => env('BOTPRESS_KNOWLEDGE_BASE_ID'),
        'file_key_prefix' => env('BOTPRESS_FILE_KEY_PREFIX', 'knowledge-sync'),
    ],

    'manual_urls' => array_values(array_filter(array_map('trim', explode(',', (string) env('KNOWLEDGE_SYNC_MANUAL_URLS', ''))))),
];
