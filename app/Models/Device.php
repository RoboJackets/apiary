<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    /** @use HasFactory<\Database\Factories\DeviceFactory> */
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'serial_number';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'serial_number',
        'hardware_version',
        'software_version',
        'firmware_version',
        'battery_percentage',
        'last_seen_user_id',
        'last_seen_at',
        'last_seen_ip_address',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     *
     * @psalm-pure
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'serial_number' => 'integer',
            'battery_percentage' => 'integer',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Get the user who was most recently seen with this device.
     *
     * @return BelongsTo<\App\Models\User, \App\Models\Device>
     */
    public function lastSeenUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_seen_user_id');
    }
}
