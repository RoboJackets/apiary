<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Team;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class TotalTeamMembers extends Value
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
    public function calculate(Request $request): ValueResult
    {
        $count = Team::where('id', $request->resourceId)->first()->members()->count();

        return $this->result($count)->allowZeroResult();
    }

    /**
     * Get the URI key for the metric.
     */
    #[\Override]
    public function uriKey(): string
    {
        return 'total-team-members';
    }
}
