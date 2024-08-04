<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\Travel\AllTravelAssignmentsComplete;
use App\Models\Payment;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Tests\TestCase;

final class AllTravelAssignmentsCompleteEmailTest extends TestCase
{
    public function testTarRequired(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->make([
            'forms' => [
                Travel::TRAVEL_INFORMATION_FORM_KEY => true,
            ],
            'primary_contact_user_id' => $user->id,
        ]);
        $travel->save();

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('fee and submitted forms for');
        $mailable->assertSeeInText('from TESTING Apiary at http://localhost:8080/nova/resources/trips/');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testTarRequiredWithOneAssignmentNeedsPayment(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->make([
            'forms' => [
                Travel::TRAVEL_INFORMATION_FORM_KEY => true,
            ],
            'primary_contact_user_id' => $user->id,
        ]);
        $travel->save();

        TravelAssignment::withoutEvents(static function () use ($travel, $user): void {
            TravelAssignment::create([
                'travel_id' => $travel->id,
                'user_id' => $user->id,
                'tar_received' => true,
            ]);
        });

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('submitted forms for');
        $mailable->assertSeeInText(". 1 traveler still needs to pay the trip fee.\n\n");
        $mailable->assertSeeInText('from TESTING Apiary at http://localhost:8080/nova/resources/trips/');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testTarRequiredWithTwoAssignmentsNeedPayment(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $travel = Travel::factory()->make([
            'forms' => [
                Travel::TRAVEL_INFORMATION_FORM_KEY => true,
            ],
            'primary_contact_user_id' => $user->id,
        ]);
        $travel->save();

        TravelAssignment::withoutEvents(static function () use ($travel, $user): void {
            TravelAssignment::create([
                'travel_id' => $travel->id,
                'user_id' => $user->id,
                'tar_received' => true,
            ]);
        });

        TravelAssignment::withoutEvents(static function () use ($travel, $otherUser): void {
            TravelAssignment::create([
                'travel_id' => $travel->id,
                'user_id' => $otherUser->id,
                'tar_received' => true,
            ]);
        });

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('submitted forms for');
        $mailable->assertSeeInText(". 2 travelers still need to pay the trip fee.\n\n");
        $mailable->assertSeeInText('from TESTING Apiary at http://localhost:8080/nova/resources/trips/');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testTarRequiredWithOneAssignmentNeedsForms(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->make([
            'forms' => [
                Travel::TRAVEL_INFORMATION_FORM_KEY => true,
            ],
            'primary_contact_user_id' => $user->id,
        ]);
        $travel->save();

        $assignment = TravelAssignment::withoutEvents(static fn (): TravelAssignment => TravelAssignment::create([
            'travel_id' => $travel->id,
            'user_id' => $user->id,
            'tar_received' => false,
        ]));

        Payment::withoutEvents(static function () use ($assignment): void {
            Payment::create([
                'payable_type' => $assignment->getMorphClass(),
                'payable_id' => $assignment->id,
                'amount' => $assignment->travel->fee_amount,
                'method' => 'waiver',
            ]);
        });

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('All travelers have paid the trip fee');
        $mailable->assertSeeInText('. 1 traveler still needs to submit a travel information form.');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testTarRequiredWithTwoAssignmentsNeedForms(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $travel = Travel::factory()->make([
            'forms' => [
                Travel::TRAVEL_INFORMATION_FORM_KEY => true,
            ],
            'primary_contact_user_id' => $user->id,
        ]);
        $travel->save();

        $firstAssignment = TravelAssignment::withoutEvents(static fn (): TravelAssignment => TravelAssignment::create([
            'travel_id' => $travel->id,
            'user_id' => $user->id,
            'tar_received' => false,
        ]));

        $secondAssignment = TravelAssignment::withoutEvents(
            static fn (): TravelAssignment => TravelAssignment::create([
                'travel_id' => $travel->id,
                'user_id' => $otherUser->id,
                'tar_received' => false,
            ])
        );

        Payment::withoutEvents(static function () use ($firstAssignment): void {
            Payment::create([
                'payable_type' => $firstAssignment->getMorphClass(),
                'payable_id' => $firstAssignment->id,
                'amount' => $firstAssignment->travel->fee_amount,
                'method' => 'waiver',
            ]);
        });

        Payment::withoutEvents(static function () use ($secondAssignment): void {
            Payment::create([
                'payable_type' => $secondAssignment->getMorphClass(),
                'payable_id' => $secondAssignment->id,
                'amount' => $secondAssignment->travel->fee_amount,
                'method' => 'waiver',
            ]);
        });

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('All travelers have paid the trip fee');
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
