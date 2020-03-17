<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A batch of emails sent to addresses collected from RecruitingVisits.
 *
 * @property int $created_by the user that created this campaign
 * @property string $status the status of this campaign
 * @property int $id the database identifier for this campaign
 */
class RecruitingCampaign extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id',
        'status',
    ];

    /**
     * Get the user that owns the campaign.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the template used in the campaign.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'notification_template_id');
    }

    /**
     * Get the recipients for this campaign.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(RecruitingCampaignRecipient::class);
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'creator' => 'users',
            'template' => 'notification-templates',
            'recipients' => 'recruiting-campaign-recipients',
        ];
    }
}
