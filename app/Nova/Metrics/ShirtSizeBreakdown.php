<?php

namespace App\Nova\Metrics;

use App\User;
use Laravel\Nova\Nova;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class ShirtSizeBreakdown extends Partition
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return Nova::humanize($this->swagType).' Sizes';
    }

    /**
     * Which type of swag we're looking at, either 'shirt' or 'polo'.
     *
     * @var bool
     */
    protected $swagType;

    /**
     * Create a new ShirtSizeBreakdown metric. swagType can be either 'shirt' or 'polo'.
     *
     * @param  bool  $swagType
     */
    public function __construct($swagType)
    {
        if (! in_array($swagType, ['shirt', 'polo'])) {
            \Log::error('Invalid swag type given to ShirtSizeBreakdown metric: "'.$swagType.'"');
            abort(400, 'Invalid swag type');

            return;
        }

        $this->swagType = $swagType;
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $column = $this->swagType == 'shirt' ? 'shirt_size' : 'polo_size';

        return $this->result(User::when(
                $request->resourceId,
                function ($query, $resourceId) {
                    // When on the detail page, look at the particular package
                    return $query->whereHas('dues', function ($q) use ($resourceId) {
                        return $q->where('dues_package_id', $resourceId)->paid();
                    });
                },
                function ($query) {
                    // When on the index, just look at all active users
                    return $query->active();
                }
            )->select($column.' as size')
            ->selectRaw('count('.$column.') as aggregate')
            ->groupBy('size')
            ->orderBy('size')
            ->get()
            ->mapWithKeys(function ($item) {
                $shirt_sizes = [
                    's' => 'Small',
                    'm' => 'Medium',
                    'l' => 'Large',
                    'xl' => 'Extra-Large',
                    'xxl' => 'XXL',
                    'xxxl' => 'XXXL',
                ];

                return [$item->size ? $shirt_sizes[$item->size] : 'Unknown' => $item->aggregate];
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
        return $this->swagType.'-size-breakdown';
    }
}
