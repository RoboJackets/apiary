<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a signed membership agreement.
 *
 * @property bool $complete Whether the agreement has been completed
 */
class Signature extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'membership_agreement_template_id',
        'user_id',
        'electronic',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'render_timestamp' => 'datetime',
        'redirect_to_cas_timestamp' => 'datetime',
        'cas_ticket_redeemed_timestamp' => 'datetime',
        'ip_address_location_estimate' => 'json',
        'electronic' => 'boolean',
        'complete' => 'boolean',
    ];

    public function membershipAgreementTemplate(): BelongsTo
    {
        return $this->belongsTo(MembershipAgreementTemplate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function hash(string $username, string $ipAddress, string $userAgent, Carbon $timestamp): string
    {
        return hash('sha256', strtolower($username).$ipAddress.$userAgent.$timestamp->toIso8601String());
    }
}
