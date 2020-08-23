<?php

declare(strict_types=1);

return [
    'resumes' => env('FEATURE_ENABLE_RESUMES', false),
    'card-present-payments' => env('FEATURE_ENABLE_CARD_PRESENT_PAYMENTS', false),
];
