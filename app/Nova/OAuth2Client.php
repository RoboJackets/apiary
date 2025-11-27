<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Actions\CreateOAuth2AuthorizationCodeGrantClient;
use App\Nova\Actions\CreateOAuth2ClientCredentialsGrantClient;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * A Nova resource for OAuth clients.
 *
 * @extends \App\Nova\Resource<\App\Models\OAuth2Client>
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
    public static $group = 'OAuth';

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
    #[\Override]
    public static function label(): string
    {
        return 'Clients';
    }

    /**
     * Get the fields displayed by the resource.
     */
    #[\Override]
    public function fields(Request $request): array
    {
        return [
            ID::make('Client ID', 'id')
                ->sortable(),

            Text::make('Name', 'name')
                ->sortable()
                ->required()
                ->rules('required')
                ->creationRules('unique:oauth_clients,name')
                ->updateRules('unique:oauth_clients,name,{{resourceId}}'),

            MorphTo::make('Owner')
                ->types([User::class])
                ->searchable()
                ->withoutTrashed()
                ->nullable(),

            Boolean::make('Revoked', 'revoked')
                ->sortable(),

            Code::make('Redirect URIs', 'redirect_uris')
                ->json()
                ->required()
                ->rules('required')
                ->hideFromIndex(),

            Code::make('Grant Types', 'grant_types')
                ->json()
                ->required()
                ->rules('required')
                ->hideFromIndex(),

            Boolean::make('Public (PKCE-Enabled Client)', fn (): bool => $this->secret === null)
                ->hideFromIndex(),

            HasMany::make('Tokens', 'tokens', OAuth2AccessToken::class),

            MorphToMany::make('Permissions', 'permissions', \Vyuldashev\NovaPermission\Permission::class)
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->searchable()
                ->showOnDetail(
                    static fn (
                        NovaRequest $request,
                        \App\Models\OAuth2Client $client
                    ): bool => in_array('client_credentials', $client->grant_types, true)
                ),

            self::metadataPanel(),
        ];
    }

    #[\Override]
    public static function searchable(): bool
    {
        return false;
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    #[\Override]
    public function actions(NovaRequest $request): array
    {
        return [
            resolve(CreateOAuth2AuthorizationCodeGrantClient::class)
                ->canSee(static fn (Request $r): bool => $request->user()->hasRole('admin')),

            resolve(CreateOAuth2ClientCredentialsGrantClient::class)
                ->canSee(static fn (Request $r): bool => $request->user()->hasRole('admin')),
        ];
    }

    /**
     * Get the URI key for the resource.
     */
    #[\Override]
    public static function uriKey(): string
    {
        return 'oauth-clients';
    }
}
