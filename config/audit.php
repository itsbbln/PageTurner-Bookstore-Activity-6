<?php

return [
    'enabled' => env('AUDITING_ENABLED', true),

    'implementation' => App\Models\Audit::class,

    'user' => [
        'morph_prefix' => 'user',
        'guards' => [
            'web',
            'api',
        ],
        'resolver' => OwenIt\Auditing\Resolvers\UserResolver::class,
    ],

    'resolvers' => [
        'ip_address' => OwenIt\Auditing\Resolvers\IpAddressResolver::class,
        'user_agent' => OwenIt\Auditing\Resolvers\UserAgentResolver::class,
        'url' => OwenIt\Auditing\Resolvers\UrlResolver::class,
    ],

    'events' => [
        'created',
        'updated',
        'deleted',
        'restored',
    ],

    'strict' => false,

    // Global exclude list; we will expand this in Lab 6 models.
    'exclude' => [],

    'empty_values' => true,
    'allowed_empty_values' => [
        'retrieved',
    ],

    'allowed_array_values' => false,

    'timestamps' => false,

    'threshold' => 0,

    'driver' => 'database',

    'drivers' => [
        'database' => [
            'table' => 'audits',
            'connection' => null,
        ],
    ],

    'queue' => [
        'enable' => false,
        'connection' => env('AUDIT_QUEUE_CONNECTION', 'sync'),
        'queue' => env('AUDIT_QUEUE_NAME', 'default'),
        'delay' => 0,
    ],

    'console' => false,
];

