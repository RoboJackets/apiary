<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class VerifiedPhoneNumbers extends Value
{
    /**
     * The element's icon.
     *
     * @var string
     */
    public $icon = 'phone';

    /**
     * The help text for the metric.
     *
     * @var string
     */
    public $helpText = 'Total number of active users with a verified phone number';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): ValueResult
    {
        return $this->result(User::active()->where('phone_verified', true)->count())->allowZeroResult();
    }

    /**
     * Get the URI key for the metric.
     */
    #[\Override]
    public function uriKey(): string
    {
        return 'verified-phone-numbers';
    }
}
