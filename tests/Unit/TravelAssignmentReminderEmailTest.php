<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\Travel\TravelAssignmentReminder;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Tests\TestCase;

final class TravelAssignmentReminderEmailTest extends TestCase
{
    public function testTarRequiredAndNotCompletedAndNotPaid(): void
    {
        $member = User::factory()->create();
        $contact = User::factory()->create();

        $travel = Travel::factory()->make([
            'tar_required' => true,
            'fee_amount' => 10,
            'primary_contact_user_id' => $contact->id,
        ]);
        $travel->save();

        $assignment = TravelAssignment::withoutEvents(static function () use ($travel, $member): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'travel_id' => $travel->id,
                'user_id' => $member->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $mailable = new TravelAssignmentReminder($assignment);

        $mailable->assertSeeInText($member->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText($contact->full_name);
        $mailable->assertSeeInText('You still need to submit a Travel Authority Request');
        $mailable->assertSeeInText('You also still need to make a $10 payment');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testTarRequiredAndCompletedAndNotPaid(): void
    {
        $member = User::factory()->create();
        $contact = User::factory()->create();

        $travel = Travel::factory()->make([
            'tar_required' => true,
            'fee_amount' => 10,
            'primary_contact_user_id' => $contact->id,
        ]);
        $travel->save();

        $assignment = TravelAssignment::withoutEvents(static function () use ($travel, $member): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'travel_id' => $travel->id,
                'user_id' => $member->id,
                'tar_received' => true,
            ]);
            $assignment->save();

            return $assignment;
        });

        $mailable = new TravelAssignmentReminder($assignment);

        $mailable->assertSeeInText($member->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText($contact->full_name);
        $mailable->assertDontSeeInText('You still need to submit a Travel Authority Request');
        $mailable->assertSeeInText('You still need to make a $10 payment for ');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testTarNotRequiredAndNotPaid(): void
    {
        $member = User::factory()->create();
        $contact = User::factory()->create();

        $travel = Travel::factory()->make([
            'tar_required' => false,
            'fee_amount' => 10,
            'primary_contact_user_id' => $contact->id,
        ]);
        $travel->save();

        $assignment = TravelAssignment::withoutEvents(static function () use ($travel, $member): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'travel_id' => $travel->id,
                'user_id' => $member->id,
                'tar_received' => false,
            ]);
            $assignment->save();

            return $assignment;
        });

        $mailable = new TravelAssignmentReminder($assignment);

        $mailable->assertSeeInText($member->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText($contact->full_name);
        $mailable->assertDontSeeInText('You still need to submit a Travel Authority Request');
        $mailable->assertSeeInText('You still need to make a $10 payment for ');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }
}
