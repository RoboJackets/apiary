<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MembershipAgreementTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MembershipAgreementTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MembershipAgreementTemplate $template): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MembershipAgreementTemplate $template): bool
    {
        return $user->hasRole('admin')
            && $template->signatures()->count() === 0;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MembershipAgreementTemplate $template): bool
    {
        return $user->hasRole('admin')
            && $template->signatures()->count() === 0;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MembershipAgreementTemplate $template): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MembershipAgreementTemplate $template): bool
    {
        return false;
    }

    public function replicate(User $user, MembershipAgreementTemplate $template): bool
    {
        return false;
    }
}
