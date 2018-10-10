<?php

namespace App\Policies;

use App\User;
use App\Rsvp;
use Illuminate\Auth\Access\HandlesAuthorization;

class RsvpPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the rsvp.
     *
     * @param  \App\User  $user
     * @param  \App\Rsvp  $rsvp
     * @return mixed
     */
    public function view(User $user, Rsvp $rsvp)
    {
        return $user->can('read-rsvps');
    }

    /**
     * Determine whether the user can create rsvps.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the rsvp.
     *
     * @param  \App\User  $user
     * @param  \App\Rsvp  $rsvp
     * @return mixed
     */
    public function update(User $user, Rsvp $rsvp)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the rsvp.
     *
     * @param  \App\User  $user
     * @param  \App\Rsvp  $rsvp
     * @return mixed
     */
    public function delete(User $user, Rsvp $rsvp)
    {
        return $user->can('delete-rsvps');
    }

    /**
     * Determine whether the user can restore the rsvp.
     *
     * @param  \App\User  $user
     * @param  \App\Rsvp  $rsvp
     * @return mixed
     */
    public function restore(User $user, Rsvp $rsvp)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the rsvp.
     *
     * @param  \App\User  $user
     * @param  \App\Rsvp  $rsvp
     * @return mixed
     */
    public function forceDelete(User $user, Rsvp $rsvp)
    {
        return false;
    }
}
