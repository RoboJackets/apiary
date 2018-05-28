<?php

return [
    'square' => [
        /*
        |--------------------------------------------------------------------------
        | Square Connect PRODUCTION Access Token
        |--------------------------------------------------------------------------
        |
        | Can be found at https://connect.squareup.com/apps/{id}
        | Create a new application at https://connect.squareup.com/apps if needed
        |
        | Listed as "Personal Access Token", or use the "Sandbox Access Token" to test
        */

        'token' => env('SQUARE_TOKEN'),

        /*
        |--------------------------------------------------------------------------
        | Square Connect PRODUCTION Location ID
        |--------------------------------------------------------------------------
        |
        | Can be found at https://connect.squareup.com/apps/{id}/locations
        | Create a new application at https://connect.squareup.com/apps if needed
        |
        | Listed as "Locations" --> "Location ID"
        |
        | If you're using the Sandbox Access Token, use "Sandbox Locations" --> "Location ID" here
        */

        'location_id' => env('SQUARE_LOCATION_ID'),
    ],
];
