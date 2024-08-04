<?php

declare(strict_types=1);

return [

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => '/github/callback',
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => '/google/callback',
    ],

    'team_slack_webhook_url' => env('TEAM_SLACK_WEBHOOK_URL'),

    'treasurer_slack_webhook_url' => env('TREASURER_SLACK_WEBHOOK_URL'),

    'treasurer_email' => env('TREASURER_EMAIL'),

    'core_slack_webhook_url' => env('CORE_SLACK_WEBHOOK_URL'),

    'core_officers_slack_webhook_url' => env('CORE_OFFICERS_SLACK_WEBHOOK_URL'),

];
