<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Redis Cache Configuration
    |--------------------------------------------------------------------------
    */

    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Cache Configuration (Fallback)
    |--------------------------------------------------------------------------
    */

    'database' => [
        'driver' => 'database',
        'table' => 'cache',
        'connection' => null,
        'prefix' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Optimizations
    |--------------------------------------------------------------------------
    */

    'ttl' => [
        'products' => 900, // 15 minutes
        'categories' => 3600, // 1 hour  
        'featured' => 1800, // 30 minutes
        'search' => 300, // 5 minutes
    ],

];