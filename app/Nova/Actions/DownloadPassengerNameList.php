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

class DownloadPassengerNameList extends Action
{
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

        $filename = $trip->name.' Passenger Name List.csv';
        $path = 'nova-exports/'.$filename;

        $csv = $trip->assignments->reduce(
            static fn (string $carry, TravelAssignment $assignment): string => $carry.
                ',,'.
                $assignment->user->first_name.','.
                $assignment->user->legal_middle_name.','.
                $assignment->user->last_name.','.
                $assignment->user->legal_gender.','.
                $assignment->user->date_of_birth?->format('m/d/Y').','.
                $assignment->user->delta_skymiles_number.",\n",
            'Record Locator,GDS System,First Name,Middle Name,Last Name,Gender,Date of Birth (Month/Day/Year),'.
            "Delta SkyMiles Number,Tour Conductor\n"
        );

        Storage::put($path, $csv);

        $url = URL::signedRoute('api.v1.nova.export', ['file' => $filename], now()->addMinutes(5));

        return ActionResponse::download($filename, $url)
            ->withMessage('The passenger name list was successfully downloaded!');
    }
}
