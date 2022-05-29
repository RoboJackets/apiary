<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

/**
 * A Nova resource for notification templates.
 *
 * @extends \App\Nova\Resource<\App\Models\NotificationTemplate>
 */
class NotificationTemplate extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\NotificationTemplate::class;

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
        'name',
        'subject',
        'body_markdown',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Name')->rules('required', 'max:255')
                ->sortable(),

            new Panel('Email Content', $this->basicFields()),

            self::metadataPanel(),
        ];
    }

    /**
     * Notification fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     *
     * @suppress PhanTypeInvalidCallableArraySize
     */
    protected function basicFields(): array
    {
        return [
            Text::make('From')
                ->rules('max:255', 'regex:/^[-a-zA-Z0-9 ]+$/')
                ->suggestions(['RoboJackets'])
                ->sortable(),

            Text::make('Subject')
                ->rules('required', 'max:255')
                ->sortable(),

            Markdown::make('Body', 'body_markdown')
                ->rules('required')
                ->hideFromIndex(),
        ];
    }
}
