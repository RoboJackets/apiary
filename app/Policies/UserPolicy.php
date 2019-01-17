<?php

namespace App\Policies;

use App\Team;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the user.
     *
     * @param  \App\User  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(User $user, User $userResource)
    {
        return $user->can('read-users');
    }

    /**
     * Determine whether the user can view any users.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('read-users');
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create-users');
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  \App\User  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function update(User $user, User $userResource)
    {
        return $user->can('update-users');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\User  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(User $user, User $userResource)
    {
        return $user->can('delete-users');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\User  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function restore(User $user, User $userResource)
    {
        return $user->can('create-users');
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param  \App\User  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function forceDelete(User $user, User $userResource)
    {
        return false;
    }

    /**
     * Determine whether the user can attach a team to a user.
     *
     * @param  \App\User  $user
     * @param  \App\User  $userResource
     * @param  \App\Team  $team
     * @return mixed
     */
    public function attachTeam(User $user, User $userResource, Team $team)
    {
        if ($team->members->contains('id', $userResource->id)) {
            return false;
        }
        if (! $team->visible && $user->cant('read-teams-hidden')) {
            return false;
        }

        return $user->can('update-teams-membership');
    }

    /**
     * Determine whether the user can attach a team to a user.
     *
     * @param  \App\User  $user
     * @param  \App\User  $userResource
     * @param  \App\Team  $team
     * @return mixed
     */
    public function attachAnyTeam(User $user, User $userResource)
    {
        return $user->can('update-teams-membership');
    }

    /**
     * Determine whether the user can detach a team from a user.
     *
     * @param  \App\User  $user
     * @param  \App\User  $userResource
     * @param  \App\Team  $team
     * @return mixed
     */
    public function detachTeam(User $user, User $userResource, Team $team)
    {
        if (! $team->visible && $user->cant('read-teams-hidden')) {
            return false;
        }

        return $user->can('update-teams-membership');
    }
}
