<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendReminders;
use App\Jobs\SendTravelAssignmentReminder;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use App\Notifications\Travel\TravelAssignmentReminder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class SendTravelAssignmentReminderChargeOffTest extends TestCase
{
    public function test_send_reminders_skips_charged_off_unpaid_assignment(): void
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

        Queue::fake();

        (new SendReminders($member))->handle();

        Queue::assertNotPushed(SendTravelAssignmentReminder::class);
        $this->assertNotNull($assignment->charged_off_at);
    }

    public function test_send_reminders_dispatches_for_charged_off_assignment_that_needs_forms(): void
    {
        $member = User::factory()->create();
        $travel = Travel::factory()->create([
            'departure_date' => CarbonImmutable::now()->subDays(10),
            'return_date' => CarbonImmutable::now()->subDays(5),
            'fee_amount' => 10,
            'status' => 'approved',
            'forms' => [
                Travel::TRAVEL_INFORMATION_FORM_KEY => true,
            ],
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

        $this->assertTrue($assignment->needs_docusign);

        Queue::fake();

        (new SendReminders($member))->handle();

        Queue::assertPushed(SendTravelAssignmentReminder::class);
    }

    public function test_send_reminders_dispatches_for_unpaid_assignment(): void
    {
        $member = User::factory()->create();
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
            ]);
            $assignment->save();

            return $assignment;
        });

        Queue::fake();

        (new SendReminders($member))->handle();

        Queue::assertPushed(SendTravelAssignmentReminder::class);
    }

    public function test_notification_should_not_send_when_charged_off_and_forms_not_needed(): void
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

        $this->assertFalse($assignment->needs_docusign);

        $notification = new TravelAssignmentReminder($assignment);

        $this->assertFalse($notification->shouldSend($member, 'mail'));
    }

    public function test_notification_should_send_when_charged_off_but_forms_needed(): void
    {
        $member = User::factory()->create([
            'emergency_contact_name' => null,
            'emergency_contact_phone' => null,
        ]);
        $travel = Travel::factory()->create([
            'departure_date' => CarbonImmutable::now()->addDays(3),
            'return_date' => CarbonImmutable::now()->addDays(10),
            'fee_amount' => 10,
            'status' => 'approved',
            'forms' => [
                Travel::TRAVEL_INFORMATION_FORM_KEY => true,
            ],
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

        $this->assertTrue($assignment->needs_docusign);

        $notification = new TravelAssignmentReminder($assignment);

        $this->assertTrue($notification->shouldSend($member, 'mail'));
    }

    public function test_notification_should_send_when_not_charged_off(): void
    {
        $member = User::factory()->create([
            'emergency_contact_name' => null,
            'emergency_contact_phone' => null,
        ]);
        $travel = Travel::factory()->create([
            'departure_date' => CarbonImmutable::now()->addDays(3),
            'return_date' => CarbonImmutable::now()->addDays(10),
            'fee_amount' => 10,
            'status' => 'approved',
        ]);
        $assignment = TravelAssignment::withoutEvents(static function () use ($travel, $member): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'travel_id' => $travel->id,
                'user_id' => $member->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $notification = new TravelAssignmentReminder($assignment);

        $this->assertTrue($notification->shouldSend($member, 'mail'));
    }
}
