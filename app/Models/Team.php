<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,Generic.Commenting.DocComment.TagValueIndent

namespace App\Models;

use App\Traits\GetMorphClassStatic;
use Chelout\RelationshipEvents\Concerns\HasBelongsToManyEvents;
use Chelout\RelationshipEvents\Concerns\HasManyEvents;
use Chelout\RelationshipEvents\Traits\HasRelationshipObservables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Laravel\Nova\Actions\Actionable;

/**
 * Represents a group of Users.
 *
 * @property int $id
 * @property string $name
 * @property bool $self_serviceable
 * @property bool $visible
 * @property int $visible_on_kiosk
 * @property bool $attendable
 * @property string|null $slug
 * @property string|null $description
 * @property string|null $mailing_list_name
 * @property string|null $slack_channel_id
 * @property string|null $slack_channel_name
 * @property string|null $slack_private_channel_id
 * @property string|null $google_group
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $project_manager_id user_id of the project manager
 * @property \Illuminate\Database\Eloquent\Collection|array<\App\Models\RemoteAttendanceLink> $remoteAttendanceLinks
 * @property-read \App\Models\User|null $projectManager
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Laravel\Nova\Actions\ActionEvent> $actions
 * @property-read int|null $actions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Attendance> $attendance
 * @property-read int|null $attendance_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\User> $members
 * @property-read int|null $members_count
 * @property-read array<\Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read int|null $remote_attendance_links_count
 *
 * @method static Builder|Team attendable()
 * @method static Builder|Team newModelQuery()
 * @method static Builder|Team newQuery()
 * @method static \Illuminate\Database\Query\Builder|Team onlyTrashed()
 * @method static Builder|Team query()
 * @method static Builder|Team selfServiceable()
 * @method static Builder|Team visible()
 * @method static Builder|Team whereAttendable($value)
 * @method static Builder|Team whereCreatedAt($value)
 * @method static Builder|Team whereDeletedAt($value)
 * @method static Builder|Team whereDescription($value)
 * @method static Builder|Team whereGoogleGroup($value)
 * @method static Builder|Team whereId($value)
 * @method static Builder|Team whereMailingListName($value)
 * @method static Builder|Team whereName($value)
 * @method static Builder|Team whereProjectManagerId($value)
 * @method static Builder|Team whereSelfServiceable($value)
 * @method static Builder|Team whereSlackChannelId($value)
 * @method static Builder|Team whereSlackChannelName($value)
 * @method static Builder|Team whereSlackPrivateChannelId($value)
 * @method static Builder|Team whereSlug($value)
 * @method static Builder|Team whereUpdatedAt($value)
 * @method static Builder|Team whereVisible($value)
 * @method static Builder|Team whereVisibleOnKiosk($value)
 * @method static \Illuminate\Database\Query\Builder|Team withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Team withoutTrashed()
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class Team extends Model
{
    use Actionable;
    use GetMorphClassStatic;
    use HasBelongsToManyEvents;
    use HasManyEvents;
    use HasRelationshipObservables;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'created_at',
        'deleted_at',
        'id',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'attendable' => 'boolean',
        'self_serviceable' => 'boolean',
        'visible' => 'boolean',
    ];

    /**
     * The rules to use for ranking results in Meilisearch.
     *
     * @var array<string>
     */
    public $ranking_rules = [
        'desc(attendance_count)',
    ];

    /**
     * The attributes that can be used for filtering in Meilisearch.
     *
     * @var array<string>
     */
    public $filterable_attributes = [
        'user_id',
    ];

    /**
     *  Get the Users that are members of this Team.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Get all of the team's attendance.
     */
    public function attendance(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    /**
     * Get all of the event's remote attendance links.
     */
    public function remoteAttendanceLinks(): MorphMany
    {
        return $this->morphMany(RemoteAttendanceLink::class, 'attendable');
    }

    /**
     * Scope a query to only include attendable teams.
     */
    public function scopeAttendable(Builder $query): Builder
    {
        return $query->where('attendable', true);
    }

    /**
     * Scope a query to only include visible teams.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('visible', true);
    }

    /**
     * Scope a query to only include self-serviceable teams.
     */
    public function scopeSelfServiceable(Builder $query): Builder
    {
        return $query->where('self_serviceable', true);
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'members' => 'teams-membership',
            'attendance' => 'attendance',
        ];
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    /**
     * Route notifications for the Slack channel.
     */
    public function routeNotificationForSlack(Notification $notification): ?string
    {
        return config('services.team_slack_webhook_url');
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('projectManager')->withCount('attendance');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string,int|string>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        if (! array_key_exists('project_manager', $array) && null !== $this->projectManager) {
            $array['project_manager'] = $this->projectManager->toArray();
        }

        if (! array_key_exists('attendance_count', $array)) {
            $array['attendance_count'] = $this->attendance()->count();
        }

        $array['user_id'] = $this->members->modelKeys();

        return $array;
    }
}
