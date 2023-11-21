<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\Dues\PaymentReminder;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\User;
use Tests\TestCase;

final class DuesPaymentReminderEmailTest extends TestCase
{
    public function testGenerateEmailNoOtherPackageAvailable(): void
    {
        $user = User::factory()->create();

        $package = DuesPackage::factory()->create();

        $transaction = DuesTransaction::factory()->make([
            'user_id' => $user->id,
            'dues_package_id' => $package->id,
        ]);

        $mailable = new PaymentReminder($transaction);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText(intval($package->cost));
        $mailable->assertSeeInText($package->name);
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText('If you would prefer to pay for');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testGenerateEmailWithOtherPackageAvailable(): void
    {
        $user = User::factory()->create();
        $user->primary_affiliation = 'student';
        $user->save();

        $firstPackage = DuesPackage::factory()->create();

        $secondPackage = DuesPackage::factory()->create();
        $secondPackage->available_for_purchase = true;
        $secondPackage->effective_end = now()->addHour();
        $secondPackage->restricted_to_students = true;
        $secondPackage->save();

        $transaction = DuesTransaction::factory()->make([
            'user_id' => $user->id,
            'dues_package_id' => $firstPackage->id,
        ]);

        $mailable = new PaymentReminder($transaction);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText(intval($firstPackage->cost));
        $mailable->assertSeeInText($firstPackage->name);
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertSeeInText('If you would prefer to pay for '.$secondPackage->name.' instead,');
        $mailable->assertDontSeeInText("\n\n\n");
    }
}
