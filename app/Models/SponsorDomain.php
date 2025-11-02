<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

/**
 * This model represents a sponsor entity in the application.
 *
 * @property int $id
 * @property string $domain_name
 * @property int $sponsor_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class SponsorDomain extends Model
{
    use HasFactory;
    use Searchable;
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'domain_name',
        'sponsor_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the sponsor that owns the domain.
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }

    /**
     * Checks whether a given email is associated with a Sponsor.
     */
    public static function sponsorEmail(string $email): bool
    {
        $domain = substr(strrchr($email, '@'), 1);

        return self::where('domain_name', $domain)->exists();
    }
}
