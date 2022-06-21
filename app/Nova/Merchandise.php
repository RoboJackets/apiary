<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\Merchandise as AppModelsMerchandise;
use App\Nova\Metrics\MerchandisePickupRate;
use App\Nova\Metrics\ShirtSizeBreakdown;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * A Nova resource for merchandise (shirts, polos, and whatever else the PR chair comes up with).
 *
 * @extends \App\Nova\Resource<\App\Models\Merchandise>
 */
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
    public function fields(NovaRequest $request): array
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
                        Select::make('Group')->options([
                            'Fall' => 'Fall',
                            'Spring' => 'Spring',
                        ]),
                    ];
                }),

            BelongsToMany::make('Dues Transactions', 'jankForNova')
                ->fields(new MerchandisePivotFields()),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        if (null === $request->resourceId) {
            return [];
        }

        $name = Str::lower(AppModelsMerchandise::where('id', $request->resourceId)->sole()->name);

        if (Str::contains($name, 'waive')) {
            return [];
        }

        return [
            (new Actions\DistributeMerchandise($request->resourceId))
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('distribute-swag');
                })
                ->canRun(static function (NovaRequest $request, AppModelsMerchandise $merchandise): bool {
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
        return $request->user()->can('update-merchandise') && 'dues-transactions' !== $request->viaResource;
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        $defaults = [
            (new MerchandisePickupRate())->onlyOnDetail(),
        ];

        if (null === $request->resourceId) {
            return [];
        }

        $name = Str::lower(AppModelsMerchandise::where('id', $request->resourceId)->sole()->name);

        if (Str::contains($name, 'waive')) {
            return [];
        }

        if (Str::contains($name, 'shirt')) {
            return [
                (new ShirtSizeBreakdown('shirt'))->onlyOnDetail(),
                ...$defaults,
            ];
        }

        if (Str::contains($name, 'polo')) {
            return [
                (new ShirtSizeBreakdown('polo'))->onlyOnDetail(),
                ...$defaults,
            ];
        }

        return $defaults;
    }

    /**
     * Not really useful on detail pages.
     */
    public static function searchable(): bool
    {
        return false;
    }
}
