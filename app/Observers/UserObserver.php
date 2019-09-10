<?php

declare(strict_types=1);

namespace App\Observers;

use App\User;
use App\Jobs\PushToJedi;

class UserObserver
{
    public function saved(User $user): void
    {
        PushToJedi::dispatch($user, 'App\User', $user->id, 'saved')->onQueue('jedi');
    }

    public function updated(User $user): void
    {
        if (null === $user->access_override_until || $user->access_override_until <= new \DateTime()) {
            return;
        }

        PushToJedi::dispatch($user, 'App\User', $user->id, 'updated/access override expiration')
            ->delay($user->access_override_until)->onQueue('jedi');
    }
}
