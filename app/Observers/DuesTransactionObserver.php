<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\CreateDuesPaymentDueNotificationInNova;
use App\Jobs\PruneDuesNotificationsInNova;
use App\Jobs\SendDuesPaymentDueNotification;
use App\Models\DuesTransaction;

class DuesTransactionObserver
{
    public function saved(DuesTransaction $duesTransaction): void
    {
        CreateDuesPaymentDueNotificationInNova::dispatch($duesTransaction->user);

        PruneDuesNotificationsInNova::dispatch($duesTransaction->user);

        SendDuesPaymentDueNotification::dispatch($duesTransaction->user)
            ->delay(now()->addHours(48)->hour(10)->minute(0)->second(0));
    }
}
