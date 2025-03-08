<?php

declare(strict_types=1);

namespace App;

use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

/**
 * Verify Horizon is running.
 *
 * @author https://github.com/dominikager
 *
 * @source https://github.com/ans-group/laravel-health-check/issues/78#issue-1847102332
 */
class HorizonHealthCheck extends HealthCheck
{
    /**
     * The name for this health check.
     */
    protected string $name = 'horizon';

    public function __construct(private readonly MasterSupervisorRepository $supervisorRepository)
    {
    }

    #[\Override]
    public function status(): Status
    {
        $masters = $this->supervisorRepository->all();

        if (count($masters) === 0) {
            return $this->problem('Horizon is inactive');
        }

        return collect($masters)->contains(
            static fn (object $master): bool => $master->status === 'paused'
        ) ? $this->problem('Horizon is paused') : $this->okay();
    }
}
