<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireSingleLineCondition
// phpcs:disable SlevomatCodingStandard.Functions.RequireSingleLineCall

namespace App\Nova\Dashboards;

use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Nova\Metrics\ActiveAttendanceBreakdown;
use App\Nova\Metrics\AttendancePerWeek;
use App\Nova\Metrics\DuesRevenueByFiscalYear;
use App\Nova\Metrics\MembersByFiscalYear;
use App\Nova\Metrics\PaymentReceivedForTravel;
use App\Nova\Metrics\PaymentsPerDay;
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

                if ($travel->needs_docusign === true) {
                    $cards[] = new TravelAuthorityRequestReceivedForTravel($travel->id);
                }

                $cards[] = (new PaymentReceivedForTravel($travel->id))->canSee(
                    static fn (Request $request): bool => $request->user()->can('read-payments')
                );
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

        return $cards;
    }
}
