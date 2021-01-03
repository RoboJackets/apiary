<?php

declare(strict_types=1);

return [
    /*
     * The max age of attendance before SUMS access is revoked, in plain English.
     */
    'attendance_timeout_limit' => env('SUMS_ATTENDANCE_TIMEOUT_LIMIT', '4 weeks ago'),

    /**
     * Whether an up-to-date membership agreement is required for access to SUMS.
     */
    'requires_agreement' => env('SUMS_REQUIRES_AGREEMENT', false),
];
