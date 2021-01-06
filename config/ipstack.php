<?php

declare(strict_types=1);

return [
    /*
     * The endpoint to hit
     */
    'base_url' => env('IPSTACK_BASE_URL', 'http://api.ipstack.com/'),

    /*
     * The token to send with the request
     */
    'api_key' => env('IPSTACK_API_KEY', null),
];
