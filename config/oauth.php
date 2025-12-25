<?php

declare(strict_types=1);

return [
    'android' => [
        'client_id' => env('ANDROID_CLIENT_ID'),
    ],

    'ios' => [
        'client_id' => env('IOS_CLIENT_ID'),
    ],

    'routes' => [
        'jwks' => true,
    ],
];
