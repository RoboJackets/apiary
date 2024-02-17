<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TravelAssignmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TravelAssignment $assignment): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('manage-travel') ||
            Travel::where('primary_contact_user_id', '=', $user->id)
                ->where('status', '=', 'draft')
                ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TravelAssignment $assignment): bool
    {
        return $assignment->travel->needs_airfare_form && (
            $user->hasRole('admin') || (
                $assignment->travel->status === 'draft' &&
                (
                    $user->can('manage-travel') ||
                    $assignment->travel->primaryContact === $user
                )
            )
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TravelAssignment $assignment): bool
    {
        return $user->hasRole('admin') || (
            $assignment->travel->status === 'draft' &&
            (
                $user->can('manage-travel') ||
                $assignment->travel->primaryContact === $user
            )
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TravelAssignment $assignment): bool
    {
        return $user->hasRole('admin') || (
            $assignment->travel->status === 'draft' &&
            (
                $user->can('manage-travel') ||
                $assignment->travel->primaryContact === $user
            )
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TravelAssignment $assignment): bool
    {
        return false;
    }

    public function replicate(User $user, TravelAssignment $assignment): bool
    {
        return false;
    }
}
