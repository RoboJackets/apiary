<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

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
 * @method static \Illuminate\Database\Eloquent\Builder visible() Scopes a query to only visible teams
 * @method static \Illuminate\Database\Query\Builder|Team onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|Team withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|Team withTrashed()
 * @method static Builder|Team attendable()
 * @method static Builder|Team newModelQuery()
 * @method \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static Builder|Team query()
 * @method static Builder|Team selfServiceable()
 * @method static Builder|Team whereAttendanceSecret($value)
 * @method static Builder|Team whereAttendanceSecretExpiration($value)
 * @method static Builder|Team whereCreatedAt($value)
 * @method static Builder|Team whereDeletedAt($value)
 * @method static Builder|Team whereDescription($value)
 * @method static Builder|Team whereGoogleGroup($value)
 * @method static Builder|Team whereId($value)
 * @method static Builder|Team whereMailingListName($value)
 * @method static Builder|Team whereName($value)
 * @method static Builder|Team whereProjectManagerId($value)
 * @method static Builder|Team whereSlackChannelId($value)
 * @method static Builder|Team whereSlackChannelName($value)
 * @method static Builder|Team whereSlackPrivateChannelId($value)
 * @method static Builder|Team whereUpdatedAt($value)
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property \Illuminate\Support\Carbon|null $attendance_secret_expiration
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property bool $self_serviceable whether this team can be joined/left voluntarily
 * @property bool $visible whether this team is visible to non-admins
 * @property int $id the database identifier for this team
 * @property int|null $project_manager_id
 * @property string $name The name of the team
 * @property string $slack_private_channel_id the slack internal ID of the team's private channel
 * @property string|null $attendance_secret
 * @property string|null $description
 * @property string|null $google_group
 * @property string|null $mailing_list_name
 * @property string|null $slack_channel_id
 * @property string|null $slack_channel_name
 *
 * @property-read \App\Models\User $projectManager
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Attendance> $attendance
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\User> $members
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Laravel\Nova\Actions\ActionEvent> $actions
 * @property-read int|null $actions_count
 * @property-read int|null $attendance_count
 * @property-read int|null $members_count
 * @property-read int|null $notifications_count
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
        'attendance_secret',
        'attendance_secret_expiration',
        'created_at',
        'deleted_at',
        'id',
        'updated_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $casts = [
        'attendance_secret_expiration' => 'datetime',
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
}
