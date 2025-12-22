<?php

declare(strict_types=1);

return [
    'guards' => [
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],
];
