<?php

declare(strict_types=1);

return [
    /*
     * The max age of attendance before SUMS access is revoked, in plain english
     */
    'attendance_timeout_limit' => env('SUMS_ATTENDANCE_TIMEOUT_LIMIT', '4 weeks ago'),
];
