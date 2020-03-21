<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];


    /**
     *  Get the Users that are members of this ClassStanding.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->whereNull('class_standing_user.deleted_at')->withTimestamps();
    }

    public static function findOrCreateFromName(string $name): self
    {
        $major = self::where('name', strtolower($name))->first();

        if (null === $major) {
            $major = new self();
            $major->name = strtolower($name);
            $major->save();
        }

        return $major;
    }
}
