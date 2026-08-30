<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResumePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->resume) {
            return true;
        }

        return $user->can('read-users-resume');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Resume $resume): bool
    {
        if ($resume->user->is($user)) {
            return true;
        }

        return $user->can('read-users-resume');
    }

    /**
     * Determine whether the user can create models.
     * Resumes are only created or updated when uploaded by their owners.
     *
     * @psalm-pure
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     * Resumes are only created or updated when uploaded by their owners.
     *
     * @psalm-pure
     */
    public function update(User $user, Resume $resume): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * Resumes are only created or updated when uploaded by their owners.
     *
     * @psalm-pure
     */
    public function delete(User $user, Resume $resume): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     * Resumes are only created or updated when uploaded by their owners.
     *
     * @psalm-pure
     */
    public function restore(User $user, Resume $resume): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Resumes are only created or updated when uploaded by their owners.
     *
     * @psalm-pure
     */
    public function forceDelete(User $user, Resume $resume): bool
    {
        return false;
    }

    /**
     * @psalm-pure
     */
    public function replicate(User $user, Resume $resume): bool
    {
        return false;
    }
}
