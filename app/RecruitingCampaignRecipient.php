<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * Represents a non-member who will receive an email invitation for General Interest.
 *
 * @property string $email_address the email address to send to
 * @property string $source the source of this recipient
 * @property int $recruiting_visit_id the RecruitingVisit that maps to this recipient
 * @property int $recruiting_campaign_id the RecruitingCampaign that maps to this recipient
 * @property int $user_id the ID of the user, if available
 * @property string $notified_at the timestamp when this recipient was contacted
 */
class RecruitingCampaignRecipient extends Model
{
    use SoftDeletes;
    use Notifiable;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the user that owns the phone.
     */
    public function recruitingCampaign(): BelongsTo
    {
        return $this->belongsTo(RecruitingCampaign::class);
    }

    /**
     * Get the user that owns the phone.
     */
    public function recruitingVisit(): BelongsTo
    {
        return $this->belongsTo(RecruitingVisit::class);
    }

    /**
     * Get the user that is related to the model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail(): string
    {
        return $this->email_address;
    }

    /**
     * Get the visit token for the model.
     */
    public function getVisitToken(): string
    {
        return $this->recruitingVisit->visit_token;
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'recruitingCampaign' => 'recruiting-campaigns',
            'recruitingVisit' => 'recruiting-visits',
            'user' => 'users',
        ];
    }
}
