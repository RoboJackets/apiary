<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\Merchandise as AppModelsMerchandise;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lynndigital\SelectOrCustom\SelectOrCustom;

class Merchandise extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = AppModelsMerchandise::class;

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
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the displayble label of the resource.
     */
    public static function label(): string
    {
        return 'Merchandise';
    }

    /**
     * Get the displayble singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'Merchandise';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Name')
                ->rules('required')
                ->creationRules('unique:merchandise,name')
                ->updateRules('unique:merchandise,name,{{resourceId}}'),

            BelongsTo::make('Fiscal Year', 'fiscalYear'),

            BelongsToMany::make('Dues Packages', 'packages')
                ->fields(static function (): array {
                    return [
                        SelectOrCustom::make('Group')->options([
                            'Fall' => 'Fall',
                            'Spring' => 'Spring',
                        ]),
                    ];
                }),

            BelongsToMany::make('Dues Transactions', 'transactions')
                ->fields(static function (): array {
                    return [
                        DateTime::make('Provided At'),

                        // I tried a BelongsTo but it appeared to be looking for the relationship on the model itself,
                        // not the pivot model. This is a temporary fallback.
                        Text::make('Provided By', 'provided_by_name'),
                    ];
                }),
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
            (new Actions\DistributeMerchandise($request->resourceId))
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('distribute-swag');
                })
                ->canRun(static function (Request $request, AppModelsMerchandise $merchandise): bool {
                    return $request->user()->can('distribute-swag');
                })->confirmButtonText('Mark as Picked Up')
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'merchandise';
    }

    // This hides the edit button from indexes. This is here to hide the edit button on the merchandise pivot.
    public function authorizedToUpdateForSerialization(NovaRequest $request): bool
    {
        return $request->user()->can('update-merchandise');
    }
}
