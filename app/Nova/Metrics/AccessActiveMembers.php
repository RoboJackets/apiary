<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class AccessActiveMembers extends Value
{
    /**
     * The element's icon.
     *
     * @var string
     */
    public $icon = 'key';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): ValueResult
    {
        return $this->result(User::accessActive()->count())->allowZeroResult();
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'access-active-members';
    }
}
