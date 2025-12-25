<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Actions\RevokeOAuth2Token;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * A Nova resource for OAuth tokens.
 *
 * @extends \App\Nova\Resource<\App\Models\OAuth2AccessToken>
 */
class OAuth2AccessToken extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\OAuth2AccessToken>
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
        'client',
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
        return 'Access Tokens';
    }

    /**
     * Get the fields displayed by the resource.
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('User'),

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

    #[\Override]
    public static function searchable(): bool
    {
        return false;
    }

    /**
     * Get the URI key for the resource.
     */
    #[\Override]
    public static function uriKey(): string
    {
        return 'oauth-tokens';
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
            RevokeOAuth2Token::make()
                ->canRun(
                    static function (NovaRequest $r, \App\Models\OAuth2AccessToken $token): bool {
                        if ($token->revoked) {
                            return false;
                        }

                        $user = $r->user();

                        return $user->hasRole('admin') || $token->user_id === $user->id;
                    }
                ),
        ];
    }
}
