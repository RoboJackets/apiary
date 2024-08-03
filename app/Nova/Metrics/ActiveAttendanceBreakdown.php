<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class ActiveAttendanceBreakdown extends Partition
{
    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        $ret = $this->showAllTime ? 'Active Attendees' : 'Attendees Last 4 Weeks';
        if ($this->resourceId !== -1) {
            $ret .= ' for '.Event::where('id', $this->resourceId)->sole()->name;
        }

        return $ret;
    }

    /**
     * Whether to show based on all attendance records or only those from the last two weeks.
     *
     * @var bool
     */
    protected $showAllTime = false;

    /**
     * If displaying on the main dashboard, this indicates the event to get attendance from.
     */
    private $resourceId = null;

    /**
     * If displaying on a page different from the type of attendable you are showing.
     * This indicates the morphClass of the attendable (for example, an event).
     */
    private $attendableType = null;

    /**
     * Create a new ActiveAttendanceBreakdown metric.
     */
    public function __construct(bool $showAllTime = false, $resourceId = null, $attendableType = null)
    {
        parent::__construct();
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): PartitionResult
    {
        $resourceId = $request->resourceId ?? $this->resourceId;
        // If a user is found, this will give "Active" in the active column, otherwise the column will be null
        $query = Attendance::selectRaw('count(distinct gtid) as aggregate')
            ->selectSub(
                User::whereRaw('attendance.gtid = users.gtid')
                    ->active()
                    ->selectRaw("'Active'"),
                'active'
            );

        if ($resourceId !== null) {
            $query = $query
                ->where('attendable_id', $resourceId)
                ->where(
                    'attendable_type',
                    $request?->model()?->getMorphClass() ?? $this->attendableType
                );
        }

        if (! $this->showAllTime) {
            $query = $query->whereBetween('created_at', [now()->subDays(28)->startOfDay(), now()]);
        }

        $result = $query
            ->groupBy('active')
            ->orderByRaw('aggregate desc, active desc')
            ->get()
            ->mapWithKeys(static function (object $item): array {
                $key = $item->active;
                if (! $item->active) {
                    $key = 'Inactive';
                }

                return [$key => $item->aggregate];
            })->toArray();

        return $this->result($result);
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return $this->resourceId === -1 ? 'active-attendance-breakdown' :
            '../'.$this->attendableType.'/'.$this->resourceId.'/metrics/active-attendance-breakdown';
    }
}
