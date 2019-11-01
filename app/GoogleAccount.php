<?php

declare(strict_types=1);

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GoogleAccount extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at', 'email_address',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(\App\User::class)->withTimestamps();
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'users' => 'users',
        ];
    }

    /**
     * Get whether this GoogleAccount is in the RoboJackets G Suite.
     *
     * @return bool
     */
    public function getIsGSuiteAttribute(): bool
    {
        return Str::endsWith($this->email_address, '@robojackets.org');
    }
}
