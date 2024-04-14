<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireSingleLineCondition
// phpcs:disable SlevomatCodingStandard.Functions.RequireSingleLineCall

namespace App\Nova\Dashboards;

use App\Models\Event;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Nova\Metrics\ActiveAttendanceBreakdown;
use App\Nova\Metrics\AttendancePerWeek;
use App\Nova\Metrics\DuesRevenueByFiscalYear;
use App\Nova\Metrics\EmergencyContactInformationForTravel;
use App\Nova\Metrics\MembersByFiscalYear;
use App\Nova\Metrics\PaymentReceivedForTravel;
use App\Nova\Metrics\PaymentsPerDay;
use App\Nova\Metrics\RsvpSourceBreakdown;
use App\Nova\Metrics\TransactionsByDuesPackage;
use App\Nova\Metrics\TravelAuthorityRequestReceivedForTravel;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(): array
    {
        $cards = [
            (new PaymentsPerDay())->canSee(
                static fn (Request $request): bool => $request->user()->can('read-payments')
            ),
            (new MembersByFiscalYear())->canSee(
                static fn (Request $request): bool => $request->user()->can('read-dues-transactions')
            ),
            (new DuesRevenueByFiscalYear())->canSee(
                static fn (Request $request): bool => $request->user()->can('read-dues-transactions')
            ),
            (new AttendancePerWeek())->canSee(
                static fn (Request $request): bool => $request->user()->can('read-attendance')
            ),
            (new ActiveAttendanceBreakdown())->canSee(
                static fn (Request $request): bool => $request->user()->can('read-users') && $request->user()->can(
                    'read-attendance'
                )
            ),
            (new TransactionsByDuesPackage())
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-payments')),
        ];

        if (request()->is('nova-api/dashboards/main')) {
            foreach (Travel::all() as $travel) {
                $should_include = false;

                if ($travel->return_date > Carbon::now() && $travel->assignments()->exists()) {
                    $should_include = true;
                }

                if (
                    $travel->needs_docusign === true &&
                    $travel->assignments()->where('tar_received', false)->exists()
                ) {
                    $should_include = true;
                }

                if (
                    $travel->assignments()->leftJoin('payments', static function (JoinClause $join): void {
                        $join->on('travel_assignments.id', '=', 'payable_id')
                            ->where('payments.amount', '>', 0)
                            ->where('payments.payable_type', TravelAssignment::getMorphClassStatic())
                            ->whereNull('payments.deleted_at');
                    })->whereNull(
                        'payments.id'
                    )->exists()
                ) {
                    $should_include = true;
                }

                if (! $should_include) {
                    continue;
                }

                $cards[] = (new PaymentReceivedForTravel($travel->id))->canSee(
                    static fn (Request $request): bool => $request->user()->can('read-payments')
                );

                if ($travel->needs_docusign === true) {
                    $cards[] = new TravelAuthorityRequestReceivedForTravel($travel->id);
                }

                $cards[] = new EmergencyContactInformationForTravel($travel->id);
            }

            foreach (Event::all() as $event) {
                $should_include = false;
                $attributes = $event->getAttributes();

                if ($event->rsvps()->count() > 0) {
                    $should_include = true;
                }
                if ($event->attendance()->count() > 0) {
                    $should_include = true;
                }
                if ($event->end_time === null) {
                    if ($event->start_time === null || $event->start_time < Carbon::now()) {
                        $should_include = false;
                    }
                } elseif ($event->end_time < Carbon::now()) {
                    $should_include = false;
                }
                if (! $should_include) {
                    continue;
                }

                $cards[] = (new RsvpSourceBreakdown($event->id))
                    ->canSee(static fn (Request $request): bool => $request->user()->can('read-rsvps'));
                $cards[] = (new ActiveAttendanceBreakdown(true, $event->id, 'event'))
                    ->canSee(static fn (Request $request): bool => $request->user()->can('read-attendance'));
            }

            return $cards;
        }

        if (request()->is('nova-api/metrics/*-received-*')) {
            $parts = Str::of(Str::of(request()->path())->explode('/')->last())->explode('-');
            $type = $parts->first();
            $id = intval($parts->last());

            switch ($type) {
                case 'forms':
                    return [new TravelAuthorityRequestReceivedForTravel($id)];
                case 'payment':
                    return [
                        (new PaymentReceivedForTravel($id))->canSee(
                            static fn (Request $request): bool => $request->user()->can('read-payments')
                        ),
                    ];
            }
        }

        if (request()->is('nova-api/metrics/emergency-contact-*')) {
            $parts = Str::of(Str::of(request()->path())->explode('/')->last())->explode('-');
            $id = intval($parts->last());

            return [
                new EmergencyContactInformationForTravel($id),
            ];
        }

        if (request()->is('nova-api/metrics/active-attendance-breakdown-event-*')) {
            $parts = Str::of(Str::of(request()->path())->explode('/')->last())->explode('-');
            $id = intval($parts->last());

            return [
                (new ActiveAttendanceBreakdown(true, $id, 'event'))->canSee(
                    static fn (Request $request): bool => $request->user()->can('read-attendance')
                ),
            ];
        }

        if (request()->is('nova-api/metrics/rsvp-source-breakdown-*')) {
            $parts = Str::of(Str::of(request()->path())->explode('/')->last())->explode('-');
            $id = intval($parts->last());

            return [
                (new RsvpSourceBreakdown($id))->canSee(
                    static fn (Request $request): bool => $request->user()->can('read-rsvps')
                ),
            ];
        }

        return $cards;
    }
}
