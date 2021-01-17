<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\DuesTransaction as AppModelsDuesTransaction;
use App\Nova\Traits\DuesPackageCards;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;
use Lynndigital\SelectOrCustom\SelectOrCustom;

/**
 * A Nova resource for dues packages.
 *
 * @property int $id
 */
class DuesPackage extends Resource
{
    use DuesPackageCards;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\DuesPackage::class;

    /**
     * Get the displayble label of the resource.
     */
    public static function label(): string
    {
        return 'Dues Packages';
    }

    /**
     * Get the displayble singular label of the resource.
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
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->creationRules('unique:dues_packages,name')
                ->updateRules('unique:dues_packages,name,{{resourceId}}'),

            BelongsTo::make('Fiscal Year', 'fiscalYear', FiscalYear::class)
                ->sortable(),

            Number::make('Paid Transactions', function (): int {
                return AppModelsDuesTransaction::leftJoin('payments', static function (JoinClause $join): void {
                    $join->on('dues_transactions.id', '=', 'payable_id')
                         ->where('payments.payable_type', AppModelsDuesTransaction::getMorphClassStatic())
                         ->where('payments.amount', '>', 0);
                })
                ->whereNotNull('payments.id')
                ->where('dues_package_id', $this->id)->count();
            })->onlyOnIndex(),

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
                ->rules('required'),

            Boolean::make('Available for Purchase')
                ->sortable(),

            Boolean::make('Restricted to Students')
                ->sortable(),

            BelongsTo::make('Cannot Be Purchased After', 'conflictsWith', self::class)
                ->nullable()
                ->hideFromIndex(),

            HasMany::make('Prevents Purchase Of', 'hasConflictWith', self::class),

            BelongsToMany::make('Merch')
                ->fields(static function (): array {
                    return [
                        SelectOrCustom::make('Group')->options([
                            'Fall' => 'Fall',
                            'Spring' => 'Spring',
                        ]),
                    ];
                }),

            new Panel('Swag', $this->swagFields()), // FIXME remove this

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

            self::metadataPanel(),
        ];
    }

    /**
     * Swag information.
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
}
