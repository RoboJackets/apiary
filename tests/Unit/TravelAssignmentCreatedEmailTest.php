<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\Travel\TravelAssignmentCreated;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Tests\TestCase;

final class TravelAssignmentCreatedEmailTest extends TestCase
{
    public function test_tar_required(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->make([
            'forms' => [
                Travel::TRAVEL_INFORMATION_FORM_KEY => true,
            ],
        ]);
        $travel->save();

        $assignment = TravelAssignment::withoutEvents(static function () use ($travel, $user): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'travel_id' => $travel->id,
                'user_id' => $user->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $mailable = new TravelAssignmentCreated($assignment);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('Complete the following tasks');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function test_tar_not_required(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->make([
            'fee_amount' => 10,
        ]);
        $travel->save();

        $assignment = TravelAssignment::withoutEvents(static function () use ($travel, $user): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'travel_id' => $travel->id,
                'user_id' => $user->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $mailable = new TravelAssignmentCreated($assignment);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('Pay the $10 trip fee');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }
}
