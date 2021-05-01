<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * Represents a non-member who will receive an email invitation for General Interest.
 *
 * @property      int $id
 * @property      string $email_address
 * @property      string $source
 * @property      int $recruiting_campaign_id
 * @property      int|null $recruiting_visit_id
 * @property      int|null $user_id
 * @property      string|null $notified_at
 * @property      \Illuminate\Support\Carbon|null $created_at
 * @property      \Illuminate\Support\Carbon|null $updated_at
 * @property      \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|array<\Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\RecruitingCampaign $recruitingCampaign
 * @property-read \App\Models\RecruitingVisit|null $recruitingVisit
 * @property-read \App\Models\User|null $user
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient newModelQuery()
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient newQuery()
 * @method        static \Illuminate\Database\Query\Builder|RecruitingCampaignRecipient onlyTrashed()
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient query()
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereCreatedAt($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereDeletedAt($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereEmailAddress($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereId($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereNotifiedAt($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereRecruitingCampaignId($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereRecruitingVisitId($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereSource($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereUpdatedAt($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereUserId($value)
 * @method        static \Illuminate\Database\Query\Builder|RecruitingCampaignRecipient withTrashed()
 * @method        static \Illuminate\Database\Query\Builder|RecruitingCampaignRecipient withoutTrashed()
 * @mixin         \Eloquent
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
