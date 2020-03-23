<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

class SchoolsBreakdown extends MajorDemographicsBreakdown
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Schools of Active Members';

    /**
     * Create a new MajorsBreakdown metric.
     */
    public function __construct()
    {
        parent::__construct('school');
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'schools-breakdown';
    }
}
