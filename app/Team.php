<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App;

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
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Represents a group of Users.
 *
 * @property int $id the database identifier for this team
 * @property boolean $visible whether this team is visible to non-admins
 * @property boolean $self_serviceable whether this team can be joined/left voluntarily
 * @property string $name The name of the team
 * @property string $slack_private_channel_id the slack internal ID of the team's private channel
 * @method static \Illuminate\Database\Eloquent\Builder visible() Scopes a query to only visible teams
 */
class Team extends Model
{
    use Actionable;
    use SoftDeletes;
    use HasSlug;
    use HasManyEvents;
    use HasBelongsToManyEvents;
    use HasRelationshipObservables;
    use Notifiable;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     *  Get the Users that are members of this Team.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(\App\User::class)->withTimestamps();
    }

    /**
     * Get all of the team's attendance.
     */
    public function attendance(): MorphMany
    {
        return $this->morphMany(\App\Attendance::class, 'attendable');
    }

    /**
     * Scope a query to only include attendable teams.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAttendable(Builder $query): Builder
    {
        return $query->where('attendable', true);
    }

    /**
     * Scope a query to only include visible teams.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('visible', true);
    }

    /**
     * Scope a query to only include self-serviceable teams.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSelfServiceable(Builder $query): Builder
    {
        return $query->where('self_serviceable', true);
    }

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
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
        return $this->belongsTo(\App\User::class, 'project_manager_id');
    }

    /**
     * Route notifications for the Slack channel.
     *
     * @param Notification $notification
     *
     * @return string|null
     */
    public function routeNotificationForSlack(Notification $notification): ?string
    {
        return config('services.team_slack_webhook_url');
    }
}
