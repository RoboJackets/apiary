<?php

declare(strict_types=1);

return [
    'domestic_travel_authority_request' => [
        'powerform_url' => 'https://na3.docusign.net/Member/PowerFormSigning.aspx?PowerFormId=0b84b2d4-fed7-4a43-bfd2-b5bb934e13b6&env=na3&acct=7554587e-5afc-4247-8977-071ef5c80e3b&v=2',
        'traveler_name' => 'Traveler',
        'ingest_mailbox_name' => 'Ingest Mailbox',
        'fields' => [
            'state_contract_airline' => 'Checkbox d259085e-55cd-4e6e-a931-3d665524e8f1',
            'non_contract_airline' => 'Checkbox 92887317-7509-4759-b864-f1a909dc2998',
            'personal_automobile' => 'Checkbox 455c590b-07cb-4259-92a5-72c8f1be7be4',
            'rental_vehicle' => 'Checkbox 7cc47168-9e71-4f87-ae26-16378d53e14f',
            'other' => 'Checkbox b00fe61b-4fa1-4251-800d-196c855cca34',
            'itinerary' => 'Text c5f02d8b-07f8-4217-a998-789a12f249e3',
            'purpose' => 'Text 4d2ad347-7d25-442c-8f44-17c352a00996',
            'airfare_cost' => 'Text 524e4b94-9bbc-4c93-bfe0-2d279642b208',
            'other_cost' => 'Text 08d16936-ef3e-4507-a6f9-16a03c3206a2',
            'lodging_cost' => 'Text 3fbcc863-518a-4321-970d-37a3279e83c1',
            'registration_cost' => 'Text cf195e9c-d7df-45f7-966b-59124a0eaa3a',
            'tar_total_cost' => 'Text 6a2d7da0-1bfc-41e7-b703-919ed44e4df1',
            'departure_date' => 'Text f7fd4fa3-ed52-4724-a6fb-8d20ea349d60',
            'return_date' => 'Text 90a3327d-bea3-464b-a48a-4fa1998038c0',
            'peoplesoft_project_number' => 'Text 4d891474-a261-49a4-96a7-c2dcf3f4122b',
            'peoplesoft_account_code' => 'Text 13513f85-0589-4ce8-bd28-bccb967363f3',
            'accounting_total_cost' => 'Text 63e8a56e-7740-4784-848c-c35973aede12',
            'covid_destination' => 'Text f8a52e89-4ad8-4d22-b907-1b07b3f64dd7',
            'covid_dates' => 'Text 5bc7773d-a2da-4872-be4b-2bec828b9874',
            'home_department' => 'Text 10438f34-34d6-4b32-bbf3-3b9e32245d88',
            'employee_id' => 'Text ff02a451-65d5-49b9-bb29-6158fcb72229',
        ],
    ],

    'domestic_travel_authority_request_with_airfare' => [
        'powerform_url' => 'https://na3.docusign.net/Member/PowerFormSigning.aspx?PowerFormId=9ea288ba-6a87-4ca9-b936-d1b136853ffd&env=na3&acct=7554587e-5afc-4247-8977-071ef5c80e3b&v=2',
        'traveler_name' => 'Traveler',
        'ingest_mailbox_name' => 'Ingest Mailbox',
        'fields' => [
            'state_contract_airline' => 'Checkbox d259085e-55cd-4e6e-a931-3d665524e8f1',
            'non_contract_airline' => 'Checkbox 92887317-7509-4759-b864-f1a909dc2998',
            'personal_automobile' => 'Checkbox 455c590b-07cb-4259-92a5-72c8f1be7be4',
            'rental_vehicle' => 'Checkbox 7cc47168-9e71-4f87-ae26-16378d53e14f',
            'other' => 'Checkbox b00fe61b-4fa1-4251-800d-196c855cca34',
            'itinerary' => 'Text c5f02d8b-07f8-4217-a998-789a12f249e3',
            'purpose' => 'purpose',
            'airfare_cost' => 'Text 524e4b94-9bbc-4c93-bfe0-2d279642b208',
            'other_cost' => 'Text 08d16936-ef3e-4507-a6f9-16a03c3206a2',
            'lodging_cost' => 'Text 3fbcc863-518a-4321-970d-37a3279e83c1',
            'registration_cost' => 'Text cf195e9c-d7df-45f7-966b-59124a0eaa3a',
            'tar_total_cost' => 'Text 6a2d7da0-1bfc-41e7-b703-919ed44e4df1',
            'departure_date' => 'Text f7fd4fa3-ed52-4724-a6fb-8d20ea349d60',
            'return_date' => 'Text 90a3327d-bea3-464b-a48a-4fa1998038c0',
            'peoplesoft_project_number' => 'Text 4d891474-a261-49a4-96a7-c2dcf3f4122b',
            'peoplesoft_account_code' => 'Text 13513f85-0589-4ce8-bd28-bccb967363f3',
            'accounting_total_cost' => 'Text 63e8a56e-7740-4784-848c-c35973aede12',
            'airfare_phone' => 'phone',
            'airfare_non_employee_checkbox' => 'Checkbox d8c5db43-a953-4ba0-b16e-0efc4e61c758',
            'airfare_employee_checkbox' => 'Checkbox 919ae169-7eed-4b4d-b061-3cded59af5af',
            'airfare_non_employee_domestic_checkbox' => 'Checkbox cf8f7460-d006-4b9f-96f5-b7de53575d02',
            'airfare_employee_domestic_checkbox' => 'Checkbox a64ad0ca-e8ca-429f-ae83-4cff0a564d2d',
            'covid_destination' => 'Text f8a52e89-4ad8-4d22-b907-1b07b3f64dd7',
            'covid_dates' => 'dates',
            'home_department' => 'Text 10438f34-34d6-4b32-bbf3-3b9e32245d88',
            'employee_id' => 'employee_id',
        ],
    ],

    'international_travel_authority_request_with_airfare' => [
        'powerform_url' => 'https://na3.docusign.net/Member/PowerFormSigning.aspx?PowerFormId=7b39f0ec-5bcb-4a53-8d55-196583af290e&env=na3&acct=7554587e-5afc-4247-8977-071ef5c80e3b&v=2',
        'traveler_name' => 'Traveler',
        'ingest_mailbox_name' => 'Ingest Mailbox',
        'fields' => [
            'state_contract_airline' => 'state_contract_airline',
            'non_contract_airline' => 'non_contract_airline',
            'personal_automobile' => 'personal_automobile',
            'rental_vehicle' => 'rental_vehicle',
            'other' => 'other',
            'itinerary' => 'itinerary',
            'purpose' => 'purpose',
            'airfare_cost' => 'airfare_cost',
            'other_cost' => 'other_cost',
            'lodging_cost' => 'lodging_cost',
            'registration_cost' => 'registration_cost',
            'total_cost' => 'total_cost',
            'departure_date' => 'departure_date',
            'return_date' => 'return_date',
            'employee_id' => 'employee_id',
            'home_department' => 'home_department',
            'driver_worktag' => 'driver_worktag',
            'account_code' => 'account_code',
            'export_control' => 'export_control',
            'export_control_description' => 'export_control_description',
            'embargoed_destination' => 'embargoed_destination',
            'embargoed_countries' => 'embargoed_countries',
            'biological_materials' => 'biological_materials',
            'biological_materials_description' => 'biological_materials_description',
            'equipment' => 'equipment',
            'equipment_description' => 'equipment_description',
            'phone' => 'phone',
            'dates' => 'dates',
            'non_employee' => 'non_employee',
            'employee' => 'employee',
            'non_employee_account' => 'non_employee_account',
            'employee_account' => 'employee_account',
            'destination' => 'destination',
            'international_travel_justification' => 'international_travel_justification',
        ],
    ],

    'covid_risk_acknowledgement' => [
        'powerform_url' => 'https://na3.docusign.net/Member/PowerFormSigning.aspx?PowerFormId=96d129ad-3097-4842-a6db-d8b68df351cd&env=na3&acct=7554587e-5afc-4247-8977-071ef5c80e3b&v=2',
        'traveler_name' => 'Traveler',
        'ingest_mailbox_name' => 'Ingest Mailbox',
        'fields' => [
            'covid_destination' => 'Text f8a52e89-4ad8-4d22-b907-1b07b3f64dd7',
            'covid_dates' => 'Text 5bc7773d-a2da-4872-be4b-2bec828b9874',
        ],
    ],

    'ingest_mailbox' => env('DOCUSIGN_INGEST_MAILBOX'),

    'single_sign_on_url' => 'https://account.docusign.com/organizations/7097b206-c4cf-4e3c-8e62-2219e510c3c3/saml2/login/sp/4564d62e-c67b-46f2-b1d7-f8ccc628a269',

    'client_id' => env('DOCUSIGN_CLIENT_ID'),

    'client_secret' => env('DOCUSIGN_CLIENT_SECRET'),

    'connect_timeout' => env('DOCUSIGN_CONNECT_TIMEOUT'),

    'read_timeout' => env('DOCUSIGN_READ_TIMEOUT'),

    'api_base_path' => env('DOCUSIGN_API_BASE_PATH'),

    'account_id' => env('DOCUSIGN_ACCOUNT_ID'),

    'impersonate_user_id' => env('DOCUSIGN_IMPERSONATE_USER_ID'),

    'private_key' => env('DOCUSIGN_PRIVATE_KEY'),

    'templates' => [
        'membership_agreement_member_only' => env('DOCUSIGN_MEMBERSHIP_AGREEMENT_MEMBER_ONLY_TEMPLATE_ID'),

        'membership_agreement_member_and_guardian' => env(
            'DOCUSIGN_MEMBERSHIP_AGREEMENT_MEMBER_AND_GUARDIAN_TEMPLATE_ID'
        ),
    ],

    'service_account_reply_to' => [
        'address' => 'support@robojackets.org',
        'name' => 'RoboJackets',
    ],
];
