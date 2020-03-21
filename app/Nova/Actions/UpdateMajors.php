<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Jobs\CreateOrUpdateUserFromBuzzAPI;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class UpdateMajors extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\User>  $models
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        foreach ($models as $user) {
            if ($user->is_service_account) {
                continue;
            }

            // It's not possible for users to be created here but the parameter is required.
            CreateOrUpdateUserFromBuzzAPI::dispatch(
                CreateOrUpdateUserFromBuzzAPI::IDENTIFIER_USER,
                $user,
                'nova_action'
            )->onQueue('buzzapi');
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
