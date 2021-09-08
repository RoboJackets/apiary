<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

/**
 * Represents a template for a notififaction that is stored in the database.
 *
 * @property int $id
 * @property string $name
 * @property string $from
 * @property string $subject
 * @property string|null $body_markdown
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Laravel\Nova\Actions\ActionEvent> $actions
 * @property-read int|null $actions_count
 * @property-read \App\Models\User $creator
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate newQuery()
 * @method static \Illuminate\Database\Query\Builder|NotificationTemplate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereBodyMarkdown($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|NotificationTemplate withTrashed()
 * @method static \Illuminate\Database\Query\Builder|NotificationTemplate withoutTrashed()
 * @mixin         \Barryvdh\LaravelIdeHelper\Eloquent
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
