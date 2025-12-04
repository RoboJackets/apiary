<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;

/**
 * This model represents a sponsor entity in the application.
 *
 * @property int $id
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class SponsorUser extends Authenticatable
{
    use HasFactory;
    use HasOneTimePasswords;
    use Notifiable;
    use Searchable;
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'email',
    ];

    /**
     * Get the sponsor company that this SponsorUser belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }

    /**
     * Get a calculated uid based on the user portion of the sponsor's email.
     */
    public function getUidAttribute(): string
    {
        return explode('@', (string) $this->email, 2)[0];
    }
}
