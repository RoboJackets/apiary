<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\DuesTransaction;
use App\Models\FiscalYear;
use App\Models\Payment;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class MembersForOneFiscalYear extends Value
{
    /**
     * The element's icon.
     *
     * @var string
     */
    public $icon = 'user-group';

    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Total Members';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): ValueResult
    {
        $result = $this->result(self::query($request->resource, intval($request->resourceId)))->allowZeroResult();

        if ($request->resource === 'fiscal-years') {
            $previousFiscalYear = FiscalYear::where(
                'ending_year',
                intval(FiscalYear::where('id', $request->resourceId)->sole()->ending_year) - 1
            )->first();

            if ($previousFiscalYear !== null) {
                $result->previous(self::query($request->resource, $previousFiscalYear->id));
            }
        }

        return $result;
    }

    private static function query(string $resource, int $resourceId): int
    {
        return Payment::selectRaw('count(distinct user_id) as distinct_users')
            ->where('payable_type', DuesTransaction::getMorphClassStatic())
            ->whereNull('payments.deleted_at')
            ->whereIn('payable_id', static function (Builder $q) use ($resource, $resourceId): void {
                $q->select('id')
                    ->from('dues_transactions')
                    ->when(
                        $resource === 'fiscal-years',
                        static function (Builder $query, bool $isFiscalYear) use ($resourceId): void {
                            $query
                                ->whereIn(
                                    'dues_package_id',
                                    static function (Builder $query) use ($resourceId): void {
                                        $query->select('id')
                                            ->from('dues_packages')
                                            ->where('fiscal_year_id', $resourceId);
                                    }
                                );
                        },
                        static function (Builder $query) use ($resourceId): void {
                            $query->where('dues_package_id', $resourceId);
                        }
                    )
                    ->whereNull('deleted_at');
            })->leftJoin(
                'dues_transactions',
                'payments.payable_id',
                '=',
                'dues_transactions.id'
            )->whereNotNull('dues_transactions.id')
            ->where('payments.amount', '>', 0)
            ->whereNull('dues_transactions.deleted_at')->first()->distinct_users;
    }

    /**
     * Get the URI key for the metric.
     */
    #[\Override]
    public function uriKey(): string
    {
        return 'members';
    }
}
