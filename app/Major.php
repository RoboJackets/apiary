<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Major extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id',
        'deleted_at',
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
     *  Get the Users that are members of this Major.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->whereNull('major_user.deleted_at')->withTimestamps();
    }

    public static function findOrCreateFromGtadGroup(string $gtad_group): Major
    {
        $major = self::where('gtad_majorgroup_name', strtolower($gtad_group))->first();

        if (null === $major) {
            $exploded = explode('_', $gtad_group);

            $major = new Major();
            $major->gtad_majorgroup_name = strtolower($gtad_group);
            $major->whitepages_ou = strtoupper($exploded[0]);
            $major->school = strtoupper($exploded[1]);
            $major->save();
        }

        return $major;
    }
}
