<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Payment $payment): bool
    {
        return $user->can('read-payments');
    }

    public function viewAny(User $user): bool
    {
        return $user->can('read-payments');
    }

    /**
     * @psalm-pure
     */
    public function create(User $user): bool
    {
        return false; // not manually
    }

    /**
     * @psalm-pure
     */
    public function update(User $user, Payment $payment): bool
    {
        return false; // not manually
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->can('delete-payments');
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->can('delete-payments');
    }

    /**
     * @psalm-pure
     */
    public function forceDelete(User $user, Payment $payment): bool
    {
        return false;
    }

    /**
     * @psalm-pure
     */
    public function replicate(User $user, Payment $payment): bool
    {
        return false;
    }
}
