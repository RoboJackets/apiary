<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

/**
 * Represents a template for a notififaction that is stored in the database.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate newModelQuery()
 * @method \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereBodyMarkdown($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|NotificationTemplate onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|NotificationTemplate withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|NotificationTemplate withTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $created_by The user ID that created the template
 * @property int $id the database ID for this NotificationTemplate
 * @property string $body_markdown The body of this template
 * @property string $name The name of the template
 * @property string $subject The subject that will be used for emails sent using this template
 *
 * @property-read \App\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Laravel\Nova\Actions\ActionEvent> $actions
 * @property-read int|null $actions_count
 */
class NotificationTemplate extends Model
{
    use Actionable;
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['created_by'];

    /**
     * Get the user that owns the template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'creator' => 'users',
        ];
    }
}
