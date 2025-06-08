<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class CreatePersonalAccessToken extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Create Token';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Enter a name to identify this token. It will be visible to the user.';

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $models
     *
     * @phan-suppress PhanTypeMismatchArgument
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if (config('passport.personal_access_client.id') === null
            || config('passport.personal_access_client.secret') === null) {
            return Action::danger(
                'Passport personal access client ID and/or secret environment variables not set. '.
                'Make sure they are set and try again.'
            );
        }

        return Action::modal(
            'personal-access-token-modal',
            [
                'token' => $models->sole()->createToken($fields->name)->accessToken,
            ]
        )->withMessage('The personal access token was created successfully!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Name')
                ->rules('required'),
        ];
    }
}
