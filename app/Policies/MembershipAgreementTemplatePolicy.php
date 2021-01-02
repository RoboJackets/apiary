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
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MembershipAgreementTemplate $membershipAgreementTemplate): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-membership-agreement-templates');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MembershipAgreementTemplate $membershipAgreementTemplate): bool
    {
        return $user->can('update-membership-agreement-templates')
            && 0 === $membershipAgreementTemplate->signatures()->count();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MembershipAgreementTemplate $membershipAgreementTemplate): bool
    {
        return $user->can('update-membership-agreement-templates')
            && 0 === $membershipAgreementTemplate->signatures()->count();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MembershipAgreementTemplate $membershipAgreementTemplate): bool
    {
        return $user->can('update-membership-agreement-templates');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MembershipAgreementTemplate $membershipAgreementTemplate): bool
    {
        return false;
    }
}
