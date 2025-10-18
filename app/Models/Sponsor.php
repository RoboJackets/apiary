<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * This model represents a sponsor entity in the application.
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $end_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Sponsor extends Model
{
    use HasFactory;
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
        'name',
        'end_date',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'end_date' => 'datetime',
        ];
    }

    /**
     * Get the active status of the sponsor.
     */
    public function active(): bool
    {
        return $this->end_date > now();
    }

    /**
     * Check if the given email is authorized for the sponsor.
     */
    public function domainNames(): HasMany
    {
        return $this->hasMany(SponsorDomain::class, 'sponsor_id');
    }

    /**
     * Return the users that have logged in under this sponsor.
     */
    public function users(): HasMany
    {
        return $this->hasMany(SponsorUser::class, 'sponsor_id');
    }
}
