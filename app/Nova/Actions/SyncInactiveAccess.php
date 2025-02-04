<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Jobs\PushToJedi;
use App\Models\User;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class SyncInactiveAccess extends Action
{
    /**
     * Determine where the action redirection should be without confirmation.
     *
     * @var bool
     */
    public $withoutConfirmation = true;

    /**
     * Indicates if this action is only available on the resource index view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;

    /**
     * Indicates if the action can be run without any models.
     *
     * @var bool
     */
    public $standalone = true;

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach (User::where('is_service_account', '=', false)->accessInactive()->get() as $user) {
            PushToJedi::dispatch($user, self::class, request()->user()->id, 'manual_batch');
        }

        return Action::message('Access was synced successfully!');
    }
}
