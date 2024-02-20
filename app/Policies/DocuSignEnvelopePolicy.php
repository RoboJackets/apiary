<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DocuSignEnvelope;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocuSignEnvelopePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-docusign-envelopes') ||
            Travel::where('primary_contact_user_id', $user->id)
                ->whereIn('status', ['approved', 'complete'])
                ->exists() ||
            DocuSignEnvelope::where('sent_by', $user->id)->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DocuSignEnvelope $docuSignEnvelope): bool
    {
        return $user->can('view-docusign-envelopes') ||
            $docuSignEnvelope->sent_by === $user->id ||
            (
                $docuSignEnvelope->signable_type === TravelAssignment::getMorphClassStatic() &&
                $docuSignEnvelope->signable->travel->primaryContact->id === $user->id
            );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DocuSignEnvelope $docuSignEnvelope): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DocuSignEnvelope $docuSignEnvelope): bool
    {
        return $user->hasRole('admin') ||
            $docuSignEnvelope->sent_by === $user->id ||
            (
                $docuSignEnvelope->signable_type === TravelAssignment::getMorphClassStatic() &&
                $docuSignEnvelope->signable->travel->primaryContact->id === $user->id
            );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DocuSignEnvelope $docuSignEnvelope): bool
    {
        return $this->delete($user, $docuSignEnvelope);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DocuSignEnvelope $docuSignEnvelope): bool
    {
        return false;
    }

    public function replicate(User $user, DocuSignEnvelope $docuSignEnvelope): bool
    {
        return false;
    }
}
