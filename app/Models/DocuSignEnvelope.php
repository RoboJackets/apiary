<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

namespace App\Models;

use App\Observers\DocuSignEnvelopeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A DocuSign envelope.
 *
 * @property int $id
 * @property int $signed_by
 * @property string|null $url
 * @property string|null $envelope_id
 * @property string $signable_type
 * @property int $signable_id
 * @property bool $complete
 * @property string|null $membership_agreement_filename
 * @property string|null $travel_authority_filename
 * @property string|null $direct_bill_airfare_filename
 * @property string|null $covid_risk_filename
 * @property string|null $itinerary_request_filename
 * @property string|null $summary_filename
 * @property string|null $signer_ip_address
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $viewed_at
 * @property \Illuminate\Support\Carbon|null $signed_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $sent_by
 * @property-read string|null $sender_view_url
 * @property-read \App\Models\User|null $sentBy
 * @property-read \App\Models\Signature|\App\Models\TravelAssignment $signable
 * @property-read \App\Models\User $signedBy
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope newQuery()
 * @method static \Illuminate\Database\Query\Builder|DocuSignEnvelope onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope query()
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereComplete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereCovidRiskFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereDirectBillAirfareFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereEnvelopeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereMembershipAgreementFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereSentBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereSignableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereSignableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereSignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereSignedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereSignerIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereSummaryFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereTravelAuthorityFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereViewedAt($value)
 * @method static \Illuminate\Database\Query\Builder|DocuSignEnvelope withTrashed()
 * @method static \Illuminate\Database\Query\Builder|DocuSignEnvelope withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property bool $acknowledgement_sent
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignEnvelope whereAcknowledgementSent($value)
 */
#[ObservedBy([DocuSignEnvelopeObserver::class])]
class DocuSignEnvelope extends Model
{
    use SoftDeletes;

    /**
     * The name of the database table for this model.
     *
     * @var string
     */
    protected $table = 'docusign_envelopes';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'complete' => 'boolean',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'signed_at' => 'datetime',
            'completed_at' => 'datetime',
            'acknowledgement_sent' => 'boolean',
        ];
    }

    /**
     * Get the owning model.
     *
     * @return MorphTo<Signature|TravelAssignment,DocuSignEnvelope>
     */
    public function signable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the User that signed the envelope.
     *
     * @return BelongsTo<User,DocuSignEnvelope>
     */
    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    /**
     * Get the User that sent the envelope.
     *
     * @return BelongsTo<User,DocuSignEnvelope>
     */
    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function getSenderViewUrlAttribute(): ?string
    {
        if ($this->envelope_id === null) {
            return null;
        }

        if (config('docusign.api_base_path') === 'https://demo.docusign.net/restapi') {
            $hostname = 'https://appdemo.docusign.com';
        } else {
            $hostname = 'https://app.docusign.com';
        }

        if (Str::contains($this->envelope_id, '-')) {
            return $hostname.'/documents/details/'.$this->envelope_id;
        } else {
            return Str::lower(
                $hostname.'/documents/details/'.
                Str::substr($this->envelope_id, 0, 8).'-'.
                Str::substr($this->envelope_id, 8, 4).'-'.
                Str::substr($this->envelope_id, 12, 4).'-'.
                Str::substr($this->envelope_id, 16, 4).'-'.
                Str::substr($this->envelope_id, 20, 12)
            );
        }
    }
}
