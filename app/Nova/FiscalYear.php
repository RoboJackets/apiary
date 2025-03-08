<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\FiscalYear as AppModelsFiscalYear;
use App\Nova\Metrics\MembersForOneFiscalYear;
use App\Nova\Metrics\MerchandiseSelections;
use App\Nova\Metrics\PaymentMethodBreakdown;
use App\Nova\Metrics\TotalCollections;
use App\Nova\Metrics\TransactionsByDuesPackage;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * A Nova resource for fiscal years.
 *
 * @extends \App\Nova\Resource<\App\Models\FiscalYear>
 */
class FiscalYear extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = AppModelsFiscalYear::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'ending_year';

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
        'ending_year',
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
    public function fields(NovaRequest $request): array
    {
        return [
            Number::make('Ending Year')
                ->rules('required', 'integer', 'digits:4', 'min:2010', 'max:2030')
                ->creationRules('unique:fiscal_years,ending_year')
                ->updateRules('unique:fiscal_years,ending_year,{{resourceId}}'),

            HasMany::make('Dues Packages', 'packages'),

            HasMany::make('Merchandise', 'merchandise'),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    #[\Override]
    public function actions(NovaRequest $request): array
    {
        return [
            (new Actions\CreateDuesPackages())
                ->canSee(static fn (Request $request): bool => $request->user()->can('create-dues-packages'))
                ->canRun(
                    static fn (NovaRequest $request, AppModelsFiscalYear $fiscalYear): bool => $request->user()->can(
                        'create-dues-packages'
                    )
                )->confirmButtonText('Create Packages'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<\Laravel\Nova\Card>
     */
    #[\Override]
    public function cards(NovaRequest $request): array
    {
        return [
            (new MembersForOneFiscalYear())
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-payments')),
            (new TotalCollections())
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-payments')),
            (new PaymentMethodBreakdown())
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-payments')),
            (new MerchandiseSelections())
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-payments')),
            (new TransactionsByDuesPackage())
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-payments')),
        ];
    }
}
