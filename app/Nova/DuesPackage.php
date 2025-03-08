<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\DuesTransaction as AppModelsDuesTransaction;
use App\Models\Payment;
use App\Nova\Metrics\MerchandiseSelections;
use App\Nova\Metrics\PaymentMethodBreakdown;
use App\Nova\Metrics\TotalCollections;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * A Nova resource for dues packages.
 *
 * @extends \App\Nova\Resource<\App\Models\DuesPackage>
 */
class DuesPackage extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\DuesPackage::class;

    /**
     * Get the displayable label of the resource.
     */
    #[\Override]
    public static function label(): string
    {
        return 'Dues Packages';
    }

    /**
     * Get the displayable singular label of the resource.
     */
    #[\Override]
    public static function singularLabel(): string
    {
        return 'Dues Package';
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Dues';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'name',
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = [
        'fiscalYear',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the fields displayed by the resource.
     */
    #[\Override]
    public function fields(Request $request): array
    {
        return [
            Heading::make(
                '<strong>In general, dues packages should not be created manually.</strong> '.
                'Use the <strong>Create Dues Packages</strong> action on a Fiscal Year to create default packages, '.
                'then update as needed.'
            )
                ->asHtml()
                ->showOnCreating(true)
                ->showOnUpdating(false)
                ->showOnDetail(false),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->creationRules('unique:dues_packages,name')
                ->updateRules('unique:dues_packages,name,{{resourceId}}'),

            BelongsTo::make('Fiscal Year', 'fiscalYear', FiscalYear::class)
                ->sortable(),

            Number::make('Paid Transactions', fn (): int => DB::table('dues_transactions')
                ->selectRaw('count(distinct dues_transactions.id) as count')
                ->leftJoin('payments', static function (JoinClause $join): void {
                    $join->on('dues_transactions.id', '=', 'payable_id')
                        ->where('payments.payable_type', AppModelsDuesTransaction::getMorphClassStatic())
                        ->where('payments.amount', '>', 0);
                })
                ->whereNotNull('payments.id')
                ->whereNull('payments.deleted_at')
                ->whereNull('dues_transactions.deleted_at')
                ->where('dues_package_id', $this->id)->get()[0]->count)
                ->onlyOnIndex(),

            Boolean::make('Active', 'is_active')
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            DateTime::make('Membership Start Date', 'effective_start')
                ->help('This is the date when someone who paid for this package will be considered a member.')
                ->hideFromIndex()
                ->rules('required', 'date', 'before:effective_end'),

            DateTime::make('Membership End Date', 'effective_end')
                ->help(
                    'This is the date when someone who paid for this package will no longer be considered a member.'
                    .' They will be prompted to pay dues again if a new package is available to purchase at that time.'
                )
                ->hideFromIndex()
                ->rules('required', 'date', 'after:effective_start'),

            Currency::make('Cost')
                ->sortable()
                ->rules('required', 'integer'),

            Currency::make(
                'Square Processing Fee',
                static fn (
                    \App\Models\DuesPackage $package
                ): float => Payment::calculateProcessingFee(intval($package->cost * 100)) / 100
            )
                ->onlyOnDetail(),

            Boolean::make('Available for Purchase')
                ->sortable(),

            Boolean::make('Restricted to Students')
                ->sortable(),

            BelongsTo::make('Cannot Be Purchased After', 'conflictsWith', self::class)
                ->withoutTrashed()
                ->nullable()
                ->hideFromIndex(),

            HasMany::make('Prevents Purchase Of', 'hasConflictWith', self::class),

            BelongsToMany::make('Merchandise')
                ->fields(static fn (): array => [
                    Select::make('Group')->options([
                        'Fall' => 'Fall',
                        'Spring' => 'Spring',
                    ]),
                ]),

            new Panel('Access', [
                Boolean::make('Active', 'is_access_active')
                    ->onlyOnDetail(),

                DateTime::make('Access Start Date', 'access_start')
                    ->help(
                        'This is the date when someone who paid for this package will have access to RoboJackets '
                        .'systems.'
                    )
                    ->hideFromIndex()
                    ->rules('required', 'date', 'before_or_equal:effective_start', 'before:access_end'),

                DateTime::make('Access End Date', 'access_end')
                    ->help(
                        'This is the date when someone who paid for this package will lose access to RoboJackets '
                        .'systems, unless they pay for a different package or get an override. This is typically around'
                        .' 1 to 2 months later than the "End Date", and should align with the following dues deadline.'
                    )
                    ->hideFromIndex()
                    ->rules('required', 'date', 'after:effective_end', 'after:access_start'),
            ]),

            HasMany::make('Dues Transactions', 'duesTransactions', DuesTransaction::class)
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-dues-transactions')),

            self::metadataPanel(),
        ];
    }

    /**
     * Only show packages available for purchase for relatable queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\DuesPackage>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\DuesPackage>
     */
    #[\Override]
    public static function relatableQuery(NovaRequest $request, $query): Builder
    {
        if ($request->is('nova-api/dues-transactions/*')) {
            return $query->availableForPurchase();
        }

        return $query;
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<\Laravel\Nova\Card>
     */
    #[\Override]
    public function cards(Request $request): array
    {
        return [
            (new TotalCollections())
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-payments')),
            (new PaymentMethodBreakdown())
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-payments')),
            (new MerchandiseSelections())
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-payments')),
        ];
    }

    /**
     * Handle any post-validation processing.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    #[\Override]
    protected static function afterValidation(NovaRequest $request, $validator): void
    {
        if ($request->resourceId !== null && $request->resourceId === $request->conflictsWith) {
            $validator->errors()->add('conflictsWith', 'Packages can\'t be configured to conflict with themselves');
        }
    }
}
