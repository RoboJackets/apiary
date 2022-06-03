<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\DuesPaymentReminder;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\User;
use Tests\TestCase;

class DuesPaymentReminderEmailTest extends TestCase
{
    public function testGenerateEmail(): void
    {
        $user = User::factory()->create();

        $package = DuesPackage::factory()->create();

        // this will always use the above two resources, because there aren't any others
        $transaction = DuesTransaction::factory()->create();

        $mailable = new DuesPaymentReminder($transaction);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText(intval($package->cost));
        $mailable->assertSeeInText($package->name);
        $mailable->assertSeeInText(number_format(Payment::calculateSurcharge($package->cost * 100) / 100, 2));
    }
}
