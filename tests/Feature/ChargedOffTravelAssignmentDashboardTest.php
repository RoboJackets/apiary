<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Tests\TestCase;

final class ChargedOffTravelAssignmentDashboardTest extends TestCase
{
    public function test_charged_off_assignment_is_still_current_travel_assignment(): void
    {
        $member = User::factory()->create();
        $travel = Travel::factory()->create([
            'departure_date' => CarbonImmutable::now()->subDays(10),
            'return_date' => CarbonImmutable::now()->subDays(5),
            'fee_amount' => 10,
            'status' => 'approved',
        ]);
        $assignment = TravelAssignment::withoutEvents(static function () use ($travel, $member): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'travel_id' => $travel->id,
                'user_id' => $member->id,
                'charged_off_at' => now(),
            ]);
            $assignment->save();

            return $assignment;
        });

        $member->refresh();

        $this->assertTrue($member->current_travel_assignment->is($assignment));
        $this->assertFalse($member->current_travel_assignment->is_paid);
    }

    public function test_dashboard_shows_action_required_for_charged_off_assignment(): void
    {
        $this->withoutMix();

        $member = $this->getTestUser(['member']);
        $travel = Travel::factory()->create([
            'departure_date' => CarbonImmutable::now()->subDays(10),
            'return_date' => CarbonImmutable::now()->subDays(5),
            'fee_amount' => 10,
            'status' => 'approved',
        ]);
        TravelAssignment::withoutEvents(static function () use ($travel, $member): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'travel_id' => $travel->id,
                'user_id' => $member->id,
                'charged_off_at' => now(),
            ]);
            $assignment->save();

            return $assignment;
        });

        $response = $this->actingAs($member, 'web')->get('/');

        $response->assertOk();
        $response->assertSee('Action Required for Travel');
        $response->assertSee($travel->name);
    }
}
