<?php declare(strict_types = 1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Nova;

use Laravel\Nova\Panel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use App\Nova\Metrics\SwagPickupRate;
use App\Nova\Metrics\TotalCollections;
use App\Nova\Metrics\ShirtSizeBreakdown;
use App\Nova\Metrics\PaymentMethodBreakdown;

class DuesPackage extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\DuesPackage';

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return 'Dues Packages';
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
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
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<mixed>
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Boolean::make('Active', 'is_active')
                ->sortable()
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            DateTime::make('Start Date', 'effective_start')
                ->hideFromIndex()
                ->rules('required'),

            DateTime::make('End Date', 'effective_end')
                ->hideFromIndex()
                ->rules('required'),

            Currency::make('Cost')
                ->sortable()
                ->format('%.2n')
                ->rules('required'),

            Boolean::make('Available for Purchase')
                ->sortable(),

            new Panel('Swag', $this->swagFields()),

            new Panel('Access', [
                Boolean::make('Active', 'is_access_active')
                    ->onlyOnDetail(),

                DateTime::make('Start Date', 'access_start')
                    ->onlyOnDetail(),

                DateTime::make('End Date', 'access_end')
                    ->onlyOnDetail(),

                DateTime::make('Access Start Date', 'access_start')
                    ->onlyOnForms()
                    ->rules('required'),

                DateTime::make('Access End Date', 'access_end')
                    ->onlyOnForms()
                    ->rules('required'),
            ]),

            HasMany::make('Dues Transactions', 'duesTransactions', DuesTransaction::class)
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-dues-transactions');
                }),

            new Panel('Metadata', $this->metaFields()),
        ];
    }

    /**
     * Swag information
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function swagFields(): array
    {
        return [
            Boolean::make('Eligible for T-Shirt', 'eligible_for_shirt')
                ->hideFromIndex(),

            Boolean::make('Eligible for Polo')
                ->hideFromIndex(),
        ];
    }

    /**
     * Timestamp fields
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function metaFields(): array
    {
        return [
            DateTime::make('Created', 'created_at')
                ->onlyOnDetail(),

            DateTime::make('Last Updated', 'updated_at')
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(Request $request): array
    {
        return [
            (new TotalCollections())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                        return $request->user()->can('read-payments');
                }),
            (new PaymentMethodBreakdown())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                        return $request->user()->can('read-payments');
                }),
            (new SwagPickupRate('shirt'))
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                        return $request->user()->can('read-dues-transactions');
                }),
            (new SwagPickupRate('polo'))
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                        return $request->user()->can('read-dues-transactions');
                }),
            (new ShirtSizeBreakdown('shirt'))
                ->canSee(static function (Request $request): bool {
                        return $request->user()->can('read-dues-transactions');
                }),
            (new ShirtSizeBreakdown('polo'))
                ->canSee(static function (Request $request): bool {
                        return $request->user()->can('read-dues-transactions');
                }),
            (new ShirtSizeBreakdown('shirt'))
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                        return $request->user()->can('read-dues-transactions');
                }),
            (new ShirtSizeBreakdown('polo'))
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                        return $request->user()->can('read-dues-transactions');
                }),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(Request $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Lenses\Lens>
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [];
    }
}
