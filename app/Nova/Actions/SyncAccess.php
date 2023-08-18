<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Jobs\PushToJedi;
use Illuminate\Support\Collection;
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
        foreach ($models as $user) {
            if ($user->is_service_account) {
                continue;
            }

            // I tried to make this class ShouldQueue so Nova would handle queueing
            // but was getting an exception. I think it's fine to run synchronously...?
            PushToJedi::dispatchSync(
                $user,
                self::class,
                request()->user()->id,
                count($models) === 1 ? 'manual' : 'manual_batch'
            );
        }
    }
}
