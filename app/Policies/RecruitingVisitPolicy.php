<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecruitingVisitPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the recruiting visit.
     *
     * @param  \App\User  $user
     * @param  \App\RecruitingVisit  $resource
     * @return mixed
     */
    public function view(User $user, RecruitingVisit $resource)
    {
        return $user->can('read-recruiting-visits');
    }

    /**
     * Determine whether the user can view any recruiting visits.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('read-recruiting-visits');
    }

    /**
     * Determine whether the user can create recruiting visits.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false; // not in nova
    }

    /**
     * Determine whether the user can update the recruiting visit.
     *
     * @param  \App\User  $user
     * @param  \App\RecruitingVisit  $resource
     * @return mixed
     */
    public function update(User $user, RecruitingVisit $resource)
    {
        return $user->can('update-recruiting-visits');
    }

    /**
     * Determine whether the user can delete the recruiting visit.
     *
     * @param  \App\User  $user
     * @param  \App\RecruitingVisit  $resource
     * @return mixed
     */
    public function delete(User $user, RecruitingVisit $resource)
    {
        return $user->can('delete-recruiting-visits');
    }

    /**
     * Determine whether the user can restore the recruiting visit.
     *
     * @param  \App\User  $user
     * @param  \App\RecruitingVisit  $resource
     * @return mixed
     */
    public function restore(User $user, RecruitingVisit $resource)
    {
        return $user->can('delete-recruiting-visits');
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param  \App\User  $user
     * @param  \App\RecruitingVisit  $resource
     * @return mixed
     */
    public function forceDelete(User $user, RecruitingVisit $resource)
    {
        return false;
    }
}
