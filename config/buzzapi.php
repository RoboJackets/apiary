<?php

declare(strict_types=1);

return [

    /*
     * BuzzAPI Hostname
     * Override this if you want to use the test instance (test.api.gatech.edu)
     */
    'host' => env('BUZZAPI_HOST', 'api.gatech.edu'),

    /*
     * BuzzAPI Application ID
     * *MANDATORY* You must specify this in .env
     *
     * This is usually the username for a service account,
     * but could also be a personal GT account username in special cases
     */
    'app_id' => env('BUZZAPI_APP_ID', null),

    /*
     * BuzzAPI Application Password
     * *MANDATORY* You must specify this in .env
     *
     * This is the password for the Application ID above
     * Usually the password for a service account,
     * but could also be a personal GT account password in special cases
     */
    'app_password' => env('BUZZAPI_APP_PASSWORD', null),

    /*
     * BuzzAPI Default Log Level
     * This can be overridden in each request if desired
     * Options: info, warn, debug
     */
    'default_log_level' => env('BUZZAPI_DEFAULT_LOG_LEVEl', 'info'),

    /**
     * Float describing the timeout of the request in seconds. Use 0 to wait indefinitely.
     */
    'timeout' => env('BUZZAPI_TIMEOUT', 10),

    /**
     * Float describing the number of seconds to wait while trying to connect to a server. Use 0 to wait indefinitely.
     */
    'connect_timeout' => env('BUZZAPI_CONNECT_TIMEOUT', 5),
];
