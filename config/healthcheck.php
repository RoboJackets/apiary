<?php

declare(strict_types=1);

return [
    /**
     * Base path for the health check endpoints, by default will use /.
     */
    'base-path' => '',

    /**
     * List of health checks to run when determining the health
     * of the service.
     */
    'checks' => [
        \UKFast\HealthCheck\Checks\CacheHealthCheck::class,
        \UKFast\HealthCheck\Checks\DatabaseHealthCheck::class,
        \UKFast\HealthCheck\Checks\HttpHealthCheck::class,
        \UKFast\HealthCheck\Checks\LogHealthCheck::class,
        \UKFast\HealthCheck\Checks\MigrationUpToDateHealthCheck::class,
        \UKFast\HealthCheck\Checks\PackageSecurityHealthCheck::class,
        \UKFast\HealthCheck\Checks\StorageHealthCheck::class,
        ...(env('APP_ENV') === 'sandbox' ? [] : [
            \App\HorizonHealthCheck::class,
            \UKFast\HealthCheck\Checks\RedisHealthCheck::class,
            \UKFast\HealthCheck\Checks\SchedulerHealthCheck::class,
        ]),
    ],

    /**
     * A list of middleware to run on the health-check route
     * It's recommended that you have a middleware that only
     * allows admin consumers to see the endpoint.
     *
     * See UKFast\HealthCheck\BasicAuth for a one-size-fits all
     * solution
     */
    'middleware' => [],

    /**
     * Used by the basic auth middleware.
     */
    'auth' => [
        'user' => env('HEALTH_CHECK_USER'),
        'password' => env('HEALTH_CHECK_PASSWORD'),
    ],

    /**
     * Can define a list of connection names to test. Names can be
     * found in your config/database.php file. By default, we just
     * check the 'default' connection.
     */
    'database' => [
        'connections' => ['default'],
    ],

    /**
     * Can give an array of required environment values, for example
     * 'REDIS_HOST'. If any don't exist, then it'll be surfaced in the
     * context of the healthcheck.
     */
    'required-env' => [],

    /**
     * List of addresses and expected response codes to
     * monitor when running the HTTP health check.
     *
     * E.g. address => response code
     */
    'addresses' => [
        env('APP_URL').'/privacy' => 200,
        env('APP_URL').'/attendance/kiosk' => 200,
        env('MEILISEARCH_HOST').'/health' => 200,
    ],

    /**
     * Default response code for HTTP health check. Will be used
     * when one isn't provided in the addresses config.
     */
    'default-response-code' => 200,

    /**
     * Default timeout for cURL requests for HTTP health check.
     */
    'default-curl-timeout' => 1.0,

    /**
     * An array of other services that use the health check package
     * to hit. The URI should reference the endpoint specifically,
     * for example: https://api.example.com/health.
     */
    'x-service-checks' => [
    ],

    /**
     * A list of stores to be checked by the Cache health check.
     */
    'cache' => [
        'stores' => [
            env('CACHE_STORE'),
        ],
    ],

    /**
     * A list of disks to be checked by the Storage health check.
     */
    'storage' => [
        'disks' => [
            'local',
        ],
    ],

    'scheduler' => [
        'cache-key' => 'laravel-scheduler-health-check',
        'minutes-between-checks' => 2,
    ],
];
