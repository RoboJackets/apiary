<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Major;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class MajorsMissingDisplayNames extends Value
{
    /**
     * The element's icon.
     *
     * @var string
     */
    public $icon = 'eye-off';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->result(Major::where('display_name', null)->count())->allowZeroResult();
    }

    /**
     * Get the URI key for the metric.
     */
    #[\Override]
    public function uriKey(): string
    {
        return 'majors-missing-display-names';
    }
}
