<?php

declare(strict_types=1);

return [
    'resumes' => env('FEATURE_ENABLE_RESUMES', false),
    'card-present-payments' => env('FEATURE_ENABLE_CARD_PRESENT_PAYMENTS', false),
    'sandbox-mode' => env('FEATURE_SANDBOX_MODE', false),
    'sandbox-users' => array_filter(explode(',', env('SANDBOX_USERS', ''))),
    'whitepages' => env('FEATURE_ENABLE_WHITEPAGES', true),
    'prune-access' => env('FEATURE_PRUNE_ACCESS', ! env('APP_DEBUG', false)),
];
