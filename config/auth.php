<?php

declare(strict_types=1);

return [

    'guards' => [
        'api' => [
            'driver' => 'passport',
            'provider' => 'user_or_client',
        ],
    ],

    'providers' => [
        'user_or_client' => [
            'driver' => 'user_or_client',
            'model' => \App\Models\User::class,
        ],
    ],

];
