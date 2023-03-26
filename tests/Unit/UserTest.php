<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Payment;
use Tests\TestCase;

final class UserTest extends TestCase
{
    /**
     * Test the is_student attribute.
     */
    public function testIsStudent(): void
    {
        $user = $this->getTestUser(['member']);

        $user->primary_affiliation = 'member';
        $this->assertFalse($user->is_student);
        $user->primary_affiliation = 'faculty';
        $this->assertFalse($user->is_student);
        $user->primary_affiliation = 'staff';
        $this->assertFalse($user->is_student);
        $user->primary_affiliation = 'employee';
        $this->assertFalse($user->is_student);
        $user->primary_affiliation = 'affiliate';
        $this->assertFalse($user->is_student);

        $user->primary_affiliation = 'student';
        $this->assertTrue($user->is_student);

        // Create dues package / transaction / payment
        $package = DuesPackage::factory()->make();
        $package->effective_end = now()->addMonths(10);
        $package->restricted_to_students = true;
        $package->save();
        $transaction = new DuesTransaction();
        $transaction->user_id = $user->id;
        $transaction->dues_package_id = $package->id;
        $transaction->save();
        $payment = new Payment();
        $payment->payable_id = $transaction->id;
        $payment->payable_type = DuesTransaction::getMorphClassStatic();
        $payment->recorded_by = $user->id;
        $payment->method = 'cash';
        $payment->amount = $package->cost;
        // @phan-suppress-next-line PhanTypeMismatchPropertyProbablyReal
        $payment->processing_fee = 0;
        $payment->save();

        // They paid a student package
        $this->assertTrue($user->is_student);

        $package->restricted_to_students = false;
        $package->save();

        // They paid a non-student package
        $this->assertFalse($user->is_student);
    }
}
