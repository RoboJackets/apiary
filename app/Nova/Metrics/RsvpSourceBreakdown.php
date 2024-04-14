<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Event;
use App\Models\Rsvp;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class RsvpSourceBreakdown extends Partition
{
    /**
     * The displayable name of the metric.
     */
    public function name(): string
    {
        return $this->resourceId === -1 ? 'RSVP Sources' : 'RSVP Sources for '.Event::where(
            'id',
            $this->resourceId
        )->sole()->name;
    }

    /**
     * The resource ID attached to this metric.
     * Used when the resource ID cannot be inferred from the Nova page.
     */
    protected $resourceId;

    public function __construct(?int $resourceId = null)
    {
        parent::__construct();
        $this->resourceId = $resourceId;
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        $resourceId = $request->resourceId ?? $this->resourceId;

        return $this->result(
            Rsvp::where('event_id', $resourceId)
                ->leftJoin('recruiting_visits', 'source', '=', 'visit_token')
                ->when(
                    config('database.default') === 'mysql',
                    static function (Builder $query): void {
                        $query->selectRaw('if(recruiting_visits.id, "Recruiting Email", source) as rsvpsource');
                    }
                )
                ->selectRaw('count(rsvps.id) as aggregate')
                ->groupBy('rsvpsource')
                ->orderByDesc('aggregate')
                ->get()
                ->mapWithKeys(static function (object $item): array {
                    if ($item->rsvpsource !== null) {
                        return [$item->rsvpsource => $item->aggregate];
                    }

                    return ['<unknown>' => $item->aggregate];
                })
                ->toArray()
        );
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return $this->resourceId === -1 ? 'rsvp-source-breakdown' : 'rsvp-source-breakdown-'.$this->resourceId;
    }
}
