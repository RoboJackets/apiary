<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OAuth2ClientPolicy {
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the notification template.
     */
    public function view(User $user, Client $client): bool
    {
        return $user->hasRole("admin") || $user->id === $client->user_id;
    }

    /**
     * Determine whether the user can create notification templates.
     */
    public function create(User $user): bool
    {
        return $user->hasRole("admin");
    }

    /**
     * Determine whether the user can update the notification template.
     */
    public function update(User $user, Client $client): bool
    {
        return $user->hasRole("admin") || $user->id === $client->user_id;
    }

    /**
     * Determine whether the user can delete the notification template.
     */
    public function delete(User $user, Client $client): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the notification template.
     */
    public function restore(User $user, Client $client): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the OAuth2 client.
     */
    public function forceDelete(User $user, Client $client): bool
    {
        return false;
    }
}
