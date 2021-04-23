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
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\Models\User>  $models
     */
    public function handle(ActionFields $fields, Collection $models): void
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
                1 === count($models) ? 'manual' : 'manual_batch'
            );
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        return [];
    }
}
