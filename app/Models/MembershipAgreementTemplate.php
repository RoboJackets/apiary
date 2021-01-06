<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipAgreementTemplate extends Model
{
    use SoftDeletes;

    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class);
    }

    public function renderForUser(User $user): string
    {
        return str_replace(
            [
                '{{ $user->first_name }}',
                '{{ $user->last_name }}',
            ],
            [
                $user->first_name,
                $user->last_name,
            ],
            $this->text
        );
    }
}
