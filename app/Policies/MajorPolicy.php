<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Major;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MajorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any majors.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the major.
     */
    public function view(User $user, Major $major): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create majors.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the major.
     */
    public function update(User $user, Major $major): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the major.
     */
    public function delete(User $user, Major $major): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the major.
     */
    public function restore(User $user, Major $major): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the major.
     */
    public function forceDelete(User $user, Major $major): bool
    {
        return false;
    }

    /**
     * Determine whether the user can attach a user to the major.
     */
    public function attachUser(User $user, Major $major, User $userResource): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can attach any user to the major.
     */
    public function attachAnyUser(User $user, Major $major): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can detach a user from the major.
     */
    public function detachUser(User $user, Major $major, User $userResource): bool
    {
        return $user->hasRole('admin');
    }

    public function replicate(User $user, Major $major): bool
    {
        return false;
    }
}
