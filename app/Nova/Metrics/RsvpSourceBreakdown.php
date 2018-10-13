<?php

namespace App\Nova\Metrics;

use App\Rsvp;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class RsvpSourceBreakdown extends Partition
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'RSVP Source Breakdown';

    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->count($request, Rsvp::class, 'source')
                    ->label(function ($value) {
                        switch ($value) {
                            case null:
                            return '<unknown>';
                            default:
                            return $value;
                        }
                    });
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'rsvp-source-breakdown';
    }
}
