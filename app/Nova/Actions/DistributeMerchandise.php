<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\DuesTransaction;
use App\Models\DuesTransactionMerchandise;
use App\Models\Merchandise;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class DistributeMerchandise extends Action
{
    /**
     * The Merchandise model that will be distributed. This is used to build the list of users.
     *
     * @var ?\App\Models\Merchandise
     */
    private $resource;

    public function __construct(?string $resourceId)
    {
        if ($resourceId === null) {
            return;
        }

        $this->resource = Merchandise::where('id', '=', $resourceId)->sole();
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Merchandise>  $models
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        if (count($models) !== 1) {
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

        if ($dtm === null) {
            return Action::danger('This user is not eligible for this merchandise.');
        }

        if ($dtm->provided_at !== null) {
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
     *
     * @phan-suppress PhanTypeInvalidCallableArraySize
     */
    public function fields(NovaRequest $request): array
    {
        $resource = $this->resource;

        return [
            Select::make('Distributed To', 'provided_to')
                ->options(
                    static fn (): array => User::whereHas(
                        'duesTransactions',
                        static function (Builder $query) use ($resource): void {
                            $query->whereHas(
                                'merchandise',
                                static function (Builder $query) use ($resource): void {
                                    $query->when(
                                        $resource !== null,
                                        static function (Builder $query) use ($resource): void {
                                            $query->where('merchandise.id', $resource->id);
                                        }
                                    )
                                        ->whereNull('dues_transaction_merchandise.provided_at');
                                }
                            )
                                ->whereHas('payment', static function (Builder $query): void {
                                    $query->where('amount', '>', 0);
                                });
                        }
                    )
                        ->get()
                        ->mapWithKeys(static fn (User $user): array => [strval($user->id) => $user->name])
                        ->toArray()
                )
                ->searchable()
                ->required()
                ->rules('required'),

            Select::make('Distributed By', 'provided_by')
                ->options([strval($request->user()->id) => $request->user()->name])
                ->default(strval($request->user()->id))
                ->required()
                ->rules('required')
                // If we use `readonly()`, then this field's value isn't passed to the action
                ->withMeta(['extraAttributes' => ['readonly' => true]]),
        ];
    }
}
