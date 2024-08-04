<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

/**
 * A Nova resource for access tokens.
 *
 * @extends \App\Nova\Resource<\App\Models\OAuth2AccessToken>
 */
class OAuth2AccessToken extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\OAuth2AccessToken::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'OAuth2';

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
        'user',
        'client',
    ];

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return 'Access Tokens';
    }

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            ID::make('ID', 'id')
                ->sortable(),

            BelongsTo::make('User')
                ->searchable(),

            BelongsTo::make('Client', 'client', OAuth2Client::class),

            Text::make('Name'),

            Boolean::make('Revoked'),

            new Panel(
                'Metadata',
                [
                    DateTime::make('Created', 'created_at')
                        ->onlyOnDetail(),

                    DateTime::make('Last Updated', 'updated_at')
                        ->onlyOnDetail(),

                    DateTime::make('Expires', 'expires_at')
                        ->onlyOnDetail(),
                ]
            ),
        ];
    }

    public static function searchable(): bool
    {
        return false;
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'oauth-tokens';
    }
}
