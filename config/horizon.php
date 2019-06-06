<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection where Horizon will store the
    | meta information required for it to function. It includes the list
    | of supervisors, failed jobs, job metrics, and other information.
    |
    */

    'use' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing all Horizon data in Redis. You
    | may modify the prefix when you are running multiple installations
    | of Horizon on the same server so that they don't have problems.
    |
    */

    'prefix' => env('HORIZON_PREFIX', env('APP_NAME', 'apiary').':'.env('APP_ENV', 'prod').':'),

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    |
    | This option allows you to configure when the LongWaitDetected event
    | will be fired. Every connection / queue combination may have its
    | own, unique threshold (in seconds) before this event is fired.
    |
    */

    'waits' => [
        'redis:default' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long (in minutes) you desire Horizon to
    | persist the recent and failed jobs. Typically, recent jobs are kept
    | for one hour while all failed jobs are stored for an entire week.
    |
    */

    'trim' => [
        'recent' => 1440,   // 24 hours
        'failed' => 10080,  // 1 week
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue worker settings used by your application
    | in all environments. These supervisors and settings handle all your
    | queued jobs and will be provisioned by Horizon during deployment.
    |
    */

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default', 'jedi'],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 3,
                'block_for' => null,
            ],
        ],

        'test' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default', 'jedi'],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 1,
                'block_for' => null,
            ],
        ],

        'jedi-test' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default', 'jedi'],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 1,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default', 'jedi'],
                'balance' => 'simple',
                'processes' => 1,
                'tries' => 1,
                'block_for' => null,
            ],
        ],
    ],

    'middleware' => [
        'web',
        'auth.cas.force',
    ],
];
