<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Jobs\UpdateMajorsForUser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
     *
     * @suppress PhanPossiblyNonClassMethodCall
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        foreach ($models as $user) {
            if ($user->is_service_account) {
                continue;
            }

            UpdateMajorsForUser::dispatch($user)->onQueue('buzzapi');
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
