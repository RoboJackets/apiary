<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\DuesTransaction;
use App\Models\DuesTransactionMerchandise;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class UndoMerchandiseDistribution extends Action
{
    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Clear Pick-Up';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Select the member to clear merchandise distribution. Only members that picked up this item '.
        'are shown in the list.';

    /**
     * The metadata for the element.
     *
     * @var array<string, bool>
     */
    public $meta = [
        'destructive' => true,
    ];

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Merchandise>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $merchandise = $models->sole();
        $provided_to = User::where('id', $fields->member)->sole();

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

        if ($dtm === null) {
            return Action::danger('This user is not eligible for this merchandise.');
        }

        if ($dtm->provided_at === null) {
            return Action::danger('This user did not pick up this merchandise.');
        }

        $dtm->provided_at = null;
        $dtm->provided_by = null;
        $dtm->provided_via = null;
        $dtm->save();

        return Action::message('Cleared pick-up!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            Select::make('Member', 'member')
                ->options(
                    fn (): array => User::whereHas(
                        'duesTransactions',
                        function (Builder $query): void {
                            $query->whereHas(
                                'merchandise',
                                function (Builder $query): void {
                                    $query->when(
                                        $this->resource !== null,
                                        function (Builder $query): void {
                                            $query->where('merchandise.id', $this->resource?->id);
                                        }
                                    )
                                        ->whereNotNull('dues_transaction_merchandise.provided_at');
                                }
                            )
                                ->whereHas('payment', static function (Builder $query): void {
                                    $query->where('amount', '>', 0);
                                });
                        }
                    )
                        ->get()
                        ->mapWithKeys(static fn (User $user): array => [strval($user->id) => $user->name])
                        ->sort()
                        ->toArray()
                )
                ->required()
                ->searchable()
                ->rules('required'),
        ];
    }
}
