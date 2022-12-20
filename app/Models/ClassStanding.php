<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a class standing (e.g. freshman).
 *
 * @property int $id
 * @property string $name
 * @property int|null $rank_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\User> $members
 * @property-read int|null $members_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ClassStanding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassStanding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassStanding query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassStanding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassStanding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassStanding whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassStanding whereRankOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassStanding whereUpdatedAt($value)
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class ClassStanding extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the Users that are members of this ClassStanding.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\User>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->whereNull('class_standing_user.deleted_at')->withTimestamps();
    }

    public static function findOrCreateFromName(string $name): self
    {
        $classStanding = self::where('name', strtolower($name))->first();

        if ($classStanding === null) {
            $classStanding = new self();
            $classStanding->name = strtolower($name);
            $classStanding->save();
        }

        return $classStanding;
    }
}
