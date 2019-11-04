<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\DuesPackage;
use App\DuesTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Metrics\ValueResult;
use Laravel\Nova\Nova;

class SwagPickupRate extends TextMetric
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
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
     *
     * @param string  $swagType
     */
    public function __construct(string $swagType)
    {
        if (! in_array($swagType, ['shirt', 'polo'])) {
            Log::error('Invalid swag type given to SwagPickupRate metric: "'.$swagType.'"');
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
     * @return \Laravel\Nova\Metrics\ValueResult
     */
    public function calculate(Request $request): ValueResult
    {
        $package = DuesPackage::where('id', $request->resourceId)->first();
        $eligible = 'shirt' === $this->swagType ? $package->eligible_for_shirt : $package->eligible_for_polo;

        if (! $eligible) {
            return $this->result('n/a');
        }

        $result = DuesTransaction::where('dues_package_id', $request->resourceId)
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
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'swag-pickup-rate-'.$this->swagType;
    }
}
