<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Nova\Actions\Actionable;

/**
 * Represents a non-member's visit to a recruiting event.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingVisit newModelQuery()
 * @method \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingVisit query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingVisit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingVisit whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingVisit whereFasetEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingVisit whereFasetName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingVisit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingVisit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingVisit whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingVisit whereVisitToken($value)
 * @method static \Illuminate\Database\Query\Builder|RecruitingVisit onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|RecruitingVisit withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|RecruitingVisit withTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $id the database identifier for this RecruitingVisit
 * @property int $user_id the ID of the user, if available
 * @property string $faset_email
 * @property string $faset_name
 * @property string $recruiting_email the email address provided by the visitor
 * @property string $visit_token an identifier for this visit
 *
 * @property-read \App\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\RecruitingResponse> $recruitingResponses
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Laravel\Nova\Actions\ActionEvent> $actions
 * @property-read int|null $actions_count
 * @property-read int|null $notifications_count
 * @property-read int|null $recruiting_responses_count
 */
class RecruitingVisit extends Model
{
    use Actionable;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    /**
     *  Get the Recruiting Responses associated with this Recruiting Visit.
     */
    public function recruitingResponses(): HasMany
    {
        return $this->hasMany(RecruitingResponse::class);
    }

    /**
     *  Get the organization member who visited at the recruiting event, assuming the record could be linked.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Save the model to the database.
     *
     * @param array  $options
     *
     * @return bool          Whether the save succeeded
     */
    public function save(array $options = []): bool
    {
        if (! isset($this->visit_token) && '' !== $this->visit_token) {
            // Store 20 char secure random token
            $this->visit_token = strtr(base64_encode(random_bytes(15)), '+/=', '-_.');
        }

        return parent::save($options);
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail(): string
    {
        return $this->recruiting_email;
    }

    /**
     * Get the visit token for the model.
     */
    public function getVisitToken(): string
    {
        return $this->visit_token;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['recruiting_email', 'recruiting_name'];

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'recruitingResponses' => 'recruiting-responses',
            'user' => 'users',
        ];
    }
}
