<?php

declare(strict_types=1);

namespace App\Policies;

use App\AttendanceExport;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendanceExportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the attendance export.
     */
    public function view(User $user, AttendanceExport $attendanceExport): bool
    {
        return $user->can('read-attendance');
    }

    /**
     * Determine whether the user can view any attendance export.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-attendance');
    }

    /**
     * Determine whether the user can create attendance exports.
     */
    public function create(User $user): bool
    {
        // Attendance exports should be created with the Nova action or the weekly job.
        return false;
    }

    /**
     * Determine whether the user can update the attendance export.
     */
    public function update(User $user, AttendanceExport $attendanceExport): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the attendance export.
     */
    public function delete(User $user, AttendanceExport $attendanceExport): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the attendance export.
     */
    public function restore(User $user, AttendanceExport $attendanceExport): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the attendance export.
     */
    public function forceDelete(User $user, AttendanceExport $attendanceExport): bool
    {
        return false;
    }
}
