<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SponsorUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SponsorUserPolicy
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
    public function view(User $user, SponsorUser $sponsorUser): bool
    {
        return $user->can('view-sponsors');
    }

    /**
     * Determine whether the user can create models.
     *
     * @psalm-pure
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @psalm-pure
     */
    public function update(User $user, SponsorUser $sponsorUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SponsorUser $sponsorUser): bool
    {
        return $user->can('manage-sponsors');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SponsorUser $sponsorUser): bool
    {
        return $user->can('manage-sponsors');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @psalm-pure
     */
    public function forceDelete(User $user, SponsorUser $sponsorUser): bool
    {
        return false;
    }

    /**
     * @psalm-pure
     */
    public function replicate(User $user, SponsorUser $sponsorUser): bool
    {
        return false;
    }
}
