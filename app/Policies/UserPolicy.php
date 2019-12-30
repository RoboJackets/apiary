<?php

declare(strict_types=1);

namespace App\Policies;

use App\Team;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the user.
     */
    public function view(User $user, User $userResource): bool
    {
        return $user->can('read-users');
    }

    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-users');
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->can('create-users');
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $user, User $userResource): bool
    {
        return $user->can('update-users');
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $user, User $userResource): bool
    {
        return $user->can('delete-users');
    }

    /**
     * Determine whether the user can restore the user.
     */
    public function restore(User $user, User $userResource): bool
    {
        return $user->can('create-users');
    }

    /**
     * Determine whether the user can permanently delete the user.
     */
    public function forceDelete(User $user, User $userResource): bool
    {
        return false;
    }

    /**
     * Determine whether the user can attach a team to a user.
     */
    public function attachTeam(User $user, User $userResource, Team $team): bool
    {
        if ($team->members->contains('id', $userResource->id)) {
            return false;
        }

        if (false === $team->visible && $user->cant('read-teams-hidden')) {
            return false;
        }

        if ($user->can('update-teams-membership-own') && $user->is($userResource) && $team->self_serviceable) {
            return true;
        }

        if ((null !== $team->projectManager) && $team->projectManager->is($user)) {
            return true;
        }

        return $user->can('update-teams-membership');
    }

    /**
     * Determine whether the user can attach a team to a user.
     */
    public function attachAnyTeam(User $user, User $userResource): bool
    {
        if ($user->can('update-teams-membership')) {
            return true;
        }

        if ($user->can('update-teams-membership-own') && $user->is($userResource)) {
            return true;
        }

        $user_manages = $user->manages()->get();
        if (count($user_manages) > 0) {
            $target_in = $userResource->teams()->get();
            $diff = $user_manages->diff($target_in);
            if (count($diff) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can detach a team from a user.
     */
    public function detachTeam(User $user, User $userResource, Team $team): bool
    {
        if (false === $team->visible && $user->cant('read-teams-hidden')) {
            return false;
        }

        if ($user->can('update-teams-membership-own') && $user->is($userResource) && $team->self_serviceable) {
            return true;
        }

        if ((null !== $team->projectManager) && $team->projectManager->is($user)) {
            return true;
        }

        return $user->can('update-teams-membership');
    }
}
