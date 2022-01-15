<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong

return [
    'travel_authority_request' => [
        'powerform_url' => 'https://na3.docusign.net/Member/PowerFormSigning.aspx?PowerFormId=c36ffa0c-05d8-44fa-882f-c7ed0ad8f12c&env=na3&acct=7554587e-5afc-4247-8977-071ef5c80e3b&v=2',
        'traveler_name' => 'Traveler',
        'treasurer_name' => 'Primary Contact',
        'fields' => [
            'state_contract_airline' => 'Checkbox 3ec2b0f3-144c-42ac-87a1-d32a8a3af4b2',
            'non_contract_airline' => 'Checkbox 7c086f5c-4ca2-4143-95dd-d79891cc0271',
            'personal_automobile' => 'Checkbox 195f7e52-784e-4dac-95f2-e746c16dcb87',
            'rental_vehicle' => 'Checkbox 124e66ea-66d4-4029-94a6-8591e977db89',
            'other' => 'Checkbox e6cf8911-286a-444a-89bf-ef5ec63246f2',
            'itinerary' => 'Text 6187f653-0f21-4d8a-b078-253352aa6658',
            'purpose' => 'Text 981c407e-cf08-4eb9-bd8f-50404eeda087',
            'airfare_cost' => 'Text 5c589e88-c52e-4951-8c5d-9dd361c6d160',
            'other_cost' => 'Text 178e7427-ee2c-4dc4-95da-0e8bf816584c',
            'lodging_cost' => 'Text 5125ab17-bc84-47af-82d8-3e220e8d7e85',
            'registration_cost' => 'Text 0e4ce0f9-df5b-44ce-b7f3-4e78d9e124e8',
            'total_cost' => 'Text 3398924e-41bd-4bd9-bbca-27d8146629bd',
            'departure_date' => 'Text 44243914-c1ae-4779-af16-b8b31aa9c0fd',
            'return_date' => 'Text 5bce78de-35e6-43d9-86d9-b3e050f747ea',
        ],
    ],

    'treasurer_account' => env('TREASURER_DOCUSIGN_ACCOUNT'),

    'treasurer_name' => env('TREASURER_DOCUSIGN_NAME'),
];
