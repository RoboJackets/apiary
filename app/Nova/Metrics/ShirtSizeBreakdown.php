<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\DuesPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
        $swagType = $this->swagType;

        if ('dues-packages' === $request->resource) {
            $package = DuesPackage::where('id', $request->resourceId)->withTrashed()->first();
            $eligible = 'shirt' === $swagType ? $package->eligible_for_shirt : $package->eligible_for_polo;

            if (! $eligible) {
                return $this->result([]);
            }
        }

        $column = 'shirt' === $swagType ? 'shirt_size' : 'polo_size';

        return $this->result(
            User::select($column.' as size')
            ->selectRaw('count(id) as count')
            ->when(
                $request->resourceId,
                static function (Builder $query, int $resourceId) use ($request, $swagType): void {
                    // When on the detail page, look at the particular package
                    $query->whereHas('dues', static function (Builder $query) use ($request, $swagType): void {
                        $query->when(
                            'fiscal-years' === $request->resource,
                            static function (Builder $query, bool $isFiscalYear) use ($request, $swagType): void {
                                $query
                                    ->whereIn(
                                        'dues_package_id',
                                        static function (QueryBuilder $query) use ($request, $swagType): void {
                                            $query->select('id')
                                                ->from('dues_packages')
                                                ->where('fiscal_year_id', $request->resourceId)
                                                ->where('eligible_for_'.$swagType, true);
                                        }
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
                return [null !== $item->size ? User::$shirt_sizes[$item->size] : 'Unknown' => $item->count];
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
