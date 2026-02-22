<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // 'allowed_origins' => ['http://localhost:3000'], /* Change in production */

    'allowed_origins' => ['https://dream-pack-store.vercel.app'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Accept', 
        'Authorization',
        'X-Requested-With',
        'Origin'
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
