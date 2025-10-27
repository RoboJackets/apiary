<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\SponsorDomain as AppModelsSponsorDomain;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

/**
 * A Nova resource for sponsor domains.
 *
 * @extends \App\Nova\Resource<\App\Models\SponsorDomain>
 */
class SponsorDomain extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\SponsorDomain>
     */
    public static $model = AppModelsSponsorDomain::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'domain_name';

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
            BelongsTo::make('Sponsor', 'sponsor', Sponsor::class)
                ->rules('required')
                ->sortable(),
            Text::make('Domain Name', 'domain_name')
                ->rules('required', 'max:255')
                ->sortable(),
        ];
    }
}
