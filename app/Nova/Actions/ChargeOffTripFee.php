<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\TravelAssignment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ChargeOffTripFee extends DestructiveAction
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Charge Off';

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Charge Off';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Are you sure you want to charge off this trip fee? The assignment will remain unpaid,'.
        ' but will no longer keep this trip on the dashboard or trigger reminder emails.';

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\TravelAssignment>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $assignment = $models->sole();

        if (! Auth::user()->hasRole('admin')) {
            return Action::danger('Only admins can charge off trip fees.');
        }

        if ($assignment->travel->status === 'draft') {
            return Action::danger('Trip fees cannot be charged off while the trip is in draft status.');
        }

        if ($assignment->travel->return_date >= Carbon::today()) {
            return Action::danger('Trip fees can only be charged off after the return date has passed.');
        }

        if ($assignment->is_paid) {
            return Action::danger('This travel assignment is already paid in full.');
        }

        if ($assignment->charged_off_at !== null) {
            return Action::danger('This travel assignment has already been charged off.');
        }

        $assignment->charged_off_at = now();
        $assignment->save();

        return Action::message('Trip fee charged off!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     *
     * @psalm-pure
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [];
    }

    public static function availableFor(TravelAssignment $assignment): bool
    {
        return $assignment->travel !== null
            && $assignment->travel->status !== 'draft'
            && $assignment->travel->return_date < Carbon::today()
            && ! $assignment->is_paid
            && $assignment->charged_off_at === null;
    }
}
