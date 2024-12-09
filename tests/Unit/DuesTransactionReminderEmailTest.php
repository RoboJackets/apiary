<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\Dues\TransactionReminder;
use App\Models\DuesPackage;
use App\Models\User;
use Tests\TestCase;

final class DuesTransactionReminderEmailTest extends TestCase
{
    public function test_generate_email_no_other_package_available(): void
    {
        $user = User::factory()->create();
        $user->primary_affiliation = 'student';
        $user->save();

        $package = DuesPackage::factory()->create();
        $package->available_for_purchase = true;
        $package->effective_end = now()->addHour();
        $package->restricted_to_students = true;
        $package->save();

        $mailable = new TransactionReminder($user);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText(intval($package->cost));
        $mailable->assertSeeInText($package->name);
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText('If you would prefer to pay for');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function test_generate_email_with_other_package_available(): void
    {
        $user = User::factory()->create();
        $user->primary_affiliation = 'student';
        $user->save();

        $firstPackage = DuesPackage::factory()->make([
            'name' => 'first package',
        ]);
        $firstPackage->available_for_purchase = true;
        $firstPackage->effective_end = now()->addHour();
        $firstPackage->restricted_to_students = true;
        $firstPackage->save();

        $secondPackage = DuesPackage::factory()->make([
            'name' => 'second package',
        ]);
        $secondPackage->available_for_purchase = true;
        $secondPackage->effective_end = now()->addHour();
        $secondPackage->restricted_to_students = true;
        $secondPackage->save();

        $mailable = new TransactionReminder($user);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText(intval($firstPackage->cost));
        $mailable->assertSeeInText($firstPackage->name);
        $mailable->assertSeeInText(intval($secondPackage->cost));
        $mailable->assertSeeInText($secondPackage->name);
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }
}
