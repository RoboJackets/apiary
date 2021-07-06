<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Laravel\Passport\Token;

class OAuth2AccessTokenPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the OAuth2 access token.
     */
    public function view(User $user, Token $client): bool
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
    public function update(User $user, Token $client): bool
    {
        return false;  // not meaningful
    }

    /**
     * Determine whether the user can delete the OAuth2 access token.
     */
    public function delete(User $user, Token $client): bool
    {
        return false;  // use the action
    }

    /**
     * Determine whether the user can restore the OAuth2 access token.
     */
    public function restore(User $user, Token $client): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the OAuth2 access token.
     */
    public function forceDelete(User $user, Token $client): bool
    {
        return false;
    }
}
