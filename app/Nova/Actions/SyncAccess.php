<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Nova\Actions;

use App\Jobs\PushToJedi;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class SyncAccess extends Action
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
            if (Str::startsWith($user->uid, 'svc_')) {
                continue;
            }

            // I tried to make this class ShouldQueue so Nova would handle queueing
            // but was getting an exception. I think it's fine to run synchronously...?
            PushToJedi::dispatchNow(
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
