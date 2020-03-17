<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Major;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class TotalMajors extends Value
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count($request, Major::class);
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'total-majors';
    }
}
