<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class CreatePersonalAccessToken extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $models
     * @return array<string, string>
     *
     * @phan-suppress PhanNonClassMethodCall,PhanTypeExpectedObjectPropAccess
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        if (count($models) > 1) {
            return Action::danger('This action can only be run on one model at a time.');
        }

        if (config('passport.personal_access_client.id') === null
            || config('passport.personal_access_client.secret') === null) {
            return Action::danger(
                'Passport personal access client ID and/or secret environment variables not set. '.
                'Make sure they are set and try again.'
            );
        }

        $user = $models[0];

        $token = $user->createToken($fields->name)->accessToken;

        Session::flash('pat_user_name', $user->name);
        Session::flash('pat_plain_token', $token);

        return Action::redirect(route('oauth2.pat.created'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request)
    {
        return [
            Heading::make(
                '<p>To avoid issues, let the outer page load fully before clicking "Create Personal Access Token."</p>'
            )
                ->asHtml(),

            Text::make('Name')
                ->help('Enter a name to identify this token. It will be visible to the user.')
                ->rules('required'),
        ];
    }
}
