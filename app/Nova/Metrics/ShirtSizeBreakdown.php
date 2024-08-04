<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

// @phan-file-suppress PhanPossiblyFalseTypeReturn

use App\Models\DuesTransaction;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class ShirtSizeBreakdown extends Partition
{
    /**
     * Whether this is for a shirt or a polo.
     *
     * @var string
     */
    protected $type;

    /**
     * Create a new FieldByActiveBreakdown metric.
     */
    public function __construct(string $type)
    {
        parent::__construct();
        $this->type = $type;
    }

    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        return Str::ucfirst($this->type).' Size Breakdown';
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): PartitionResult
    {
        $column_name = $this->type.'_size';
        $resourceId = $request->resourceId;

        return $this->result(
            DB::table('dues_transactions')
                ->select($column_name)
                ->selectRaw('count(distinct users.id) as count')
                ->leftJoin('payments', static function (JoinClause $join): void {
                    $join->on('dues_transactions.id', '=', 'payable_id')
                        ->where('payments.amount', '>', 0)
                        ->where('payments.payable_type', DuesTransaction::getMorphClassStatic())
                        ->whereNull('payments.deleted_at');
                })->leftJoin(
                    'users',
                    'dues_transactions.user_id',
                    '=',
                    'users.id'
                )
                ->whereIn(
                    'dues_transactions.id',
                    static function (Builder $query) use ($resourceId): void {
                        $query->select('dues_transaction_id')
                            ->from('dues_transaction_merchandise')
                            ->where('merchandise_id', $resourceId);
                    }
                )
                ->whereNotNull('payments.id')
                ->whereNull('dues_transactions.deleted_at')
                ->groupBy($column_name)
                ->get()
                ->mapWithKeys(static function (object $row) use ($column_name): array {
                    if ($row->$column_name === null) {
                        return ['Unknown' => $row->count];
                    }

                    return [User::$shirt_sizes[$row->$column_name] => $row->count];
                })
                ->sortBy(static function (string $count, string $shirt_size): int {
                    if ($shirt_size === 'Unknown') {
                        return -1;
                    }

                    return array_search(
                        array_search(
                            $shirt_size,
                            User::$shirt_sizes,
                            true
                        ),
                        array_keys(
                            User::$shirt_sizes
                        ),
                        true
                    );
                })
                ->toArray()
        );
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'shirt-size-breakdown';
    }
}
