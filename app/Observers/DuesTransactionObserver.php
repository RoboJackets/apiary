<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\CreateDuesPaymentDueNotificationInNova;
use App\Jobs\PruneDuesNotificationsInNova;
use App\Models\DuesTransaction;

class DuesTransactionObserver
{
    public function saved(DuesTransaction $duesTransaction): void
    {
        CreateDuesPaymentDueNotificationInNova::dispatch($duesTransaction->user);
        PruneDuesNotificationsInNova::dispatch($duesTransaction->user);
    }
}
