<?php

declare(strict_types=1);

namespace App;

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
}
