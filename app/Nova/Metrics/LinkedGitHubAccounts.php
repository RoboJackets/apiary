<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

class LinkedGitHubAccounts extends FieldByActiveBreakdown
{
    /**
     * Create a new LinkedGitHubAccounts metric.
     */
    public function __construct()
    {
        parent::__construct('github_username', true);
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'linked-github-accounts';
    }

    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        return 'Linked GitHub Accounts';
    }
}
