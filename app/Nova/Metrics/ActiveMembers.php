<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class ActiveMembers extends Value
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
        if (isset($request->resourceId)) {
            $team = Team::where('id', $request->resourceId)->first();
            $count = $team ?
                $team->members()
                ->active()
                ->count()
                : 0;

            return $this->result($count)->allowZeroResult();
        }

        return $this->result(User::active()->count())->allowZeroResult();
    }

    /**
     * Get the URI key for the metric.
     */
    #[\Override]
    public function uriKey(): string
    {
        return 'active-members';
    }
}
