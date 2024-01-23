<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\DocuSignEnvelope;
use App\Models\TravelAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use ZipArchive;

class DownloadDocuSignForms extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Download Forms';

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
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $travel = $models->first()->load('assignments.envelope.signable.user');

        $filename = $travel->name.'.zip';

        $path = Storage::disk('local')->path('nova-exports/'.$filename);

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE);

        $travel->assignments->each(static function (TravelAssignment $assignment, int $key) use ($zip): void {
            $assignment->envelope->each(static function (DocuSignEnvelope $envelope, int $key) use ($zip): void {
                if ($envelope->travel_authority_filename !== null) {
                    $zip->addFile(
                        Storage::disk('local')->path($envelope->travel_authority_filename),
                        $envelope->signable->user->full_name.' - Travel Authority Request.pdf'
                    );
                }

                if ($envelope->covid_risk_filename !== null) {
                    $zip->addFile(
                        Storage::disk('local')->path($envelope->covid_risk_filename),
                        $envelope->signable->user->full_name.' - COVID Risk Acknowledgement.pdf'
                    );
                }

                if ($envelope->direct_bill_airfare_filename !== null) {
                    $zip->addFile(
                        Storage::disk('local')->path($envelope->direct_bill_airfare_filename),
                        $envelope->signable->user->full_name.' - Direct Bill Airfare Request.pdf'
                    );
                }

                if ($envelope->itinerary_request_filename !== null) {
                    $zip->addFile(
                        Storage::disk('local')->path($envelope->itinerary_request_filename),
                        $envelope->signable->user->full_name.' - Itinerary Request.pdf'
                    );
                }
            });
        });

        if ($zip->count() === 0) {
            return Action::danger('No forms have been submitted!');
        }

        $zip->close();

        // Generate signed URL to pass to frontend to facilitate file download
        $url = URL::signedRoute('api.v1.nova.export', ['file' => $filename], now()->addMinutes(5));

        return ActionResponse::download($filename, $url)
            ->withMessage('The forms were successfully downloaded!');
    }
}
