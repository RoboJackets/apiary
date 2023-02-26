<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\Travel\AllTravelAssignmentsComplete;
use App\Models\Payment;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Tests\TestCase;

class AllTravelAssignmentsCompleteEmailTest extends TestCase
{
    public function testTarRequired(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->make([
            'tar_required' => true,
            'primary_contact_user_id' => $user->id,
        ]);
        $travel->save();

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('fee and submitted travel forms for');
        $mailable->assertSeeInText('from TESTING Apiary at http://localhost:8080/nova/resources/travel/');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testTarRequiredWithOneAssignmentNeedsPayment(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->make([
            'tar_required' => true,
            'primary_contact_user_id' => $user->id,
        ]);
        $travel->save();

        TravelAssignment::create([
            'travel_id' => $travel->id,
            'user_id' => $user->id,
            'tar_received' => true,
        ]);

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('submitted travel forms for');
        $mailable->assertSeeInText('1 traveler still needs to pay the travel fee.');
        $mailable->assertSeeInText('from TESTING Apiary at http://localhost:8080/nova/resources/travel/');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testTarRequiredWithTwoAssignmentsNeedPayment(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $travel = Travel::factory()->make([
            'tar_required' => true,
            'primary_contact_user_id' => $user->id,
        ]);
        $travel->save();

        TravelAssignment::create([
            'travel_id' => $travel->id,
            'user_id' => $user->id,
            'tar_received' => true,
        ]);

        TravelAssignment::create([
            'travel_id' => $travel->id,
            'user_id' => $otherUser->id,
            'tar_received' => true,
        ]);

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('submitted travel forms for');
        $mailable->assertSeeInText('2 travelers still need to pay the travel fee.');
        $mailable->assertSeeInText('from TESTING Apiary at http://localhost:8080/nova/resources/travel/');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testTarRequiredWithOneAssignmentNeedsForms(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->make([
            'tar_required' => true,
            'primary_contact_user_id' => $user->id,
        ]);
        $travel->save();

        $assignment = TravelAssignment::create([
            'travel_id' => $travel->id,
            'user_id' => $user->id,
            'tar_received' => false,
        ]);

        Payment::create([
            'payable_type' => $assignment->getMorphClass(),
            'payable_id' => $assignment->id,
            'amount' => $travel->fee_amount,
            'method' => 'waiver',
        ]);

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('All travelers have paid the travel fee');
        $mailable->assertSeeInText('. 1 traveler still needs to submit forms.');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testTarRequiredWithTwoAssignmentsNeedForms(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $travel = Travel::factory()->make([
            'tar_required' => true,
            'primary_contact_user_id' => $user->id,
        ]);
        $travel->save();

        $firstAssignment = TravelAssignment::create([
            'travel_id' => $travel->id,
            'user_id' => $user->id,
            'tar_received' => false,
        ]);

        $secondAssignment = TravelAssignment::create([
            'travel_id' => $travel->id,
            'user_id' => $otherUser->id,
            'tar_received' => false,
        ]);

        Payment::create([
            'payable_type' => $firstAssignment->getMorphClass(),
            'payable_id' => $firstAssignment->id,
            'amount' => $travel->fee_amount,
            'method' => 'waiver',
        ]);

        Payment::create([
            'payable_type' => $secondAssignment->getMorphClass(),
            'payable_id' => $secondAssignment->id,
            'amount' => $travel->fee_amount,
            'method' => 'waiver',
        ]);

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('All travelers have paid the travel fee');
        $mailable->assertSeeInText('. 2 travelers still need to submit forms.');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testTarNotRequired(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->make([
            'primary_contact_user_id' => $user->id,
        ]);
        $travel->save();

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('fee for');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }
}
