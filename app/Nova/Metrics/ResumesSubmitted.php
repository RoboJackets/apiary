<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class ResumesSubmitted extends Value
{
    /**
     * The element's icon.
     *
     * @var string
     */
    public $icon = 'upload';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): ValueResult
    {
        return $this->result(
            User::active()->where('resume_date', '>', now()->subDays($request->range)->startOfDay())->count()
        )->allowZeroResult();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int,string>
     */
    #[\Override]
    public function ranges(): array
    {
        return [
            30 => '30 Days',
            60 => '60 Days',
            90 => '90 Days',
            180 => '180 Days',
            365 => '365 Days',
        ];
    }

    /**
     * Get the URI key for the metric.
     */
    #[\Override]
    public function uriKey(): string
    {
        return 'resumes-submitted';
    }
}
