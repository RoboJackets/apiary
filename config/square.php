<?php

declare(strict_types=1);

use Square\Environments;

return [
    'access_token' => env('SQUARE_ACCESS_TOKEN'),
    'location_id' => env('SQUARE_LOCATION_ID'),
    'base_url' => env('SQUARE_ENVIRONMENT') === 'production' ? Environments::Production : Environments::Sandbox,
];
