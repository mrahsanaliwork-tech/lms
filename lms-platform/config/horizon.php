<?php

use Illuminate\Support\Str;

return [

    'domain' => env('HORIZON_DOMAIN'),
    'path'   => env('HORIZON_PATH', 'horizon'),
    'use'    => 'default',

    'prefix' => env('HORIZON_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_horizon:'),

    'middleware' => ['web', 'auth', 'can:admin-horizon'],

    'waits' => [
        'redis:default' => 60,
    ],

    'trim' => [
        'recent'            => 60,
        'pending'           => 60,
        'completed'         => 60,
        'recent_failed'     => 10080,
        'failed'            => 10080,
        'monitored'         => 10080,
    ],

    'silenced' => [],

    'metrics' => [
        'trim_snapshots' => [
            'job'   => 24,
            'queue' => 24,
        ],
    ],

    'fast_termination' => false,

    'memory_limit' => 64,

    'defaults' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue'      => ['default'],
            'balance'    => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 1,
            'maxTime'      => 0,
            'maxJobs'      => 0,
            'memory'       => 128,
            'tries'        => 3,
            'timeout'      => 60,
            'nice'         => 0,
        ],
    ],

    'environments' => [
        'production' => [
            'supervisor-default' => [
                'connection'   => 'redis',
                'queue'        => ['default'],
                'balance'      => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'tries'        => 3,
            ],
            'supervisor-emails' => [
                'connection'   => 'redis',
                'queue'        => ['emails'],
                'balance'      => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 3,
                'tries'        => 3,
            ],
            'supervisor-ai' => [
                'connection'   => 'redis',
                'queue'        => ['ai'],
                'balance'      => 'simple',
                'minProcesses' => 1,
                'maxProcesses' => 2,
                'tries'        => 2,
                'timeout'      => 120,
            ],
            'supervisor-notifications' => [
                'connection'   => 'redis',
                'queue'        => ['notifications'],
                'balance'      => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 3,
                'tries'        => 3,
            ],
        ],

        'local' => [
            'supervisor-local' => [
                'connection'   => 'redis',
                'queue'        => ['default', 'emails', 'ai', 'notifications'],
                'balance'      => 'simple',
                'minProcesses' => 1,
                'maxProcesses' => 3,
                'tries'        => 1,
            ],
        ],
    ],
];
