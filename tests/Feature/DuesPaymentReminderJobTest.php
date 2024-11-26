<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendDuesPaymentReminder;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class DuesPaymentReminderJobTest extends TestCase
{
    private int $jobCounter;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobCounter = 0;
    }

    public function test_creating_transaction_dispatches_job(): void
    {
        Queue::fake();

        $this->user = User::factory()->create();

        DuesPackage::factory()->create();

        $transaction = DuesTransaction::factory()->make([
            'user_id' => $this->user->id,
        ]);
        $transaction->save();

        Queue::assertPushed(SendDuesPaymentReminder::class, [$this, 'validateJob']);
    }

    public function test_creating_two_transactions_dispatches_one_job(): void
    {
        Queue::fake();

        $this->user = User::factory()->create();

        DuesPackage::factory()->create();

        $transactionOne = DuesTransaction::factory()->make([
            'user_id' => $this->user->id,
        ]);
        $transactionOne->save();

        $transactionTwo = DuesTransaction::factory()->make([
            'user_id' => $this->user->id,
        ]);
        $transactionTwo->save();

        Queue::assertPushed(SendDuesPaymentReminder::class, [$this, 'validateJob']);
    }

    public function test_creating_payment_with_zero_amount_dispatches_job(): void
    {
        Queue::fake();

        $this->user = User::factory()->create();

        DuesPackage::factory()->create();

        $transaction = DuesTransaction::withoutEvents(function (): DuesTransaction {
            $transaction = DuesTransaction::factory()->make([
                'user_id' => $this->user->id,
            ]);
            $transaction->save();

            return $transaction;
        });

        $payment = new Payment();
        $payment->payable_type = DuesTransaction::getMorphClassStatic();
        $payment->payable_id = $transaction->id;
        $payment->amount = 0;
        $payment->method = 'square';
        $payment->save();

        Queue::assertPushed(SendDuesPaymentReminder::class, [$this, 'validateJob']);
    }

    public function test_creating_payment_with_nonzero_amount_does_not_dispatch_job(): void
    {
        Queue::fake();

        $this->user = User::factory()->create();

        DuesPackage::factory()->create();

        $transaction = DuesTransaction::withoutEvents(function (): DuesTransaction {
            $transaction = DuesTransaction::factory()->make([
                'user_id' => $this->user->id,
            ]);
            $transaction->save();

            return $transaction;
        });

        $payment = new Payment();
        $payment->payable_type = DuesTransaction::getMorphClassStatic();
        $payment->payable_id = $transaction->id;
        $payment->amount = 100;
        $payment->method = 'square';
        $payment->save();

        Queue::assertNotPushed(SendDuesPaymentReminder::class);
    }

    public function validateJob(SendDuesPaymentReminder $job): bool
    {
        $this->assertEquals(0, $this->jobCounter);
        $this->assertLessThan($job->delay, now()->addHours(48)->hour(9)->startOfHour()->minute(59));
        $this->assertEquals($job->user->id, $this->user->id);
        $this->assertEquals($job->uniqueId(), strval($this->user->id));

        $this->jobCounter++;

        // returning true here so the outside assertion passes
        return true;
    }
}
