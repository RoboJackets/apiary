<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Jobs\CreateOrUpdateUserFromBuzzAPI;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class RefreshFromGTED extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Refresh from GTED';

    /**
     * Determine where the action redirection should be without confirmation.
     *
     * @var bool
     */
    public $withoutConfirmation = true;

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() === 1) {
            $user = $models->sole();

            if ($user->is_service_account) {
                return Action::danger('Service accounts cannot be refreshed from GTED.');
            }

            CreateOrUpdateUserFromBuzzAPI::dispatchSync(
                CreateOrUpdateUserFromBuzzAPI::IDENTIFIER_USER,
                $user,
                'nova_action'
            );

            return Action::message('The user was refreshed successfully!');
        }

        $counter = 0;

        foreach ($models as $user) {
            if ($user->is_service_account) {
                continue;
            }

            // It's not possible for users to be created here but the parameter is required.
            CreateOrUpdateUserFromBuzzAPI::dispatch(
                CreateOrUpdateUserFromBuzzAPI::IDENTIFIER_USER,
                $user,
                'nova_action_batch'
            );

            $counter++;
        }

        return Action::message('Refresh jobs have been queued for '.$counter.' users!');
    }
}
