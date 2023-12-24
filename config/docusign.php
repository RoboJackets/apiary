<?php

declare(strict_types=1);

return [
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
