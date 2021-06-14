<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;

class RevokeOAuth2Tokens extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Revoke All OAuth2 Tokens';

    public function __construct()
    {
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        foreach ($models as $user) {
            $user_access_tokens = $user->tokens()
                ->whereRevoked(false)
                ->whereHas('client', function ($clientQuery) use ($fields) {
                    if (! $fields->include_personal_access_tokens) {
                        $clientQuery->whereNotNull('user_id'); // PATs are created with a Personal Access Client that isn't associated with any user
                    }
                })
                ->get();

            foreach ($user_access_tokens as $access_token) {
                $tokenRepository->revokeAccessToken($access_token->id);
                $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($access_token->id);
            }
        }

        return Action::message('Successfully deleted OAuth2 tokens');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Heading::make('This action will revoke all OAuth2 access, refresh, and optionally, personal'.
                ' access tokens associated with this user.'),
            Boolean::make('Include Personal Access Tokens')
                ->help('Check this box if you\'d like to revoke all of this user\'s personal access tokens as well.'),
        ];
    }
}
