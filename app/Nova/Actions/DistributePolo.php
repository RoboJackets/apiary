<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator

namespace App\Nova\Actions;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class DistributePolo extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\Models\DuesTransaction>  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        $failures = [];
        foreach ($models as $model) {
            if ($model->package()->first()->eligible_for_polo) {
                if ($model->is_paid) {
                    if (null === $model->swag_polo_provided) {
                        $model->swag_polo_provided = Carbon::now();
                        $model->swag_polo_providedBy = Auth::user()->id;
                        $model->save();
                    } else {
                        $this->markAsFailed($model, 'Already picked up');
                        if (1 === count($models)) {
                            return Action::danger('Polo already picked up.');
                        }

                        $failures[] = $model->id;
                    }
                } else {
                    $this->markAsFailed($model, 'Not yet paid');
                    if (1 === count($models)) {
                        return Action::danger('This transaction is not yet paid.');
                    }

                    $failures[] = $model->id;
                }
            } else {
                $this->markAsFailed($model, 'Not eligible');
                if (1 === count($models)) {
                    return Action::danger('The associated package is not eligible for a polo.');
                }

                $failures[] = $model->id;
            }
        }
        if (count($failures) > 0) {
            if (count($models) > count($failures)) {
                return Action::danger(
                    'Some selected dues transactions are currently not eligible for a polo: '.implode(', ', $failures)
                );
            }

            return Action::danger('No selected dues transactions are currently eligible for a polo.');
        }

        return Action::message(Str::plural('Polo', count($models)).' marked as picked up!');
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
