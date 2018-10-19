<?php

namespace App\Policies;

use App\Team;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @return mixed
     */
    public function view(User $user, Team $team)
    {
        if (! $team->visible) {
            return $user->can('read-teams-hidden');
        } else {
            return $user->can('read-teams');
        }
    }

    /**
     * Determine whether the user can view any teams.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('read-teams');
    }

    /**
     * Determine whether the user can create teams.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create-teams');
    }

    /**
     * Determine whether the user can update the team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @return mixed
     */
    public function update(User $user, Team $team)
    {
        return $user->can('update-teams');
    }

    /**
     * Determine whether the user can delete the team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @return mixed
     */
    public function delete(User $user, Team $team)
    {
        return $user->can('delete-teams');
    }

    /**
     * Determine whether the user can restore the team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @return mixed
     */
    public function restore(User $user, Team $team)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @return mixed
     */
    public function forceDelete(User $user, Team $team)
    {
        return false;
    }

    /**
     * Determine whether the user can attach a user to a team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @param  \App\User  $userResource
     * @return mixed
     */
    public function attachUser(User $user, Team $team, User $userResource)
    {
        if ($team->members->contains('id', $userResource->id)) return false;
        if (! $team->visible && $user->cant('read-teams-hidden')) {
            return false;
        }
        return $user->can('update-teams-membership');
    }

    /**
     * Determine whether the user can attach a user to a team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @param  \App\User  $userResource
     * @return mixed
     */
    public function attachAnyUser(User $user, Team $team)
    {
        if (! $team->visible && $user->cant('read-teams-hidden')) {
            return false;
        }
        return $user->can('update-teams-membership');
    }

    /**
     * Determine whether the user can detach a user from a team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @param  \App\User  $userResource
     * @return mixed
     */
    public function detachUser(User $user, Team $team, User $userResource)
    {
        if (! $team->visible && $user->cant('read-teams-hidden')) {
            return false;
        }
        return $user->can('update-teams-membership');
    }
}
