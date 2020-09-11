<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\PushToJedi;
use App\User;
use DateTime;

class UserObserver
{
    public function saved(User $user): void
    {
        PushToJedi::dispatch($user, User::class, $user->id, 'saved');
    }

    public function updated(User $user): void
    {
        if (null === $user->access_override_until || $user->access_override_until <= new DateTime()) {
            return;
        }

        PushToJedi::dispatch($user, User::class, $user->id, 'updated or access override expiration')
            ->delay($user->access_override_until);
    }
}
