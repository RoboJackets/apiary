<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Travel;
use App\Models\User;
use App\Nova\Dashboards\Main;
use Carbon\CarbonImmutable;
use Tests\TestCase;

final class NovaDashboardTravelInclusionTest extends TestCase
{
    public function test_includes_future_trip_with_assignments(): void
    {
        $travel = $this->createTrip(true, 10);
        $this->createTravelAssignment($travel, User::factory()->create(), false);

        $this->assertTrue(Main::shouldIncludeTravel($travel));
    }

    public function test_includes_past_trip_with_unpaid_assignment(): void
    {
        $travel = $this->createTrip(false, 10);
        $this->createTravelAssignment($travel, User::factory()->create(), false);

        $this->assertTrue(Main::shouldIncludeTravel($travel));
    }

    public function test_excludes_past_trip_when_all_assignments_are_paid(): void
    {
        $travel = $this->createTrip(false, 10);
        $this->createTravelAssignment($travel, User::factory()->create(), true);

        $this->assertFalse(Main::shouldIncludeTravel($travel));
    }

    public function test_excludes_past_trip_when_all_assignments_are_paid_or_charged_off(): void
    {
        $travel = $this->createTrip(false, 10);
        $this->createTravelAssignment($travel, User::factory()->create(), true);

        $chargedOff = $this->createTravelAssignment($travel, User::factory()->create(), false);
        $chargedOff->charged_off_at = now();
        $chargedOff->save();

        $this->assertFalse(Main::shouldIncludeTravel($travel));
    }

    public function test_includes_past_trip_when_one_assignment_is_still_outstanding(): void
    {
        $travel = $this->createTrip(false, 10);
        $this->createTravelAssignment($travel, User::factory()->create(), true);

        $chargedOff = $this->createTravelAssignment($travel, User::factory()->create(), false);
        $chargedOff->charged_off_at = now();
        $chargedOff->save();

        $this->createTravelAssignment($travel, User::factory()->create(), false);

        $this->assertTrue(Main::shouldIncludeTravel($travel));
    }

    private function createTrip(bool $future, int $feeAmount): Travel
    {
        $contact = User::factory()->create();

        return Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'departure_date' => $future ? CarbonImmutable::now()->addDays(3) : CarbonImmutable::now()->subDays(10),
            'return_date' => $future ? CarbonImmutable::now()->addDays(10) : CarbonImmutable::now()->subDays(5),
            'fee_amount' => $feeAmount,
            'status' => 'approved',
        ]);
    }
}
