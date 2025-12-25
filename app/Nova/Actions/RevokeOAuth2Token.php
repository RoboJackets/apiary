<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class RevokeOAuth2Token extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Revoke Token';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Revoke';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'This action will revoke the selected access token and its associated refresh token.';

    /**
     * Disables action log events for this action.
     *
     * @var bool
     */
    public $withoutActionEvents = true;

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
     * @param  \Illuminate\Support\Collection<int,\App\Models\OAuth2AccessToken>  $models
     */
    public function handle(ActionFields $fields, Collection $models): mixed
    {
        foreach ($models as $token) {
            $token->revoke();
            $token->refreshToken?->revoke();

            $this->markAsFinished($token);
        }

        $count = $models->count();

        return Action::message('Successfully revoked '.($count === 1 ? 'token' : $count.' tokens').'!');
    }
}
