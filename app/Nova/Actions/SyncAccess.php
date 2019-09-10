<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Nova\Actions;

use App\Jobs\PushToJedi;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SyncAccess extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields  $fields
     * @param \Illuminate\Support\Collection  $models
     *
     * @return void
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        foreach ($models as $user) {
            if (Str::startsWith($user->uid, 'svc_')) {
                continue;
            }

            // I tried to make this class ShouldQueue so Nova would handle queueing
            // but was getting an exception. I think it's fine to run synchronously...?
            PushToJedi::dispatchNow($user, 'App\Nova\Actions\SyncAccess', request()->user()->id, 'manual');
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
