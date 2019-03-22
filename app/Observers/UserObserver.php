<?php

namespace App\Observers;

use App\User;
use App\Jobs\PushToJedi;

class UserObserver
{
    public function saved(User $user)
    {
        PushToJedi::dispatch($user)->onQueue('jedi');
    }

    public function updated(User $user)
    {
        if (null !== $user->access_override_until && $user->access_override_until > new \DateTime()) {
            PushToJedi::dispatch($user)->delay($user->access_override_until)->onQueue('jedi');
        }
    }
}
