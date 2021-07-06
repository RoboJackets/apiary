<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OAuth2Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OAuth2ClientPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the OAuth2 client.
     */
    public function view(User $user, OAuth2Client $client): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create OAuth2 clients.
     */
    public function create(User $user): bool
    {
        return false;  // use the action
    }

    /**
     * Determine whether the user can update the OAuth2 client.
     */
    public function update(User $user, OAuth2Client $client): bool
    {
        return $user->hasRole('admin') || $user->id === $client->user_id;
    }

    /**
     * Determine whether the user can delete the OAuth2 client.
     */
    public function delete(User $user, OAuth2Client $client): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the OAuth2 client.
     */
    public function restore(User $user, OAuth2Client $client): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the OAuth2 client.
     */
    public function forceDelete(User $user, OAuth2Client $client): bool
    {
        return false;
    }
}
