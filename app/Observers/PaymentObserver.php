<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Observers;

use App\Jobs\PruneDuesNotificationsInNova;
use App\Jobs\PushToJedi;
use App\Jobs\SendDuesPaymentReminder;
use App\Jobs\SendPaymentReceipt;
use App\Models\DuesTransaction;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;

class PaymentObserver
{
    public function saved(Payment $payment): void
    {
        PushToJedi::dispatch($payment->payable->user, Payment::class, $payment->id, 'saved');

        $payment->payable->user->searchable();

        PruneDuesNotificationsInNova::dispatch($payment->payable->user);

        if ($payment->payable_type === DuesTransaction::getMorphClassStatic() &&
            0 === intval($payment->amount)
        ) {
            SendDuesPaymentReminder::dispatch($payment->payable->user);
        }

        // this is pretty cursed but i don't have a better idea on guaranteeing exactly one receipt email from
        // ~four save events
        Cache::lock('send_payment_receipt_'.$payment->id, 5)->get(static function () use ($payment): void {
            if (! $payment->receipt_sent &&
                intval($payment->amount) > 0 &&
                (
                    'square' !== $payment->method ||
                    null !== $payment->receipt_url
                )
            ) {
                    $payment->receipt_sent = true;
                    $payment->save();

                    SendPaymentReceipt::dispatch($payment);
            }
        });
    }
}
