<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\Travel\TravelAssignmentReminder;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Tests\TestCase;

class TravelAssignmentReminderEmailTest extends TestCase
{
    public function testTarRequiredAndNotCompletedAndNotPaid(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->create();
        $travel->tar_required = true;
        $travel->fee_amount = 10;
        $travel->save();

        $assignment = TravelAssignment::factory()->create();

        $contact = User::factory()->create();

        $travel->primary_contact_user_id = $contact->id;
        $travel->save();

        $mailable = new TravelAssignmentReminder($assignment);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText($contact->full_name);
        $mailable->assertSeeInText('You still need to submit a Travel Authority Request');
        $mailable->assertSeeInText('You also still need to make a $10 payment');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
    }

    public function testTarRequiredAndCompletedAndNotPaid(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->create();
        $travel->tar_required = true;
        $travel->fee_amount = 10;
        $travel->save();

        $assignment = TravelAssignment::factory()->create();
        $assignment->tar_received = true;
        $assignment->save();

        $contact = User::factory()->create();

        $travel->primary_contact_user_id = $contact->id;
        $travel->save();

        $mailable = new TravelAssignmentReminder($assignment);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText($contact->full_name);
        $mailable->assertDontSeeInText('You still need to submit a Travel Authority Request');
        $mailable->assertSeeInText('You still need to make a $10 payment for ');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
    }

    public function testTarNotRequiredAndNotPaid(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->create();
        $travel->tar_required = false;
        $travel->fee_amount = 10;
        $travel->save();

        $assignment = TravelAssignment::factory()->create();

        $contact = User::factory()->create();

        $travel->primary_contact_user_id = $contact->id;
        $travel->save();

        $mailable = new TravelAssignmentReminder($assignment);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText($contact->full_name);
        $mailable->assertDontSeeInText('You still need to submit a Travel Authority Request');
        $mailable->assertSeeInText('You still need to make a $10 payment for ');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
    }
}
