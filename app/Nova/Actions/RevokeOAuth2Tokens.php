<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;

class RevokeOAuth2Tokens extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Revoke All Tokens';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Revoke All Tokens';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'This action will revoke all OAuth2 access, refresh, and optionally, personal '.
        'access tokens associated with this user.';

    /**
     * The metadata for the element.
     *
     * @var array<string, bool>
     */
    public $meta = [
        'destructive' => true,
    ];

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        foreach ($models as $user) {
            $user_access_tokens = $user->tokens()
                ->whereRevoked(false)
                ->whereHas('client', static function (Builder $clientQuery) use ($fields): void {
                    if ($fields->include_personal_access_tokens === true) {
                        return;
                    }

                    // PATs are created with a Personal Access Client that
                    // isn't associated with any user
                    $clientQuery->whereNotNull('user_id');
                })
                ->get();

            foreach ($user_access_tokens as $access_token) {
                $tokenRepository->revokeAccessToken($access_token->id);
                $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($access_token->id);
            }

            $this->markAsFinished($user);
        }

        return Action::message('Successfully revoked all tokens!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Boolean::make('Revoke Personal Access Tokens')
                ->help('Check this box if you\'d like to revoke all of this user\'s personal access tokens.'),
        ];
    }
}
