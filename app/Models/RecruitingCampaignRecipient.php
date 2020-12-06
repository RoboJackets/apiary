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
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient newModelQuery()
 * @method \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereEmailAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereNotifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereRecruitingCampaignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereRecruitingVisitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaignRecipient whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|RecruitingCampaignRecipient onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|RecruitingCampaignRecipient withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|RecruitingCampaignRecipient withTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $id
 * @property int $recruiting_campaign_id the RecruitingCampaign that maps to this recipient
 * @property int $recruiting_visit_id the RecruitingVisit that maps to this recipient
 * @property int $user_id the ID of the user, if available
 * @property string $email_address the email address to send to
 * @property string $notified_at the timestamp when this recipient was contacted
 * @property string $source the source of this recipient
 *
 * @property-read \App\Models\RecruitingCampaign $recruitingCampaign
 * @property-read \App\Models\RecruitingVisit $recruitingVisit
 * @property-read \App\Models\User $user
 * @property-read int|null $notifications_count
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
