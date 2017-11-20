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
        | Listed as "Personal Access Token"
        */

        'token' => env('SQUARE_TOKEN'),

        /*
        |--------------------------------------------------------------------------
        | Square Connect SANDBOX/TEST Access Token
        |--------------------------------------------------------------------------
        |
        | Can be found at https://connect.squareup.com/apps/{id}
        | Create a new application at https://connect.squareup.com/apps if needed
        | 
        | Listed as "Sandbox Access Token"
        */

        'token_test' => env('SQUARE_TOKEN_TEST'),

        /*
        |--------------------------------------------------------------------------
        | Square Connect PRODUCTION Location ID
        |--------------------------------------------------------------------------
        |
        | Can be found at https://connect.squareup.com/apps/{id}/locations
        | Create a new application at https://connect.squareup.com/apps if needed
        | 
        | Listed as "Locations" --> "Location ID"
        */

        'location_id' => env('SQUARE_LOCATION_ID'),

        /*
        |--------------------------------------------------------------------------
        | Square Connect SANDBOX/TEST Location ID
        |--------------------------------------------------------------------------
        |
        | Can be found at https://connect.squareup.com/apps/{id}/locations
        | Create a new application at https://connect.squareup.com/apps if needed
        | 
        | Listed as "Sandbox Locations" --> "Location ID"
        */

        'location_id_test' => env('SQUARE_LOCATION_ID_TEST')
    ]
];
