<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Metrics\ValueResult;
use Laravel\Nova\Nova;

class SwagPickupRate extends TextMetric
{
    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        return Nova::humanize($this->swagType).' Pickup Rate';
    }

    /**
     * Which type of swag we're looking at, either 'shirt' or 'polo'.
     *
     * @var string
     */
    protected $swagType;

    /**
     * Create a new SwagPickupRate metric. swagType can be either 'shirt' or 'polo'.
     */
    public function __construct(string $swagType)
    {
        parent::__construct();
        if (! in_array($swagType, ['shirt', 'polo'], true)) {
            Log::error('Invalid swag type given to SwagPickupRate metric: "'.$swagType.'"');
            abort(400, 'Invalid swag type');
        }

        $this->swagType = $swagType;
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): ValueResult
    {
        if ('dues-packages' === $request->resource) {
            $package = DuesPackage::where('id', $request->resourceId)->withTrashed()->first();
            $eligible = 'shirt' === $this->swagType ? $package->eligible_for_shirt : $package->eligible_for_polo;

            if (! $eligible) {
                return $this->result('n/a');
            }
        }

        $result = DuesTransaction::when(
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
        )
        ->selectRaw('`swag_'.$this->swagType.'_provided` is not null as provided')
        ->selectRaw('count(id) as aggregate')
        ->groupBy('provided')
        ->get()
        ->mapWithKeys(static function (object $item): array {
            return [$item->provided ? 'true' : 'false' => $item->aggregate];
        })->toArray();

        $hasAnyPickedUp = isset($result['true']);
        $hasAnyNotPickedUp = isset($result['false']);

        if ($hasAnyPickedUp && $hasAnyNotPickedUp) {
            $value = sprintf('%.1f', ($result['true'] / ($result['true'] + $result['false']) * 100));

            return $this->result($value.'%');
        }

        if ($hasAnyPickedUp) {
            return $this->result('100%');
        }

        if ($hasAnyNotPickedUp) {
            return $this->result('0%');
        }

        return $this->result('No transactions');
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'swag-pickup-rate-'.$this->swagType;
    }
}
