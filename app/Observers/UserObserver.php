<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\PushToJedi;
use App\User;

class UserObserver
{
    public function saved(User $user): void
    {
        PushToJedi::dispatch($user, User::class, $user->id, 'saved');
    }
}
