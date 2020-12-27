<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\FiscalYear as AppModelsFiscalYear;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Number;

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
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'ending_year',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(Request $request): array
    {
        return [
            Number::make('Ending Year')
                ->rules('required', 'integer', 'digits:4', 'min:2010', 'max:2030')
                ->creationRules('unique:fiscal_years,ending_year')
                ->updateRules('unique:fiscal_years,ending_year,{{resourceId}}'),

            HasMany::make('Dues Packages', 'packages'),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [
            (new Actions\CreateDuesPackages())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('create-dues-packages');
                })
                ->canRun(static function (Request $request, AppModelsFiscalYear $fiscalYear): bool {
                    return $request->user()->can('create-dues-packages');
                }),
        ];
    }
}
