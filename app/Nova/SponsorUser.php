<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\SponsorUser as AppModelsSponsorUser;
use App\Rules\SponsorUserValidEmail;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

/**
 * A Nova resource for sponsor users.
 *
 * @extends \App\Nova\Resource<\App\Models\SponsorUser>
 */
class SponsorUser extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\SponsorUser>
     */
    public static $model = AppModelsSponsorUser::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'email';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Other';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'id',
        'name',
    ];

    /**
     * The number of results to display in the global search.
     *
     * @var int
     */
    public static $globalSearchResults = 5;

    /**
     * The number of results to display when searching the resource using Scout.
     *
     * @var int
     */
    public static $scoutSearchResults = 5;

    /**
     * Get the fields displayed by the resource.
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            BelongsTo::make('Sponsor', 'company', Sponsor::class)
                ->withoutTrashed()
                ->rules('required')
                ->sortable(),
            Text::make('Email', 'email')
                ->rules('required', 'email:rfc,strict,dns,spoof', 'max:255', new SponsorUserValidEmail())
                ->creationRules('unique:sponsor_users,email')
                ->updateRules('unique:sponsor_users,email,{{resourceId}}')
                ->sortable(),
        ];
    }
}
