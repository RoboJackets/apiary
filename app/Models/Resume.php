<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * This model references a user's resume with a filepath.
 * Also allows resumes to be indexed for search.
 *
 * @property int $id
 * @property int $user_id
 * @property string $filepath
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $last_uploaded_at
 * @property-read \App\Models\User $user
 * @property-read string $filename
 */
class Resume extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'filepath',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_uploaded_at' => 'datetime',
    ];

    /**
     * Get the user that this resume belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Gets the filename from the filepath.
     *
     * @psalm-mutation-free
     */
    public function getFilenameAttribute(): string
    {
        return basename($this->filepath);
    }

    /**
     * Scopes queries to only include resumes with active users.
     */
    public function scopeUserIsActive(Builder $query): Builder
    {
        return $query->whereHas('user', static function (Builder $q): void {
            $q->active();
        });
    }
}
