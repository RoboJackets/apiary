<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DuesTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DuesTransactionPolicy
{
    use HandlesAuthorization;

    public function view(User $user, DuesTransaction $resource): bool
    {
        return $user->can('read-dues-transactions');
    }

    public function viewAny(User $user): bool
    {
        return $user->can('read-dues-transactions');
    }

    public function create(User $user): bool
    {
        return $user->can('create-dues-transactions');
    }

    public function update(User $user, DuesTransaction $resource): bool
    {
        return $user->can('update-dues-transactions');
    }

    public function delete(User $user, DuesTransaction $resource): bool
    {
        return false;
    }

    public function restore(User $user, DuesTransaction $resource): bool
    {
        return false;
    }

    public function forceDelete(User $user, DuesTransaction $resource): bool
    {
        return false;
    }
}
