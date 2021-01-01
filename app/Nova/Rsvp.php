<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

class Rsvp extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Rsvp::class;

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = ['user'];

    /**
     * Get the displayble label of the resource.
     */
    public static function label(): string
    {
        return 'RSVPs';
    }

    /**
     * Get the displayble singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'RSVP';
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            BelongsTo::make('User'),

            BelongsTo::make('Event'),

            Text::make('Response')
                ->sortable(),

            Text::make('Source')
                ->sortable(),

            new Panel('Detailed Information', $this->detailedFields()),

            self::metadataPanel(),
        ];
    }

    /**
     * Metadata fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function detailedFields(): array
    {
        return [
            Text::make('User Agent')
                ->hideFromIndex(),

            Text::make('IP Address')
                ->hideFromIndex(),

            Text::make('Token')
                ->hideFromIndex(),
        ];
    }
}
