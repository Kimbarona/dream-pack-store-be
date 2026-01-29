<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache "store" that gets used while
    | using this library. This store is used when another is not explicitly
    | set when using a given cache tag.
    |
    | Supported: "apc", "array", "database", "file", "memcached", "redis"
    |
    */
    'default' => env('CACHE_DRIVER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the same
    | cache driver to group types of items that get stored in different caches.
    |
    */
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
            'serialize' => false,
            'prefix' => env('REDIS_PREFIX', 'dream_pack_cache'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing the APC, database, memcached, or Redis cache drivers there is
    | risk of cache collisions where other applications are using the same
    | cache as your application. To prevent this we provide a prefix that is
    | automatically prepended to every cache key.
    |
    */
    'prefix' => env('CACHE_PREFIX', 'dream_pack_cache'),
];