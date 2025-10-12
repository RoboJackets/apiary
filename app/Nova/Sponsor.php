<?php 
use App\Models\Sponsor as AppModelsSponsor;
use Laravel\Nova\Resource;

class Sponsor extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Sponsor>
     */
    public static $model = AppModelsSponsor::class;
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
            ID::make()->sortable(),
            Text::make('Name')
                ->rules('required', 'max:255')
                ->sortable(),
            DateTime::make('End Date')
                ->rules('required')
                ->sortable(),
            HasMany::make('Domain Names', 'domainNames', SponsorDomain::class),
            Boolean::make('Active', function () {
                return $this->active();
            })->onlyOnIndex(),
        ];
    }
}
