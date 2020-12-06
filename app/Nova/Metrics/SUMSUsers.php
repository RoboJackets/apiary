<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class SUMSUsers extends Value
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): ValueResult
    {
        return $this->result(User::accessActive()->where('exists_in_sums', 1)->count())->allowZeroResult();
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'sums-users';
    }

    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        return 'SUMS Users';
    }
}
