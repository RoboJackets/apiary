<?php

namespace App\Models;

use Carbon\CarbonImmutable;

class SelfServiceAccessOverrideEligibility
{
    public bool $eligible;
    public bool $user_rectifiable;
    public string $ineligible_reason;
    public CarbonImmutable $override_until;

    /**
     * @return bool
     */
    public function isUserRectifiable(): bool
    {
        return $this->user_rectifiable;
    }

    /**
     * @param bool $user_rectifiable
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setUserRectifiable(bool $user_rectifiable): SelfServiceAccessOverrideEligibility
    {
        $this->user_rectifiable = $user_rectifiable;
        return $this;
    }

    /**
     * @return CarbonImmutable
     */
    public function getOverrideUntil(): CarbonImmutable
    {
        return $this->override_until;
    }

    /**
     * @param CarbonImmutable $override_until
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setOverrideUntil(CarbonImmutable $override_until): SelfServiceAccessOverrideEligibility
    {
        $this->override_until = $override_until;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEligible(): bool
    {
        return $this->eligible;
    }

    /**
     * @param bool $is_eligible
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setEligibility(bool $is_eligible): SelfServiceAccessOverrideEligibility
    {
        $this->eligible = $is_eligible;
        return $this;
    }

    /**
     * @return string
     */
    public function getIneligibleReason(): string
    {
        return $this->ineligible_reason;
    }

    /**
     * @param string $ineligible_reason
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setIneligibleReason(string $ineligible_reason): SelfServiceAccessOverrideEligibility
    {
        $this->ineligible_reason = $ineligible_reason;
        return $this;
    }
}
