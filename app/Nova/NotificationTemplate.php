<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

class NotificationTemplate extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\NotificationTemplate::class;

    /**
     * Get the displayble label of the resource.
     */
    public static function label(): string
    {
        return 'Notification Templates';
    }

    /**
     * Get the displayble singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'Notification Template';
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
        'subject',
        'body_markdown',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Name')->rules('required', 'max:255')
                ->sortable(),

            new Panel('Email Content', $this->basicFields()),

            new Panel('Metadata', $this->metaFields()),
        ];
    }

    /**
     * Notification fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function basicFields(): array
    {
        return [
            Text::make('Subject')
                ->rules('required', 'max:255')
                ->sortable(),

            Markdown::make('Body', 'body_markdown')
                ->rules('required')
                ->hideFromIndex(),
        ];
    }

    /**
     * Timestamp and creator fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function metaFields(): array
    {
        return [

            BelongsTo::make('User', 'creator')
                ->searchable()
                ->rules('required')
                ->hideFromIndex()
                ->hideWhenUpdating(),

            DateTime::make('Created At')
                ->onlyOnDetail(),

            DateTime::make('Last Updated', 'updated_at')
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the cards available for the request.
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
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(Request $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
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
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [];
    }
}
