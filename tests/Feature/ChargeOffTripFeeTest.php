<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Travel;
use App\Models\User;
use App\Nova\Actions\ChargeOffTripFee;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Tests\TestCase;

final class ChargeOffTripFeeTest extends TestCase
{
    public function test_admin_can_charge_off_after_return_date(): void
    {
        $admin = $this->getTestUser(['admin']);
        Auth::login($admin);

        $member = User::factory()->create();
        $travel = $this->createPastTrip(10);
        $assignment = $this->createTravelAssignment($travel, $member, false);

        $response = (new ChargeOffTripFee())->handle(
            new ActionFields(collect(), collect()),
            Collection::make([$assignment])
        );

        $this->assertInstanceOf(ActionResponse::class, $response);
        $assignment->refresh();
        $this->assertNotNull($assignment->charged_off_at);
    }

    public function test_non_admin_cannot_charge_off(): void
    {
        $officer = $this->getTestUser(['officer']);
        Auth::login($officer);

        $member = User::factory()->create();
        $travel = $this->createPastTrip(10);
        $assignment = $this->createTravelAssignment($travel, $member, false);

        (new ChargeOffTripFee())->handle(
            new ActionFields(collect(), collect()),
            Collection::make([$assignment])
        );

        $assignment->refresh();
        $this->assertNull($assignment->charged_off_at);
    }

    public function test_cannot_charge_off_before_return_date(): void
    {
        $admin = $this->getTestUser(['admin']);
        Auth::login($admin);

        $member = User::factory()->create();
        $contact = User::factory()->create();
        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'departure_date' => CarbonImmutable::now()->subDays(3),
            'return_date' => CarbonImmutable::now()->addDays(3),
            'fee_amount' => 10,
            'status' => 'approved',
        ]);
        $assignment = $this->createTravelAssignment($travel, $member, false);

        (new ChargeOffTripFee())->handle(
            new ActionFields(collect(), collect()),
            Collection::make([$assignment])
        );

        $assignment->refresh();
        $this->assertNull($assignment->charged_off_at);
    }

    public function test_cannot_charge_off_paid_assignment(): void
    {
        $admin = $this->getTestUser(['admin']);
        Auth::login($admin);

        $member = User::factory()->create();
        $travel = $this->createPastTrip(10);
        $assignment = $this->createTravelAssignment($travel, $member, true);

        (new ChargeOffTripFee())->handle(
            new ActionFields(collect(), collect()),
            Collection::make([$assignment])
        );

        $assignment->refresh();
        $this->assertNull($assignment->charged_off_at);
    }

    public function test_cannot_charge_off_already_charged_off_assignment(): void
    {
        $admin = $this->getTestUser(['admin']);
        Auth::login($admin);

        $member = User::factory()->create();
        $travel = $this->createPastTrip(10);
        $assignment = $this->createTravelAssignment($travel, $member, false);
        $assignment->charged_off_at = now()->subDay();
        $assignment->save();

        $chargedOffAt = $assignment->charged_off_at;

        (new ChargeOffTripFee())->handle(
            new ActionFields(collect(), collect()),
            Collection::make([$assignment])
        );

        $assignment->refresh();
        $this->assertTrue($assignment->charged_off_at->equalTo($chargedOffAt));
    }

    public function test_cannot_charge_off_draft_trip(): void
    {
        $admin = $this->getTestUser(['admin']);
        Auth::login($admin);

        $member = User::factory()->create();
        $contact = User::factory()->create();
        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'departure_date' => CarbonImmutable::now()->subDays(10),
            'return_date' => CarbonImmutable::now()->subDays(5),
            'fee_amount' => 10,
            'status' => 'draft',
        ]);
        $assignment = $this->createTravelAssignment($travel, $member, false);

        (new ChargeOffTripFee())->handle(
            new ActionFields(collect(), collect()),
            Collection::make([$assignment])
        );

        $assignment->refresh();
        $this->assertNull($assignment->charged_off_at);
    }

    public function test_available_for_requires_past_return_date_and_unpaid(): void
    {
        $member = User::factory()->create();
        $travel = $this->createPastTrip(10);
        $assignment = $this->createTravelAssignment($travel, $member, false);

        $this->assertTrue(ChargeOffTripFee::availableFor($assignment));

        $paidAssignment = $this->createTravelAssignment(
            $travel,
            User::factory()->create(),
            true
        );
        $this->assertFalse(ChargeOffTripFee::availableFor($paidAssignment));
    }

    private function createPastTrip(int $feeAmount): Travel
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
