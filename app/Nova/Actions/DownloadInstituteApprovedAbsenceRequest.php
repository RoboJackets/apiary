<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\TravelAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;

class DownloadInstituteApprovedAbsenceRequest extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Download IAA Request';

    /**
     * Determine where the action redirection should be without confirmation.
     *
     * @var bool
     */
    public $withoutConfirmation = true;

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Travel>  $models
     *
     * @phan-suppress PhanTypeMismatchArgument
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $trip = $models->sole();

        $filename = $trip->name.' IAA Request.csv';
        $path = 'nova-exports/'.$filename;

        $csv = $trip->assignments->reduce(
            static fn (string $carry, TravelAssignment $assignment): string => $carry.
                $assignment->user->first_name.','.
                $assignment->user->last_name.','.
                $assignment->user->gtid.','.
                $assignment->user->emergency_contact_name.','.
                $assignment->user->emergency_contact_phone."\n",
            "First Name,Last Name,GTID,Emergency Contact Name,Emergency Contact Phone Number\n"
        );

        Storage::put($path, $csv);

        $url = URL::signedRoute('api.v1.nova.export', ['file' => $filename], now()->addMinutes(5));

        return ActionResponse::download($filename, $url)
            ->withMessage('The IAA request was successfully downloaded!');
    }
}
