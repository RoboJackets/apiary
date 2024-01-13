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
    public function testTarRequired(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->make([
            'tar_required' => true,
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
        $mailable->assertSeeInText('Please complete the following items');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testTarNotRequired(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->make([
            'tar_required' => false,
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
        $mailable->assertSeeInText('Please pay the $10 travel fee');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }
}
