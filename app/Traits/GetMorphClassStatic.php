<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * This is a copy of a function that Laravel provides dynamically
 */
trait GetMorphClassStatic
{
    public static function getMorphClassStatic(): string
    {
        return array_search(static::class, Relation::morphMap(), true);
    }
}
