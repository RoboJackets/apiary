<?php

declare(strict_types=1);

namespace App\Nova;

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class OAuth2Client extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\OAuth2Client::class;

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
        'id',
        'name',
    ];

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return 'OAuth2 Clients';
    }

    /**
     * Get the displayble singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'OAuth2 Client';
    }

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(Request $request): array
    {
        return [
            ID::make('Client ID', 'id')->sortable(),
            Text::make('Name', 'name')->sortable(),
            Boolean::make('Revoked', 'revoked')->sortable(),
            Text::make('Redirect URL(s)', 'redirect')->hideFromIndex(),
            Boolean::make('Public (PKCE-Enabled Client)', function (): bool {
                return null !== $this->secret;
            })->hideFromIndex(),
            Text::make('Hashed Secret', 'secret')->hideFromIndex()->readonly()
                ->help(
                    'This is a hashed version of the client secret. The plaintext client secret is only '.
                    'available immediately after creation.'
                ),
            DateTime::make('Created At', 'created_at')->hideFromIndex()->readonly(),
            DateTime::make('Updated At', 'updated_at')->hideFromIndex()->readonly(),
        ];
    }
}
