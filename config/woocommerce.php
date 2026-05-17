<?php
return [
    'url' => env('WC_STORE_URL'),
    'consumer_key' => env('WC_CONSUMER_KEY'),
    'consumer_secret' => env('WC_CONSUMER_SECRET'),
    'options' => [
        'wp_api' => true,
        'version' => env('WC_API_VERSION', 'wc/v3'),
        'timeout' => env('WC_TIMEOUT', 15),
        'verify_ssl' => env('WC_VERIFY_SSL', true),
        'follow_redirects' => env('WC_FOLLOW_REDIRECTS', false),
    ],
];
