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
    public function test_tar_required_and_not_completed_and_not_paid(): void
    {
        $member = User::factory()->create();
        $contact = User::factory()->create();

        $travel = Travel::factory()->make([
            'forms' => [
                Travel::TRAVEL_INFORMATION_FORM_KEY => true,
            ],
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
        $mailable->assertSeeInText('You still need to submit a travel information form');
        $mailable->assertSeeInText('You also still need to make a $10 payment');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function test_tar_required_and_completed_and_not_paid(): void
    {
        $member = User::factory()->create();
        $contact = User::factory()->create();

        $travel = Travel::factory()->make([
            'forms' => [
                Travel::TRAVEL_INFORMATION_FORM_KEY => true,
            ],
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

    public function test_tar_not_required_and_not_paid(): void
    {
        $member = User::factory()->create();
        $contact = User::factory()->create();

        $travel = Travel::factory()->make([
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
