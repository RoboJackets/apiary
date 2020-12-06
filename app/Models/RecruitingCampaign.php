<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A batch of emails sent to addresses collected from RecruitingVisits.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaign newModelQuery()
 * @method \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaign query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaign whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaign whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaign whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaign whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaign whereNotificationTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaign whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaign whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingCampaign whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|RecruitingCampaign onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|RecruitingCampaign withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|RecruitingCampaign withTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $created_by the user that created this campaign
 * @property int $id the database identifier for this campaign
 * @property int|null $notification_template_id
 * @property string $end_date
 * @property string $name
 * @property string $start_date
 * @property string $status the status of this campaign
 *
 * @property-read \App\NotificationTemplate $template
 * @property-read \App\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\RecruitingCampaignRecipient> $recipients
 * @property-read int|null $recipients_count
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
