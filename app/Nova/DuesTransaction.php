<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\DuesTransaction as AppModelsDuesTransaction;
use App\Nova\Actions\Payments\RecordPaymentActions;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * A Nova resource for dues transactions.
 *
 * @extends \App\Nova\Resource<\App\Models\DuesTransaction>
 */
class DuesTransaction extends Resource
{
    use RecordPaymentActions;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = AppModelsDuesTransaction::class;

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = [
        'payment',
        'package',
        'user',
    ];

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return 'Dues Transactions';
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'Dues Transaction';
    }

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Dues';

    /**
     * The number of results to display in the global search.
     *
     * @var int
     */
    public static $globalSearchResults = 2;

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Heading::make(
                '<strong>In general, dues transactions should not be created manually.</strong> '.
                'Dues transactions are created as part of the dues workflow in the member-facing UI.'
            )
                ->asHtml()
                ->showOnCreating(true)
                ->showOnUpdating(false)
                ->showOnDetail(false),

            ID::make(),

            BelongsTo::make('Paid By', 'user', User::class)
                ->searchable()
                ->rules('required', 'unique:dues_transactions,user_id,NULL,id,dues_package_id,'.$request->package)
                ->withoutTrashed(),

            BelongsTo::make('Dues Package', 'package', DuesPackage::class)
                ->rules('required', 'unique:dues_transactions,dues_package_id,NULL,id,user_id,'.$request->user)
                ->withoutTrashed(),

            Text::make('Status')
                ->resolveUsing(static fn (string $str): string => ucfirst($str))
                ->exceptOnForms(),

            Currency::make('Payment Due', function (): ?float {
                // @phan-suppress-next-line PhanPluginNonBoolBranch
                if ($this->is_paid) {
                    return null;
                }

                if ($this->package === null) {
                    return null;
                }

                if (! $this->package->is_active) {
                    return null;
                }

                return $this->package->cost;
            })
                ->onlyOnDetail(),

            BelongsToMany::make('Merchandise', 'jankForNova')
                ->fields(new MerchandisePivotFields()),

            MorphMany::make('Payments', 'payment', Payment::class)
                ->onlyOnDetail(),

            self::metadataPanel(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return $request->user()->can('read-teams-membership') ? [
            new Filters\DuesTransactionTeam(),
            new Filters\DuesTransactionPaymentStatus(),
        ] : [
            new Filters\DuesTransactionPaymentStatus(),
        ];
    }

    /**
     * Hide the edit button from indexes, in particular on merchandise pivots.
     */
    public function authorizedToUpdateForSerialization(NovaRequest $request): bool
    {
        return false;
    }

    /**
     * Get the search result subtitle for the resource.
     */
    public function subtitle(): ?string
    {
        return $this->user->full_name.' | '.$this->package->name.' | '.ucfirst($this->status);
    }
}
