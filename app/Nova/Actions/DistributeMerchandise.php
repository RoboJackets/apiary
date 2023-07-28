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
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class DistributeMerchandise extends Action
{
    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Mark as Picked Up';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Select the member to whom you are distributing merchandise. Only members that are '.
        'currently eligible to pick up this item are shown in the list.';

    /**
     * The Merchandise model that will be distributed. This is used to build the list of users.
     */
    private readonly ?Merchandise $resource;

    public function __construct(?string $resourceId)
    {
        if ($resourceId === null) {
            $this->resource = null;

            return;
        }

        $this->resource = Merchandise::where('id', '=', $resourceId)->sole();
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Merchandise>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $merchandise = $models->sole();
        $provided_by = Auth::user();
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
     */
    public function fields(NovaRequest $request): array
    {
        $resource = $this->resource;

        return [
            Select::make('Member', 'member')
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
        ];
    }
}
