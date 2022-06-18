<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\Travel\AllTravelAssignmentsComplete;
use App\Models\Travel;
use App\Models\User;
use Tests\TestCase;

class AllTravelAssignmentsCompleteEmailTest extends TestCase
{
    public function testTarRequired(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->create();
        $travel->tar_required = true;
        $travel->save();

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('fee and submitted Travel Authority Requests for');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
    }

    public function testTarNotRequired(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->create();

        $mailable = new AllTravelAssignmentsComplete($travel);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('fee for');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
    }
}
