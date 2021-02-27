<?php

declare(strict_types=1);

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

namespace App\Nova\Actions;

use App\Models\DuesTransaction;
use App\Models\DuesTransactionMerchandise;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Outhebox\NovaHiddenField\HiddenField as Hidden;

class DistributeMerchandise extends Action
{
    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\Models\Merchandise>  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        if (1 !== count($models)) {
            return Action::danger('This action requires exactly one model.');
        }

        $merchandise = $models->first();
        $provided_by = User::where('id', $fields->provided_by)->sole();
        $provided_to = User::where('id', $fields->provided_to)->sole();

        $dtm = DuesTransactionMerchandise::select(
            'dues_transaction_merchandise.id',
            'dues_transaction_merchandise.provided_at'
        )
        ->leftJoin(
            'dues_transactions',
            static function (JoinClause $join) use ($provided_to): void {
                $join->on('dues_transactions.id', '=', 'dues_transaction_id')
                     ->where('user_id', '=', $provided_to->id);
            }
        )
        ->leftJoin('payments', static function (JoinClause $join): void {
            $join->on('dues_transactions.id', '=', 'payable_id')
                 ->where('payments.payable_type', DuesTransaction::getMorphClassStatic())
                 ->where('payments.amount', '>', 0);
        })
        ->whereNotNull('payments.id')
        ->where('merchandise_id', $merchandise->id)
        ->where('user_id', $provided_to->id)
        ->first();

        if (null === $dtm) {
            return Action::danger('This user is not eligible for this merchandise.');
        }

        if (null !== $dtm->provided_at) {
            return Action::danger('This user already picked up this merchandise.');
        }

        $dtm->provided_at = Carbon::now();
        $dtm->provided_by = $provided_by->id;
        $dtm->save();

        return Action::message('Marked as picked up!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        return [
            Select::make('User', 'provided_to')
                ->options(static function (): array {
                    return User::whereHas('duesTransactions', static function (Builder $query): void {
                        $query->whereHas('merchandise', static function (Builder $query): void {
                            $query->whereHas('fiscalYear', static function (Builder $query): void {
                                $query->where('ending_year', '>=', Carbon::now()->year - 4);
                            })->whereNull('provided_at');
                        })
                        ->whereHas('payment', static function (Builder $query): void {
                            $query->where('amount', '>', 0);
                        });
                    })
                    ->get()
                    ->mapWithKeys(static function (User $user): array {
                        return [strval($user->id) => $user->name];
                    })
                    ->toArray();
                })
                ->searchable()
                ->required()
                ->help(
                    'Note that this dropdown includes users who are not eligible to receive this merchandise or already'
                    .' picked it up. Please pay attention to the result of this action.'
                )
                ->rules('required'),

            Hidden::make('Provided By', 'provided_by')
                ->current_user_id(),
        ];
    }
}
