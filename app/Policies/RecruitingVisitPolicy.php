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
     */
    public function view(User $user, RecruitingVisit $resource): bool
    {
        return $user->can('read-recruiting-visits');
    }

    /**
     * Determine whether the user can view any recruiting visits.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-recruiting-visits');
    }

    /**
     * Determine whether the user can create recruiting visits.
     */
    public function create(User $user): bool
    {
        return false; // not in nova
    }

    /**
     * Determine whether the user can update the recruiting visit.
     */
    public function update(User $user, RecruitingVisit $resource): bool
    {
        return $user->can('update-recruiting-visits');
    }

    /**
     * Determine whether the user can delete the recruiting visit.
     */
    public function delete(User $user, RecruitingVisit $resource): bool
    {
        return $user->can('delete-recruiting-visits');
    }

    /**
     * Determine whether the user can restore the recruiting visit.
     */
    public function restore(User $user, RecruitingVisit $resource): bool
    {
        return $user->can('delete-recruiting-visits');
    }

    /**
     * Determine whether the user can permanently delete the user.
     */
    public function forceDelete(User $user, RecruitingVisit $resource): bool
    {
        return false;
    }
}
