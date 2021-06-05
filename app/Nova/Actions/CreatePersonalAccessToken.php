<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Text;

class CreatePersonalAccessToken extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if (sizeof($models) > 1) {
            return Action::danger('This action can only be run on one model at a time.');
        }

        if (empty(config('passport.personal_access_client.id'))
            || empty(config('passport.personal_access_client.secret'))) {
            return Action::danger('Passport personal access client ID and/or secret environment variables not'.
                ' set. Make sure they are set and try again.');
        }

        $user = $models[0];

        $token = $user->createToken('[PAT] '.$fields->name)->accessToken;

        Session::flash('pat_user_name', $user->name);
        Session::flash('pat_plain_token', $token);

        return Action::redirect(route('oauth2.pat.created'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Heading::make('<p>To avoid issues, let the outer page load fully before clicking Run Action'.
                '.</p>')->asHtml(),
            Text::make('Name')->help('[PAT] will be automatically preprended to identify this token '.
                'as a Personal Access Token.')->rules('required'),
        ];
    }
}
