<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DuesTransactionMerchandise extends Pivot
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $casts = [
        'provided_at' => 'datetime',
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    public function providedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provided_by');
    }

    /**
     * Returns the name field of the User in providedBy. This exists because Nova was not cooperating.
     */
    public function getProvidedByNameAttribute(): ?string
    {
        return optional($this->providedBy)->name;
    }
}
