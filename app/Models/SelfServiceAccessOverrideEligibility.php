<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;

class SelfServiceAccessOverrideEligibility
{
    /**
     * Indicates if the user is currently eligible for a self-service override.
     *
     * @var bool
     */
    public bool $eligible;

    /**
     * If user is not eligible for a self-service override, this variable indicates whether the user could
     * become eligible by themselves.
     *
     * @var bool
     */
    public bool $user_rectifiable;

    /**
     * If user is not eligible for a self-service override, this summarizes why the user is ineligible for a
     * self-service override.
     *
     * @var string
     */
    public string $ineligible_reason;

    /**
     * The date the user's self-service override would end, if they were eligible and applied
     * it at this instant. May be null if an self-service override end date cannot be provided.
     *
     * @var CarbonImmutable|null
     */
    public ?CarbonImmutable $override_until;

    /**
     * An associative (string => bool) array of required tasks and their completion statuses. Array element
     * values should be set to true if the task has been completed, or false otherwise. All tasks must be completed to
     * receive a self-service override.
     *
     * @var array<string, bool>
     */
    public array $required_tasks = [];

    /**
     * An associative (string => bool) array of required system conditions and whether they are currently
     * satisfied. Array element values should be set to true if the condition is currently satisfied, or false
     * otherwise. All conditions must be satisfied (i.e., true) to receive a self-service override.
     *
     * @var array<string, bool>
     */
    public array $required_conditions = [];

    /**
     * Replace the value of the required conditions array.
     *
     * @param  array<string, bool>  $required_conditions
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setRequiredConditions(array $required_conditions): SelfServiceAccessOverrideEligibility
    {
        $this->required_conditions = $required_conditions;

        return $this;
    }

    /**
     * Replace the value of the required tasks array.
     *
     * @param  array  $required_tasks
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setRequiredTasks(array $required_tasks): SelfServiceAccessOverrideEligibility
    {
        $this->required_tasks = $required_tasks;

        return $this;
    }

    /**
     * Indicates whether the user can become eligible for a self-service override.
     *
     * @return bool
     */
    public function isUserRectifiable(): bool
    {
        return $this->user_rectifiable;
    }

    /**
     * Set whether the user can become eligible for a self-service override.
     *
     * @param  bool  $user_rectifiable
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setUserRectifiable(bool $user_rectifiable): SelfServiceAccessOverrideEligibility
    {
        $this->user_rectifiable = $user_rectifiable;

        return $this;
    }

    /**
     * Get the date on which the self-service override would/will expire.
     *
     * @return CarbonImmutable|null
     */
    public function getOverrideUntil(): ?CarbonImmutable
    {
        return $this->override_until;
    }

    /**
     * Set the date on which the self-service override would/will expire.
     *
     * @param  CarbonImmutable|null  $override_until
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setOverrideUntil(?CarbonImmutable $override_until): SelfServiceAccessOverrideEligibility
    {
        $this->override_until = $override_until;

        return $this;
    }

    /**
     * Indicates whether the user is eligible for a self-service override right now.
     *
     * @return bool
     */
    public function isEligible(): bool
    {
        return $this->eligible;
    }

    /**
     * Set whether the user is eligible for a self-service override right now.
     *
     * @param  bool  $is_eligible
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setEligibility(bool $is_eligible): SelfServiceAccessOverrideEligibility
    {
        $this->eligible = $is_eligible;

        return $this;
    }

    /**
     * Returns a simple explanation of why the user is currently ineligible for a self-service override.
     *
     * @return string
     */
    public function getIneligibleReason(): string
    {
        return $this->ineligible_reason;
    }

    /**
     * Set a simple explanation of why the user is currently ineligible for a self-service override.
     *
     * @param  string  $ineligible_reason
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setIneligibleReason(string $ineligible_reason): SelfServiceAccessOverrideEligibility
    {
        $this->ineligible_reason = $ineligible_reason;

        return $this;
    }

    /**
     * Remove each element of an associative array whose value is falsy.
     *
     * @param  array<  $arr  An associative array to filter
     * @return array A 1D array of the truthy values in $arr
     */
    private function removeFalsyAssocArrayValues(array $arr): array
    {
        $falsy_vals = array_map(static fn ($k, $v) => ! $v ? $k : null, array_keys($arr), array_values($arr));

        return array_filter($falsy_vals);
    }

    /**
     * Return an array of the incomplete required tasks for the user to potentially (if all required conditions are also
     * satisfied) become eligible for a self-service override.
     *
     * @return string[]
     */
    public function getRemainingTasks(): array
    {
        $remainingTasks = $this->removeFalsyAssocArrayValues($this->required_tasks);
        if (0 === count($remainingTasks)) {
            return ['None'];
        }

        return $remainingTasks;
    }

    /**
     * Return an array of the unsatisfied required conditions for the user to potentially (if all required tasks are
     * also completed) become eligible for a self-service override.
     *
     * @return string[]
     */
    public function getUnmetConditions(): array
    {
        $remainingConditions = $this->removeFalsyAssocArrayValues($this->required_conditions);
        if (0 === count($remainingConditions)) {
            return ['None'];
        }

        return $remainingConditions;
    }

    /**
     * Returns a string representation of the current status of this user's eligibility for a self-service override.
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->eligible) {
            return 'Eligible';
        }

        $remaining_tasks = implode(', ', $this->getRemainingTasks());
        $unmet_conditions = implode(', ', $this->getUnmetConditions());

        if ($this->isUserRectifiable()) {
            return "Eligible if remaining required tasks are completed. Incomplete tasks: $remaining_tasks.";
        }

        return "Ineligible. Unmet conditions: $unmet_conditions. Incomplete tasks: $remaining_tasks.";
    }
}
