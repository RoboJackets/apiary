<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the attendance.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        return $user->can('read-attendance');
    }

    /**
     * Determine whether the user can view any attendance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-attendance');
    }

    /**
     * Determine whether the user can create attendances.
     */
    public function create(User $user): bool
    {
        // Attendance should be created in the old admin interface for now.
        return false;
    }

    /**
     * Determine whether the user can update the attendance.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the attendance.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->can('delete-attendance');
    }

    /**
     * Determine whether the user can restore the attendance.
     */
    public function restore(User $user, Attendance $attendance): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the attendance.
     */
    public function forceDelete(User $user, Attendance $attendance): bool
    {
        return false;
    }

    public function replicate(User $user, Attendance $attendance): bool
    {
        return false;
    }
}
