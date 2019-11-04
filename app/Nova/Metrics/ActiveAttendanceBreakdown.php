<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Attendance;
use App\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class ActiveAttendanceBreakdown extends Partition
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->showAllTime ? 'Active Attendees' : 'Attendees Last 4 Weeks';
    }

    /**
     * Whether to show based on all attendance records or only those from the last two weeks.
     *
     * @var bool
     */
    protected $showAllTime = false;

    /**
     * Create a new ActiveAttendanceBreakdown metric.
     *
     * @param bool  $showAllTime
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(bool $showAllTime = false)
    {
        $this->showAllTime = $showAllTime;
    }

    /**
     * Calculate the value of the metric.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Laravel\Nova\Metrics\PartitionResult
     */
    public function calculate(Request $request): PartitionResult
    {
        // If a user is found, this will give "Active" in the active column, otherwise the column will be null
        $query = Attendance::selectRaw('count(distinct gtid) as aggregate')
            ->selectSub(
                User::whereRaw('attendance.gtid = users.gtid')
                    ->active()
                    ->selectRaw("'Active'"),
                'active'
            );

        if ($request->resourceId) {
            $query = $query
                ->where('attendable_id', $request->resourceId)
                ->where('attendable_type', get_class($request->model()));
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
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'active-attendance-breakdown';
    }
}
