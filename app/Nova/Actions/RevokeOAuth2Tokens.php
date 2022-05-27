<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;

class RevokeOAuth2Tokens extends DestructiveAction
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Revoke All OAuth2 Tokens';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $models
     * @return array<string, string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        foreach ($models as $user) {
            $user_access_tokens = $user->tokens()
                ->whereRevoked(false)
                ->whereHas('client', static function ($clientQuery) use ($fields) {
                    // @phan-suppress-next-line PhanPluginNonBoolBranch
                    if ($fields->include_personal_access_tokens) {
                        return $clientQuery;
                    }

                    return $clientQuery->whereNotNull('user_id'); // PATs are created with a Personal Access Client that
                    // isn't associated with any user
                })
                ->get();

            foreach ($user_access_tokens as $access_token) {
                $tokenRepository->revokeAccessToken($access_token->id);
                $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($access_token->id);
            }
        }

        return DestructiveAction::message('Successfully deleted OAuth2 tokens');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields()
    {
        return [
            // phpcs:disable
            Heading::make('This action will revoke all OAuth2 access, refresh, and optionally, personal '.
                'access tokens associated with this user.'),
            Boolean::make('Include Personal Access Tokens')
                ->help('Check this box if you\'d like to revoke all of this user\'s personal access tokens as well.'),
        ];
    }
}
