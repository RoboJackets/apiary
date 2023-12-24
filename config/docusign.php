<?php

declare(strict_types=1);

return [
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
