<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\DuesPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Nova;

class ShirtSizeBreakdown extends Partition
{
    /**
     * Get the displayable name of the metric.
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
     */
    public function __construct(string $swagType)
    {
        parent::__construct();
        if (! in_array($swagType, ['shirt', 'polo'], true)) {
            Log::error('Invalid swag type given to ShirtSizeBreakdown metric: "'.$swagType.'"');
            abort(400, 'Invalid swag type');
        }

        $this->swagType = $swagType;
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): PartitionResult
    {
        $column = 'shirt' === $this->swagType ? 'shirt_size' : 'polo_size';

        return $this->result(
            User::select($column.' as size')
            ->selectRaw('count('.$column.') as aggregate')
            ->when(
                $request->resourceId,
                static function (Builder $query, int $resourceId) use ($request): void {
                    // When on the detail page, look at the particular package
                    $query->whereHas('dues', static function (Builder $query) use ($request): void {
                        $query->when(
                            'fiscal-years' === $request->resource,
                            static function (Builder $query, bool $isFiscalYear) use ($request): void {
                                $query
                                    ->whereIn(
                                        'dues_package_id',
                                        DuesPackage::where(
                                            'fiscal_year_id',
                                            $request->resourceId
                                        )
                                        ->get()
                                        ->map(static function (DuesPackage $package): int {
                                            return $package->id;
                                        })
                                    );
                            },
                            static function (Builder $query) use ($request): void {
                                $query->where('dues_package_id', $request->resourceId);
                            }
                        )->paid();
                    });
                },
                static function (Builder $query): void {
                    // When on the index, just look at all active users
                    $query->active();
                }
            )
            ->groupBy('size')
            ->orderBy('size')
            ->get()
            ->mapWithKeys(static function (object $item): array {
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
     */
    public function uriKey(): string
    {
        return $this->swagType.'-size-breakdown';
    }
}
