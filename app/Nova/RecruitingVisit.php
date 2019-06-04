<?php declare(strict_types = 1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Nova;

use Laravel\Nova\Panel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;

class RecruitingVisit extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\RecruitingVisit::class;

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = ['user'];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return 'Recruiting Visits';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel(): string
    {
        return 'Recruiting Visit';
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'recruiting_name';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'recruiting_name',
        'recruiting_email',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

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
            Text::make('Name', 'recruiting_name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email', 'recruiting_email')
                ->sortable()
                ->rules('required', 'max:255', 'email'),

            new Panel('Tracking Information', $this->trackingFields()),

            new Panel('Metadata', $this->metaFields()),
        ];
    }

    /**
     * Tracking fields
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function trackingFields(): array
    {
        return [
            Text::make('Visit Token')
                ->onlyOnDetail()
                ->rules('required', 'max:255'),

            BelongsTo::make('User')
                ->searchable(),
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
        return [];
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
