<?php

declare(strict_types=1);

return [

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => true,
            'serve' => true,
            'report' => false,
        ],
    ],

];
