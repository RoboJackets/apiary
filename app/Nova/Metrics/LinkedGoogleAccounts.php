<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

class LinkedGoogleAccounts extends FieldByActiveBreakdown
{
    /**
     * Create a new LinkedGoogleAccounts metric.
     */
    public function __construct()
    {
        parent::__construct('gmail_address');
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'linked-google-accounts';
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Linked Google Accounts';
    }
}
