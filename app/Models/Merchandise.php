<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents one option of merch/swag related to a fiscal year and its dues packages.
 */
class Merchandise extends Model
{
    use SoftDeletes;

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(DuesPackage::class)->withPivot('group')->withTimestamps();
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(DuesTransaction::class)
            ->withPivot(['provided_at', 'provided_by'])
            ->withTimestamps();
    }
}
