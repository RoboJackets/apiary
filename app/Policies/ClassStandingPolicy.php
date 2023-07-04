<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ClassStanding;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClassStandingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any class standings.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the class standing.
     */
    public function view(User $user, ClassStanding $classStanding): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create class standings.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the class standing.
     */
    public function update(User $user, ClassStanding $classStanding): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the class standing.
     */
    public function delete(User $user, ClassStanding $classStanding): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the class standing.
     */
    public function restore(User $user, ClassStanding $classStanding): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the class standing.
     */
    public function forceDelete(User $user, ClassStanding $classStanding): bool
    {
        return false;
    }

    /**
     * Determine whether the user can attach a user to the major.
     */
    public function attachUser(User $user, ClassStanding $classStanding, User $userResource): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can attach any user to the major.
     */
    public function attachAnyUser(User $user, ClassStanding $classStanding): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can detach a user from the major.
     */
    public function detachUser(User $user, ClassStanding $classStanding, User $userResource): bool
    {
        return $user->hasRole('admin');
    }

    public function replicate(User $user, ClassStanding $classStanding): bool
    {
        return false;
    }
}
