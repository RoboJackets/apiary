<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Major;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class StudentsInMajor extends Value
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        $count = Major::where('id', $request->resourceId)->get()->first()->members()->count();

        return $this->result($count);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'students-in-major';
    }
}
