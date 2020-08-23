<?php

declare(strict_types=1);

return [
    'email' => env('CACHE_PREFIX') . '-email',
    'slack' => env('CACHE_PREFIX') . '-slack',
    'buzzapi' => env('CACHE_PREFIX') . '-buzzapi',
    'jedi' => env('CACHE_PREFIX') . '-jedi',
];
