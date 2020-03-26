<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

class MajorsBreakdown extends MajorDemographicsBreakdown
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Majors of Active Members';

    /**
     * Create a new MajorsBreakdown metric.
     */
    public function __construct()
    {
        parent::__construct('whitepages_ou');
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'majors-breakdown';
    }
}
