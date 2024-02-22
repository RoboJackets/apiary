<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments

namespace App\Observers;

use App\Jobs\CheckAllTravelAssignmentsComplete;
use App\Jobs\PruneDuesNotificationsInNova;
use App\Jobs\PruneTravelAssignmentNotificationsInNova;
use App\Jobs\PushToJedi;
use App\Jobs\SendDuesPaymentReminder;
use App\Jobs\SendPaymentReceipt;
use App\Jobs\SendReminders;
use App\Jobs\SendTravelAssignmentReminder;
use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\TravelAssignment;
use Illuminate\Support\Facades\Cache;

class PaymentObserver
{
    public function saved(Payment $payment): void
    {
        SendReminders::dispatch($payment->payable->user);
        PushToJedi::dispatch($payment->payable->user, Payment::class, $payment->id, 'saved');

        $payment->payable->user->searchable();

        PruneDuesNotificationsInNova::dispatch($payment->payable->user);

        if ($payment->payable_type === DuesTransaction::getMorphClassStatic() && intval($payment->amount) === 0) {
            SendDuesPaymentReminder::dispatch($payment->payable->user);
        }

        if ($payment->payable_type === TravelAssignment::getMorphClassStatic()) {
            SendTravelAssignmentReminder::dispatch($payment->payable);
            PruneTravelAssignmentNotificationsInNova::dispatch($payment->payable->user);
            CheckAllTravelAssignmentsComplete::dispatch($payment->payable->travel);
        }

        // this is pretty cursed but i don't have a better idea on guaranteeing exactly one receipt email
        // from ~four save events
        // this will wait up to 60 seconds to acquire a lock, and hold it for the duration of the closure
        // if the lock cannot be acquired an exception will be thrown, i don't think it will be an issue in prod
        if (! $payment->receipt_sent) {
            Cache::lock(name: 'send_payment_receipt_'.$payment->id, seconds: 120)->block(
                seconds: 60,
                callback: static function () use ($payment): void {
                    // double-check that a receipt was not sent in a different thread
                    $payment->refresh();
                    if (! $payment->receipt_sent &&
                        intval($payment->amount) > 0 &&
                        $payment->method !== 'waiver' &&
                        (
                            $payment->method !== 'square' ||
                            $payment->receipt_url !== null
                        )
                    ) {
                        $payment->receipt_sent = true;
                        $payment->save();

                        SendPaymentReceipt::dispatch($payment);
                    }
                }
            );
        }
    }
}
