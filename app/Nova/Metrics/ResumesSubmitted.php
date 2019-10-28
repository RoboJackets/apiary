<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class ResumesSubmitted extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Laravel\Nova\Metrics\ValueResult
     */
    public function calculate(Request $request): ValueResult
    {
        return $this->result(
            User::active()->where('resume_date', '>', now()->subDays($request->range)->startOfDay())->count()
        );
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int,string>
     */
    public function ranges(): array
    {
        return [
            30 => '30 Days',
            60 => '60 Days',
            60 => '90 Days',
            60 => '180 Days',
            365 => '365 Days',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'resumes-submitted';
    }
}
