<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SponsorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-sponsors');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sponsor $sponsor): bool
    {
        return $user->can('view-sponsors');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('manage-sponsors');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sponsor $sponsor): bool
    {
        return $user->can('manage-sponsors');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sponsor $sponsor): bool
    {
        return $user->can('manage-sponsors');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Sponsor $sponsor): bool
    {
        return $user->can('manage-sponsors');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Sponsor $sponsor): bool
    {
        return false;
    }

    public function replicate(User $user, Sponsor $sponsor): bool
    {
        return false;
    }
}
