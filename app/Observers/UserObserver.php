<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\CreateOrUpdateUserFromBuzzAPI;
use App\Jobs\PushToJedi;
use App\User;
use DateTime;

class UserObserver
{
    public function created(User $user): void
    {
        if ('cas_login' === $user->create_reason) {
            return;
        }

        CreateOrUpdateUserFromBuzzAPI::dispatch(CreateOrUpdateUserFromBuzzAPI::IDENTIFIER_USER, $user, 'buzzapi_user_observer')
            ->onQueue('buzzapi');
    }

    public function saved(User $user): void
    {
        PushToJedi::dispatch($user, User::class, $user->id, 'saved')->onQueue('jedi');
    }

    public function updated(User $user): void
    {
        if (null === $user->access_override_until || $user->access_override_until <= new DateTime()) {
            return;
        }

        PushToJedi::dispatch($user, User::class, $user->id, 'updated/access override expiration')
            ->delay($user->access_override_until)->onQueue('jedi');
    }
}
