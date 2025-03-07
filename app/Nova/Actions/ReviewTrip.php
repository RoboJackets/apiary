<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.PHP.UselessParentheses.UselessParentheses

namespace App\Nova\Actions;

use App\Jobs\PrefetchSquareCheckoutLinkForTravelAssignment;
use App\Jobs\SendDocuSignEnvelopeForTravelAssignment;
use App\Jobs\SendTravelAssignmentCreatedNotification;
use App\Models\Payment;
use App\Models\TravelAssignment;
use App\Notifications\Nova\TravelApproved;
use App\Rules\AllCriteriaMustBeSelected;
use App\Util\Matrix;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ReviewTrip extends Action
{
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
    public $confirmButtonText = 'Approve';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Carefully review the fields below. Changes can\'t be made after the trip is approved.';

    /**
     * The size of the modal. Can be "sm", "md", "lg", "xl", "2xl", "3xl", "4xl", "5xl", "6xl", "7xl".
     *
     * @var string
     */
    public $modalSize = '4xl';

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Travel>  $models
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $trip = $models->sole();

        $trip->status = 'approved';
        $trip->save();

        if ($trip->needs_docusign) {
            if ($trip->fee_amount > 0) {
                $trip->assignments->each(static function (TravelAssignment $assignment): void {
                    PrefetchSquareCheckoutLinkForTravelAssignment::dispatch($assignment)
                        ->chain([
                            new SendDocuSignEnvelopeForTravelAssignment($assignment, true),
                            new SendTravelAssignmentCreatedNotification($assignment),
                        ]);
                });
            } else {
                $trip->assignments->each(static function (TravelAssignment $assignment): void {
                    SendDocuSignEnvelopeForTravelAssignment::dispatch($assignment, true)
                        ->chain([
                            new SendTravelAssignmentCreatedNotification($assignment),
                        ]);
                });
            }
        } else {
            if ($trip->fee_amount > 0) {
                $trip->assignments->each(static function (TravelAssignment $assignment): void {
                    PrefetchSquareCheckoutLinkForTravelAssignment::dispatch($assignment)
                        ->chain([
                            new SendTravelAssignmentCreatedNotification($assignment),
                        ]);
                });
            } else {
                $trip->assignments->each(static function (TravelAssignment $assignment): void {
                    SendTravelAssignmentCreatedNotification::dispatch($assignment);
                });
            }
        }

        $trip->primaryContact->notify(new TravelApproved($trip));

        return ActionResponse::message('The trip has been approved!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        $trip = \App\Models\Travel::where('id', '=', $request->resourceId ?? $request->resources)->sole();

        $totalCost = intval($trip->tar_lodging) +
            intval($trip->tar_registration) +
            intval($trip->car_rental_cost) +
            intval($trip->meal_per_diem);

        $airfareCost = $trip->assignments->reduce(
            static function (?float $carry, \App\Models\TravelAssignment $assignment): ?float {
                $thisAirfareCost = Matrix::getHighestDisplayPrice($assignment->matrix_itinerary);

                if ($thisAirfareCost !== null && $carry !== null && $thisAirfareCost > $carry) {
                    return $thisAirfareCost;
                } elseif ($thisAirfareCost !== null && $carry === null) {
                    return $thisAirfareCost;
                } else {
                    return $carry;
                }
            }
        );

        if ($airfareCost !== null && $airfareCost > 0) {
            $totalCost += $airfareCost;
        }

        return [
            Heading::make('Review Name'),

            Text::make('Trip Name')
                ->default(static fn (): string => $trip->name)
                ->readonly(),

            BooleanGroup::make('Review Criteria')
                ->options([
                    'descriptive' => 'Trip name includes the name of the competition or other event',
                    'spelling' => 'Trip name is spelled correctly',
                    'date' => 'Trip name includes the year the trip is occurring, and month if necessary to '.
                        'disambiguate',
                ])
                ->rules('required', 'json', new AllCriteriaMustBeSelected())
                ->required(),

            Heading::make('Review Financials'),

            Currency::make('Trip Fee')
                ->default(static fn (): float => $trip->fee_amount)
                ->readonly()
                ->help('This is the amount that will be collected from each traveler.'),

            ...($trip->fee_amount > 0 ? [
                Currency::make('Square Processing Fee')
                    ->default(static fn (): float => Payment::calculateProcessingFee($trip->fee_amount * 100) / 100)
                    ->readonly()
                    ->help(
                        view('nova.help.travel.review.processingfee', ['fee_amount' => $trip->fee_amount])->render()
                    ),
            ] : []),

            ...($trip->needs_docusign ? [
                Currency::make('Total Cost per Traveler')
                    ->default(static fn (): float => $totalCost)
                    ->readonly(),
            ] : []),

            ...($trip->fee_amount > 0 ? [
                Text::make('Fee-to-Cost Ratio')
                    ->default(
                        static fn (): string => $totalCost === 0 ?
                            'Not Applicable' :
                            intval(ceil(($trip->fee_amount / $totalCost) * 100)).'%'
                    )
                    ->readonly()
                    ->help('This is the percentage of costs that are paid by travelers via the trip fee.'),
            ] : []),

            ...($trip->needs_docusign ? [
                Text::make('Workday Account Number')
                    ->default(static fn (): ?string => $trip->tar_project_number)
                    ->readonly(),
            ] : []),

            BooleanGroup::make('Review Criteria')
                ->options([
                    'fee_reasonable' => 'Trip fee is reasonable',
                    ...($trip->fee_amount > 0 ? [
                        'ratio_reasonable' => 'Ratio of trip fee to cost is reasonable',
                    ] : []),
                    ...($trip->needs_docusign ? [
                        'account_number_correct' => 'Workday account number is correct',
                    ] : []),
                ])
                ->rules('required', 'json', new AllCriteriaMustBeSelected())
                ->required(),

            Heading::make('Review Assignments'),

            Number::make('Number of Travelers')
                ->default(static fn (): int => $trip->assignments->count())
                ->readonly(),

            BooleanGroup::make('Review Criteria')
                ->options([
                    'travelers_finalized' => 'List of travelers is final',
                    ...($trip->needs_airfare_form ? [
                        'airfare_reasonable' => 'Flight selections and prices are reasonable',
                    ] : []),
                ])
                ->rules('required', 'json', new AllCriteriaMustBeSelected())
                ->required(),
        ];
    }
}
