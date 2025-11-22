<?php

declare(strict_types=1);

return [

    'guards' => [
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
        'sponsor_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\SponsorUser::class,
        ],
    ],


];
