<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GetMorphClassStatic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Represents a signed membership agreement.
 *
 * @property int $id
 * @property int $membership_agreement_template_id
 * @property int $user_id
 * @property int|null $uploaded_by
 * @property string|null $scanned_agreement
 * @property bool $electronic
 * @property string|null $cas_host
 * @property string|null $cas_service_url_hash
 * @property string|null $cas_ticket
 * @property string|null $ip_address
 * @property array|null $ip_address_location_estimate
 * @property string|null $user_agent
 * @property bool $complete
 * @property \Illuminate\Support\Carbon|null $render_timestamp
 * @property \Illuminate\Support\Carbon|null $redirect_to_cas_timestamp
 * @property \Illuminate\Support\Carbon|null $cas_ticket_redeemed_timestamp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DocuSignEnvelope> $envelope
 * @property-read int|null $envelope_count
 * @property-read \App\Models\MembershipAgreementTemplate $membershipAgreementTemplate
 * @property-read \App\Models\User|null $uploadedBy
 * @property-read \App\Models\User $user
 *
 * @method static \Database\Factories\SignatureFactory factory(...$parameters)
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
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class Signature extends Model
{
    use GetMorphClassStatic;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'membership_agreement_template_id',
        'user_id',
        'electronic',
        'complete',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'render_timestamp' => 'datetime',
            'redirect_to_cas_timestamp' => 'datetime',
            'cas_ticket_redeemed_timestamp' => 'datetime',
            'ip_address_location_estimate' => 'json',
            'electronic' => 'boolean',
            'complete' => 'boolean',
        ];
    }

    /**
     * Get the template for this signature.
     *
     * @return BelongsTo<MembershipAgreementTemplate, Signature>
     */
    public function membershipAgreementTemplate(): BelongsTo
    {
        return $this->belongsTo(MembershipAgreementTemplate::class);
    }

    /**
     * Get the user for this signature.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Signature>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that uploaded this signature (for paper signatures).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Signature>
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the DocuSign envelope for this signature.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\DocuSignEnvelope>
     */
    public function envelope(): MorphMany
    {
        return $this->morphMany(DocuSignEnvelope::class, 'signable');
    }

    public static function hash(string $username, string $ipAddress, string $userAgent, Carbon $timestamp): string
    {
        return hash('sha256', strtolower($username).$ipAddress.$userAgent.$timestamp->toIso8601String());
    }
}
