<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Passport\Token;

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
    public $confirmText = 'This action will revoke all OAuth2 access, refresh, and personal '.
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
        foreach ($models as $user) {
            $user->tokens()->each(static function (Token $token) {
                $token->revoke();
                $token->refreshToken?->revoke();
            });

            $this->markAsFinished($user);
        }

        return Action::message('Successfully revoked all tokens!');
    }
}
