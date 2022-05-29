<?php

declare(strict_types=1);

namespace App\Nova;

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

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
     * Get the displayble singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'Client';
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

            Boolean::make('Public (PKCE-Enabled Client)', function (): bool {
                return null !== $this->secret;
            })
                ->hideFromIndex(),

            HasMany::make('Tokens', 'tokens', OAuth2AccessToken::class),

            self::metadataPanel(),
        ];
    }
}
