<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Payment;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;

class ResetIdempotencyKey extends DestructiveAction
{
    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\Models\Payment>  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        if (count($models) > 1) {
            return Action::danger('This action can only be run on one payment at a time.');
        }

        $payment = $models->first();
        $payment->unique_id = Payment::generateUniqueId();
        $payment->save();

        return Action::message('The idempotency key was reset!');
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
