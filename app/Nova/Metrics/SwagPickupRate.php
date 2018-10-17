<?php

namespace App\Nova\Metrics;

use App\DuesPackage;
use Laravel\Nova\Nova;
use App\DuesTransaction;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;

class SwagPickupRate extends Value
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return Nova::humanize($this->swagType).' Pickup Rate';
    }

    /**
     * Which type of swag we're looking at, either 'shirt' or 'polo'.
     *
     * @var bool
     */
    protected $swagType;

    /**
     * Create a new SwagPickupRate metric. swagType can be either 'shirt' or 'polo'.
     *
     * @param  bool  $swagType
     */
    public function __construct($swagType)
    {
        if (! in_array($swagType, ['shirt', 'polo'])) {
            \Log::error('Invalid swag type given to SwagPickupRate metric: "'.$swagType.'"');
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
        $package = DuesPackage::where('id', $request->resourceId)->first();
        $eligible = $this->swagType == 'shirt' ? $package->eligible_for_shirt : $package->eligible_for_polo;

        if (! $eligible) {
            return $this->result('n/a');
        }

        $result = DuesTransaction::where('dues_package_id', $request->resourceId)
            ->selectRaw('`swag_'.$this->swagType.'_provided` is not null as provided')
            ->selectRaw('count(id) as aggregate')
            ->groupBy('provided')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->provided ? 'true' : 'false' => $item->aggregate];
            })->toArray();

        $hasAnyPickedUp = isset($result['true']);
        $hasAnyNotPickedUp = isset($result['false']);

        if ($hasAnyPickedUp && $hasAnyNotPickedUp) {
            $value = sprintf('%.1f', ($result['true'] / ($result['true'] + $result['false']) * 100));

            return $this->result($value.'%');
        } elseif ($hasAnyPickedUp) {
            return $this->result('100%');
        } elseif ($hasAnyNotPickedUp) {
            return $this->result('0%');
        } else {
            return $this->result('No transactions');
        }
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [];
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
        return 'swag-pickup-rate-'.$this->swagType;
    }
}
