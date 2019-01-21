<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class DistributeShirt extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $failures = [];
        foreach ($models as $model) {
            if ($model->package()->first()->eligible_for_shirt) {
                if ($model->is_paid) {
                    if (null === $model->swag_shirt_provided) {
                        $model->swag_shirt_provided = date('Y-m-d H:i:s');
                        $model->swag_shirt_providedBy = \Auth::user()->id;
                        $model->save();
                    } else {
                        $this->markAsFailed($model, null);
                        if (count($models) === 1) {
                            return Action::danger('T-shirt already picked up.');
                        } else {
                            $failures[] = $model->id;
                        }
                    }
                } else {
                    $this->markAsFailed($model, null);
                    if (count($models) === 1) {
                        return Action::danger('This transaction is not yet paid.');
                    } else {
                        $failures[] = $model->id;
                    }
                }
            } else {
                $this->markAsFailed($model, null);
                if (count($models) === 1) {
                    return Action::danger('The associated package is not eligible for a shirt.');
                } else {
                    $failures[] = $model->id;
                }
            }
        }
        if (count($failures) > 0) {
            if (count($models) > count($failures)) {
                return Action::danger('Some selected dues transactions are currently not eligible for a shirt: '.implode(', ', $failures));
            } else {
                return Action::danger('No selected dues transactions are currently eligible for a shirt.');
            }
        } else {
            return Action::message('T-shirt'.(count($models) === 1 ? '' : 's').' marked as picked up!');
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
