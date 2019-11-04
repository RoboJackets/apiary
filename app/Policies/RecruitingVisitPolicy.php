<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Policies;

use App\RecruitingVisit;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecruitingVisitPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the recruiting visit.
     *
     * @param \App\User  $user
     * @param \App\RecruitingVisit  $resource
     *
     * @return bool
     */
    public function view(User $user, RecruitingVisit $resource): bool
    {
        return $user->can('read-recruiting-visits');
    }

    /**
     * Determine whether the user can view any recruiting visits.
     *
     * @param \App\User  $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-recruiting-visits');
    }

    /**
     * Determine whether the user can create recruiting visits.
     *
     * @param \App\User  $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return false; // not in nova
    }

    /**
     * Determine whether the user can update the recruiting visit.
     *
     * @param \App\User  $user
     * @param \App\RecruitingVisit  $resource
     *
     * @return bool
     */
    public function update(User $user, RecruitingVisit $resource): bool
    {
        return $user->can('update-recruiting-visits');
    }

    /**
     * Determine whether the user can delete the recruiting visit.
     *
     * @param \App\User  $user
     * @param \App\RecruitingVisit  $resource
     *
     * @return bool
     */
    public function delete(User $user, RecruitingVisit $resource): bool
    {
        return $user->can('delete-recruiting-visits');
    }

    /**
     * Determine whether the user can restore the recruiting visit.
     *
     * @param \App\User  $user
     * @param \App\RecruitingVisit  $resource
     *
     * @return bool
     */
    public function restore(User $user, RecruitingVisit $resource): bool
    {
        return $user->can('delete-recruiting-visits');
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param \App\User  $user
     * @param \App\RecruitingVisit  $resource
     *
     * @return bool
     */
    public function forceDelete(User $user, RecruitingVisit $resource): bool
    {
        return false;
    }
}
