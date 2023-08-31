<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Jobs\PushToJedi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class SyncAccess extends Action
{
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
                return Action::danger('Service accounts do not have access to other systems.');
            }

            PushToJedi::dispatchSync($user, self::class, Auth::user()->id, 'manual');

            return Action::message('Access was synced successfully!');
        }

        foreach ($models as $user) {
            if ($user->is_service_account) {
                $this->markAsFinished($user);

                continue;
            }

            PushToJedi::dispatchSync(
                $user,
                self::class,
                request()->user()->id,
                count($models) === 1 ? 'manual' : 'manual_batch'
            );

            $this->markAsFinished($user);
        }

        return Action::message('Access was synced successfully!');
    }
}
