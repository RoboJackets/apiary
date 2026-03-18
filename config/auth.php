<?php

declare(strict_types=1);

return [
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
        'sponsor' => [
            'driver' => 'session',
            'provider' => 'sponsor_users',
        ],

    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'sponsor_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\SponsorUser::class,
        ],
    ],
];
