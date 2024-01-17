<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Actions\CreateOAuth2Client;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * A Nova resource for OAuth clients.
 *
 * @extends \App\Nova\Resource<\App\Models\Oauth2Client>
 */
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
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = [
        'user',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return 'Clients';
    }

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            ID::make('Client ID', 'id')
                ->sortable(),

            Text::make('Name', 'name')
                ->sortable()
                ->required()
                ->rules('required'),

            BelongsTo::make('User')
                ->searchable()
                ->help(
                    'This should be null for the personal access client, and otherwise populated with the user '
                    .'responsible for this client.'
                )
                ->nullable(),

            Boolean::make('Revoked', 'revoked')
                ->sortable(),

            Text::make('Redirect URL(s)', 'redirect')
                ->required()
                ->rules('required')
                ->hideFromIndex(),

            Boolean::make('Public (PKCE-Enabled Client)', fn (): bool => $this->secret !== null)
                ->hideFromIndex(),

            HasMany::make('Tokens', 'tokens', OAuth2AccessToken::class),

            self::metadataPanel(),
        ];
    }

    public static function searchable(): bool
    {
        return false;
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
            resolve(CreateOAuth2Client::class)
                ->canSee(static fn (Request $r): bool => $request->user()->hasRole('admin')),
        ];
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'oauth-clients';
    }
}
