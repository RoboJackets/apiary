<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\PaymentReceipt;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Tests\TestCase;

final class PaymentReceiptEmailTest extends TestCase
{
    public function test_generate_email_for_online_dues_payment(): void
    {
        $user = User::factory()->create();

        $package = DuesPackage::factory()->create();

        $transaction = DuesTransaction::factory()->make([
            'user_id' => $user->id,
            'dues_package_id' => $package->id,
        ]);
        $transaction->save();

        $payment = new Payment();
        $payment->payable_type = DuesTransaction::getMorphClassStatic();
        $payment->payable_id = $transaction->id;
        $payment->amount = 103.30;
        $payment->method = 'square';
        $payment->receipt_url = 'https://example.com';
        $payment->save();

        $mailable = new PaymentReceipt($payment);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($package->name);
        $mailable->assertSeeInText('online payment');
        $mailable->assertSeeInText($payment->receipt_url);
        $mailable->assertSeeInText(number_format($payment->amount, 2));
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function test_generate_email_for_cash_dues_payment(): void
    {
        $member = User::factory()->create();

        $officer = User::factory()->create();

        $package = DuesPackage::factory()->create();

        $transaction = DuesTransaction::factory()->make([
            'user_id' => $member->id,
            'dues_package_id' => $package->id,
        ]);
        $transaction->save();

        $payment = new Payment();
        $payment->payable_type = DuesTransaction::getMorphClassStatic();
        $payment->payable_id = $transaction->id;
        $payment->amount = 100;
        $payment->method = 'cash';
        $payment->recorded_by = $officer->id;
        $payment->save();

        $mailable = new PaymentReceipt($payment);

        $mailable->assertSeeInText($member->preferred_first_name);
        $mailable->assertSeeInText($officer->name);
        $mailable->assertSeeInText($package->name);
        $mailable->assertSeeInText('cash');
        $mailable->assertSeeInText(number_format($payment->amount, 2));
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function test_generate_email_for_online_travel_payment_with_tar_required_but_not_complete(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->create();
        $travel['forms'] = [
            Travel::TRAVEL_INFORMATION_FORM_KEY => true,
        ];
        $travel->save();

        $assignment = TravelAssignment::withoutEvents(static function () use ($travel, $user): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'travel_id' => $travel->id,
                'user_id' => $user->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $payment = Payment::withoutEvents(static function () use ($assignment): Payment {
            $payment = new Payment();
            $payment->payable_type = TravelAssignment::getMorphClassStatic();
            $payment->payable_id = $assignment->id;
            $payment->amount = 103.30;
            $payment->method = 'square';
            $payment->receipt_url = 'https://example.com';
            $payment->save();

            return $payment;
        });

        $mailable = new PaymentReceipt($payment);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('online payment');
        $mailable->assertSeeInText($payment->receipt_url);
        $mailable->assertSeeInText(number_format($payment->amount, 2));
        $mailable->assertSeeInText('submit a travel information form');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function test_generate_email_for_online_travel_payment_with_tar_required_and_complete(): void
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
                'tar_received' => true,
            ]);
            $assignment->save();

            return $assignment;
        });

        $payment = Payment::withoutEvents(static function () use ($assignment): Payment {
            $payment = new Payment();
            $payment->payable_type = TravelAssignment::getMorphClassStatic();
            $payment->payable_id = $assignment->id;
            $payment->amount = 103.30;
            $payment->method = 'square';
            $payment->receipt_url = 'https://example.com';
            $payment->save();

            return $payment;
        });

        $mailable = new PaymentReceipt($payment);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('online payment');
        $mailable->assertSeeInText($payment->receipt_url);
        $mailable->assertSeeInText(number_format($payment->amount, 2));
        $mailable->assertDontSeeInText('submit a Travel Authority Request');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function test_generate_email_for_online_travel_payment_with_no_tar_required(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->create();

        $assignment = TravelAssignment::withoutEvents(static function () use ($travel, $user): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'travel_id' => $travel->id,
                'user_id' => $user->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $payment = Payment::withoutEvents(static function () use ($assignment): Payment {
            $payment = new Payment();
            $payment->payable_type = TravelAssignment::getMorphClassStatic();
            $payment->payable_id = $assignment->id;
            $payment->amount = 103.30;
            $payment->method = 'square';
            $payment->receipt_url = 'https://example.com';
            $payment->save();

            return $payment;
        });

        $mailable = new PaymentReceipt($payment);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('online payment');
        $mailable->assertSeeInText($payment->receipt_url);
        $mailable->assertSeeInText(number_format($payment->amount, 2));
        $mailable->assertDontSeeInText('submit a Travel Authority Request');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function test_generate_email_for_cash_travel_payment(): void
    {
        $member = User::factory()->create();

        $officer = User::factory()->create();

        $travel = Travel::factory()->create();

        $assignment = TravelAssignment::withoutEvents(static function () use ($travel, $member): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'travel_id' => $travel->id,
                'user_id' => $member->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $payment = Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = TravelAssignment::getMorphClassStatic();
            $payment->payable_id = $assignment->id;
            $payment->amount = 100;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        $mailable = new PaymentReceipt($payment);

        $mailable->assertSeeInText($member->preferred_first_name);
        $mailable->assertSeeInText($officer->name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText('cash');
        $mailable->assertSeeInText(number_format($payment->amount, 2));
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }
}
