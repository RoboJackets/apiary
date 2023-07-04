<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the team.
     */
    public function view(User $user, Team $team): bool
    {
        return $team->visible ? $user->can('read-teams') : $user->can('read-teams-hidden');
    }

    /**
     * Determine whether the user can view any teams.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-teams');
    }

    /**
     * Determine whether the user can create teams.
     */
    public function create(User $user): bool
    {
        return $user->can('create-teams');
    }

    /**
     * Determine whether the user can update the team.
     */
    public function update(User $user, Team $team): bool
    {
        if ($user->can('update-teams')) {
            return true;
        }

        return ($team->projectManager !== null) && $team->projectManager->is($user);
    }

    /**
     * Determine whether the user can delete the team.
     */
    public function delete(User $user, Team $team): bool
    {
        return $user->can('delete-teams');
    }

    /**
     * Determine whether the user can restore the team.
     */
    public function restore(User $user, Team $team): bool
    {
        return $user->can('create-teams');
    }

    /**
     * Determine whether the user can permanently delete the team.
     */
    public function forceDelete(User $user, Team $team): bool
    {
        return false;
    }

    /**
     * Determine whether the user can attach a user to a team.
     */
    public function attachUser(User $user, Team $team, User $userResource): bool
    {
        if ($team->members->contains('id', $userResource->id)) {
            return false;
        }

        if ($team->visible === false && $user->cant('read-teams-hidden')) {
            return false;
        }

        if (($team->projectManager !== null) && $team->projectManager->is($user)) {
            return true;
        }

        if ($user->can('update-teams-membership-own') && $user->is($userResource) && $team->self_serviceable) {
            return true;
        }

        return $user->can('update-teams-membership');
    }

    /**
     * Determine whether the user can attach a user to a team.
     */
    public function attachAnyUser(User $user, Team $team): bool
    {
        if ($team->visible === false && $user->cant('read-teams-hidden')) {
            return false;
        }

        if (($team->projectManager !== null) && $team->projectManager->is($user)) {
            return true;
        }

        if ($user->can('update-teams-membership-own')
            && $team->self_serviceable
            && ! $team->members->contains('id', $user->id)
        ) {
            return true;
        }

        return $user->can('update-teams-membership');
    }

    /**
     * Determine whether the user can detach a user from a team.
     */
    public function detachUser(User $user, Team $team, User $userResource): bool
    {
        if ($team->visible === false && $user->cant('read-teams-hidden')) {
            return false;
        }

        if (($team->projectManager !== null) && $team->projectManager->is($user)) {
            return true;
        }

        if ($user->can('update-teams-membership-own') && $user->is($userResource) && $team->self_serviceable) {
            return true;
        }

        return $user->can('update-teams-membership');
    }

    public function replicate(User $user, Team $team): bool
    {
        return false;
    }
}
