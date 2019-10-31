<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

class LinkedGitHubAccounts extends FieldByActiveBreakdown
{
    /**
     * Create a new LinkedGitHubAccounts metric.
     */
    public function __construct()
    {
        parent::__construct('github_username');
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'linked-github-accounts';
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Linked GitHub Accounts';
    }
}
