<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Travel;
use App\Models\User;
use App\Nova\Metrics\OverdueTripFees;
use Carbon\CarbonImmutable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Tests\TestCase;

final class OverdueTripFeesMetricTest extends TestCase
{
    public function test_unpaid_assignment_on_departed_trip_counts_as_overdue(): void
    {
        $travel = $this->createDepartedTrip(10);
        $this->createTravelAssignment($travel, User::factory()->create(), false);

        $result = (new OverdueTripFees())->calculate(NovaRequest::create('/'));

        $this->assertSame(10, (int) $result->value);
    }

    public function test_charged_off_assignment_is_excluded_from_overdue_fees(): void
    {
        $travel = $this->createDepartedTrip(10);
        $this->createTravelAssignment($travel, User::factory()->create(), false);

        $chargedOff = $this->createTravelAssignment($travel, User::factory()->create(), false);
        $chargedOff->charged_off_at = now();
        $chargedOff->save();

        $result = (new OverdueTripFees())->calculate(NovaRequest::create('/'));

        $this->assertSame(10, (int) $result->value);
    }

    public function test_paid_assignment_is_excluded_from_overdue_fees(): void
    {
        $travel = $this->createDepartedTrip(10);
        $this->createTravelAssignment($travel, User::factory()->create(), true);

        $result = (new OverdueTripFees())->calculate(NovaRequest::create('/'));

        $this->assertSame(0, (int) $result->value);
    }

    private function createDepartedTrip(int $feeAmount): Travel
    {
        $contact = User::factory()->create();

        return Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'departure_date' => CarbonImmutable::now()->subDays(10),
            'return_date' => CarbonImmutable::now()->subDays(5),
            'fee_amount' => $feeAmount,
            'status' => 'approved',
        ]);
    }
}
