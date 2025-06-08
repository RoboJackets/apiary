<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\AccessCardObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([AccessCardObserver::class])]
class AccessCard extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'access_card_number';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Get the user for this access card.
     *
     * @return BelongsTo<\App\Models\User, \App\Models\AccessCard>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attendance records associated with this access card.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Attendance, self>
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'access_card_number', 'access_card_number');
    }
}
