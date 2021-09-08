<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a signed membership agreement.
 *
 * @property bool $electronic
 * @property bool $complete
 * @property string $ip_address
 * @property object $ip_address_location_estimate
 * @property \App\Models\MembershipAgreementTemplate $membershipAgreementTemplate
 * @property \App\Models\User $user
 * @property int $id
 * @property int $membership_agreement_template_id
 * @property int $user_id
 * @property int|null $uploaded_by
 * @property string|null $scanned_agreement
 * @property string|null $cas_host
 * @property string|null $cas_service_url_hash
 * @property string|null $cas_ticket
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $render_timestamp
 * @property \Illuminate\Support\Carbon|null $redirect_to_cas_timestamp
 * @property \Illuminate\Support\Carbon|null $cas_ticket_redeemed_timestamp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $upload_timestamp
 * @property-read \App\Models\User|null $uploadedBy
 * @method static \Illuminate\Database\Eloquent\Builder|Signature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Signature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Signature query()
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereCasHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereCasServiceUrlHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereCasTicket($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereCasTicketRedeemedTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereComplete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereElectronic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereIpAddressLocationEstimate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereMembershipAgreementTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereRedirectToCasTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereRenderTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereScannedAgreement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Signature whereUploadTimestamp($value)
 * @mixin         \Barryvdh\LaravelIdeHelper\Eloquent
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
