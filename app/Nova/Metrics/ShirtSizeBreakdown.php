<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\User;
use Laravel\Nova\Nova;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;
use Illuminate\Database\Eloquent\Builder as Eloquent;

class ShirtSizeBreakdown extends Partition
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name(): string
    {
        return Nova::humanize($this->swagType).' Sizes';
    }

    /**
     * Which type of swag we're looking at, either 'shirt' or 'polo'.
     *
     * @var string
     */
    protected $swagType;

    /**
     * Create a new ShirtSizeBreakdown metric. swagType can be either 'shirt' or 'polo'.
     *
     * @param string $swagType
     */
    public function __construct(string $swagType)
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
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Laravel\Nova\Metrics\PartitionResult
     */
    public function calculate(Request $request): PartitionResult
    {
        $column = 'shirt' === $this->swagType ? 'shirt_size' : 'polo_size';

        return $this->result(
            User::select($column.' as size')
            ->selectRaw('count('.$column.') as aggregate')
            ->when(
                $request->resourceId,
                static function (Eloquent $query, int $resourceId) {
                    // When on the detail page, look at the particular package
                    return $query->whereHas('dues', static function (Eloquent $q) use ($resourceId): Eloquent {
                        return $q->where('dues_package_id', $resourceId)->paid();
                    });
                },
                static function (Eloquent $query): Eloquent {
                    // When on the index, just look at all active users
                    return $query->active();
                }
            )
            ->groupBy('size')
            ->orderBy('size')
            ->get()
            ->mapWithKeys(static function ($item) {
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
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return $this->swagType.'-size-breakdown';
    }
}
