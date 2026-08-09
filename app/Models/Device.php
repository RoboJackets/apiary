<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $serial_number
 * @property string $manufacturer
 * @property string $model
 * @property string $hardware_version
 * @property string $bluetooth_firmware_version
 * @property string $bluetooth_software_version
 * @property string $bootloader_version
 * @property string $application_version
 * @property int $battery_percentage
 * @property int $last_seen_user_id
 * @property \Illuminate\Support\Carbon $last_seen_at
 * @property string $last_seen_ip_address
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
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
        'manufacturer',
        'model',
        'hardware_version',
        'bluetooth_firmware_version',
        'bluetooth_software_version',
        'bootloader_version',
        'application_version',
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

    /**
     * Get the attendance records created with this device.
     *
     * @return HasMany<\App\Models\Attendance, \App\Models\Device>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'device_serial_number', 'serial_number');
    }
}
