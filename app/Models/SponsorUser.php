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
 * @property int|null $sponsor_id
 * @property string|null $email_suppression_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read bool $should_receive_email
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
     * If user ID requested for this SponsorUser, returns email instead.
     */
    public function getUidAttribute(): string
    {
        return $this->email;
    }

    public function getShouldReceiveEmailAttribute(): bool
    {
        return $this->email_suppression_reason === null;
    }
}
