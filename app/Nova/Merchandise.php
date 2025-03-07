<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\DuesTransactionMerchandise;
use App\Models\Merchandise as AppModelsMerchandise;
use App\Nova\Metrics\MerchandisePickupRate;
use App\Nova\Metrics\ShirtSizeBreakdown;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
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
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = [
        'fiscalYear',
    ];

    /**
     * Get the displayable label of the resource.
     */
    #[\Override]
    public static function label(): string
    {
        return 'Merchandise';
    }

    /**
     * Get the displayable singular label of the resource.
     */
    #[\Override]
    public static function singularLabel(): string
    {
        return 'Merchandise';
    }

    /**
     * Get the fields displayed by the resource.
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Name')
                ->rules('required')
                ->creationRules('unique:merchandise,name')
                ->updateRules('unique:merchandise,name,{{resourceId}}'),

            BelongsTo::make('Fiscal Year', 'fiscalYear'),

            BelongsToMany::make('Dues Packages', 'packages')
                ->fields(static fn (): array => [
                    Select::make('Group')->options([
                        'Fall' => 'Fall',
                        'Spring' => 'Spring',
                    ]),
                ]),

            BelongsToMany::make('Dues Transactions', 'jankForNova')
                ->fields(new MerchandisePivotFields()),

            Boolean::make('Distributable', 'distributable')->default(true),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    #[\Override]
    public function actions(Request $request): array
    {
        $resourceId = $request->resourceId ?? $request->resources;

        if ($resourceId === null) {
            return [];
        }

        if (is_array($resourceId) && count($resourceId) === 1) {
            $resourceId = $resourceId[0];
        }

        $merch_item = AppModelsMerchandise::where('id', $resourceId)->sole();

        if (! $merch_item->distributable) {
            return [];
        }

        return [
            (new Actions\DistributeMerchandise($resourceId))
                ->canSee(static fn (Request $request): bool => $request->user()->can('distribute-swag'))
                ->canRun(
                    static fn (NovaRequest $request, AppModelsMerchandise $merchandise): bool => $request->user()->can(
                        'distribute-swag'
                    )
                )
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the URI key for the resource.
     */
    #[\Override]
    public static function uriKey(): string
    {
        return 'merchandise';
    }

    // This hides the edit button from indexes. This is here to hide the edit button on the merchandise pivot.
    #[\Override]
    public function authorizedToUpdateForSerialization(NovaRequest $request): bool
    {
        return $request->user()->can('update-merchandise') && $request->viaResource !== 'dues-transactions';
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<\Laravel\Nova\Card>
     */
    #[\Override]
    public function cards(NovaRequest $request): array
    {
        $defaults = [
            (new MerchandisePickupRate())->onlyOnDetail(),
        ];

        if ($request->resourceId === null) {
            return [];
        }

        $merch_item = AppModelsMerchandise::where('id', $request->resourceId)->sole();

        if (
            ! $merch_item->distributable &&
            DuesTransactionMerchandise::where('merchandise_id', '=', $merch_item->id)
                ->whereNull('provided_at')
                ->doesntExist()
        ) {
            return [];
        }

        if (Str::contains(Str::lower($merch_item->name), 'shirt')) {
            return [
                (new ShirtSizeBreakdown('shirt'))->onlyOnDetail(),
                ...$defaults,
            ];
        }

        if (Str::contains(Str::lower($merch_item->name), 'polo')) {
            return [
                (new ShirtSizeBreakdown('polo'))->onlyOnDetail(),
                ...$defaults,
            ];
        }

        return $defaults;
    }

    #[\Override]
    public static function searchable(): bool
    {
        return false;
    }
}
