<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Major;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class ActiveStudentsInMajor extends Value
{
    /**
     * The element's icon.
     *
     * @var string
     */
    public $icon = 'user-group';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        $count = Major::find($request->resourceId)->members()->active()->count();

        return $this->result($count)->allowZeroResult();
    }

    /**
     * Get the URI key for the metric.
     */
    #[\Override]
    public function uriKey(): string
    {
        return 'active-students-in-major';
    }
}
