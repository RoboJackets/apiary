<?php

namespace App\Models;

use Carbon\CarbonImmutable;

class SelfServiceAccessOverrideEligibility
{
    /**
     * @var bool Indicates if the user is currently eligible for a self-service override.
     */
    public bool $eligible;

    /**
     * @var bool If is false, this variable indicates whether the user could become eligible by themselves.
     */
    public bool $user_rectifiable;

    /**
     * @var string If is false, this summarizes why the user is ineligible for a self-service override.
     */
    public string $ineligible_reason;

    /**
     * @var CarbonImmutable|null The date the user's self-service override would end, if they were eligible and applied
     *                           it at this instant. May be null if an self-service override end date cannot be provided.
     */
    public ?CarbonImmutable $override_until;

    /**
     * @var array An associative (string => bool) array of required tasks and their completion statuses. Array element
     *            values should be set to true if the task has been completed, or false otherwise. All tasks must be completed to
     *            receive a self-service override.
     */
    public array $required_tasks = [];

    /**
     * @var array An associative (string => bool) array of required system conditions and whether they are currently
     *            satisfied. Array element values should be set to true if the condition is currently satisfied, or false
     *            otherwise. All conditions must be satisfied (i.e., true) to receive a self-service override.
     */
    public array $required_conditions = [];

    /**
     * @param  array  $required_conditions
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setRequiredConditions(array $required_conditions): SelfServiceAccessOverrideEligibility
    {
        $this->required_conditions = $required_conditions;

        return $this;
    }

    /**
     * @param  bool  $eligible
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setEligible(bool $eligible): SelfServiceAccessOverrideEligibility
    {
        $this->eligible = $eligible;

        return $this;
    }

    /**
     * @param  array  $required_tasks
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setRequiredTasks(array $required_tasks): SelfServiceAccessOverrideEligibility
    {
        $this->required_tasks = $required_tasks;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUserRectifiable(): bool
    {
        return $this->user_rectifiable;
    }

    /**
     * @param  bool  $user_rectifiable
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
     * @param  CarbonImmutable  $override_until
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setOverrideUntil(?CarbonImmutable $override_until): SelfServiceAccessOverrideEligibility
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
     * @param  bool  $is_eligible
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
     * @param  string  $ineligible_reason
     * @return SelfServiceAccessOverrideEligibility
     */
    public function setIneligibleReason(string $ineligible_reason): SelfServiceAccessOverrideEligibility
    {
        $this->ineligible_reason = $ineligible_reason;

        return $this;
    }

    /**
     * Remove elements of an associative array where the element's value is falsy.
     *
     * @param  array  $arr  An associative array to filter
     * @return array A 1D array of the truthy values in $arr
     */
    private function removeFalsyAssocArrayValues(array $arr): array
    {
        $falsy_vals = array_map(fn ($k, $v) => ! $v ? $k : null, array_keys($arr), array_values($arr));

        return array_filter($falsy_vals);
    }

    public function getRemainingTasks(): array
    {
        $remainingTasks = $this->removeFalsyAssocArrayValues($this->required_tasks);
        if (! $remainingTasks) {
            return ['None'];
        }

        return $remainingTasks;
    }

    public function getUnmetConditions(): array
    {
        $remainingConditions = $this->removeFalsyAssocArrayValues($this->required_conditions);
        if (! $remainingConditions) {
            return ['None'];
        }

        return $remainingConditions;
    }

    public function __toString()
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
