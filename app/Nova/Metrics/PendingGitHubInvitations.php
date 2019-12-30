<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class PendingGitHubInvitations extends Value
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): ValueResult
    {
        return $this->result(User::accessActive()->where('github_invite_pending', 1)->count());
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'pending-github-invitations';
    }

    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        return 'Pending GitHub Invitations';
    }
}
