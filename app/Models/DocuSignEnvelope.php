<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'complete' => 'boolean',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'signed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

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
        if (null === $this->envelope_id) {
            return null;
        }

        return Str::lower(
            'https://app.docusign.com/documents/details/'.
                Str::substr($this->envelope_id, 0, 8).'-'.
                Str::substr($this->envelope_id, 8, 4).'-'.
                Str::substr($this->envelope_id, 12, 4).'-'.
                Str::substr($this->envelope_id, 16, 4).'-'.
                Str::substr($this->envelope_id, 20, 12)
        );
    }
}
