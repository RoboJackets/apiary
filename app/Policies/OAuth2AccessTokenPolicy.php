<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OAuth2AccessToken;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OAuth2AccessTokenPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any OAuth2 access tokens.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the OAuth2 access token.
     */
    public function view(User $user, OAuth2AccessToken $token): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create OAuth2 access token.
     */
    public function create(User $user): bool
    {
        return false;  // use the action
    }

    /**
     * Determine whether the user can update the OAuth2 access token.
     */
    public function update(User $user, OAuth2AccessToken $token): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the OAuth2 access token.
     */
    public function delete(User $user, OAuth2AccessToken $token): bool
    {
        return false;  // use the action
    }

    /**
     * Determine whether the user can restore the OAuth2 access token.
     */
    public function restore(User $user, OAuth2AccessToken $token): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the OAuth2 access token.
     */
    public function forceDelete(User $user, OAuth2AccessToken $token): bool
    {
        return false;
    }

    public function replicate(User $user, OAuth2AccessToken $token): bool
    {
        return false;
    }
}
