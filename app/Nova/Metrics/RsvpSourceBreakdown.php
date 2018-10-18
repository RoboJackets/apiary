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
        return $this->result(Rsvp::where('event_id', $request->resourceId)
            ->leftJoin('recruiting_visits', 'source', '=', 'visit_token')
            ->selectRaw('if(recruiting_visits.id, "Recruiting Email", source) as rsvpsource')
            ->selectRaw('count(rsvps.id) as aggregate')
            ->groupBy('rsvpsource')
            ->orderBy('aggregate', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                if ($item->rsvpsource) {
                    return [$item->rsvpsource => $item->aggregate];
                } else {
                    return ['<unknown>' => $item->aggregate];
                }
            })->toArray()
        );
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
