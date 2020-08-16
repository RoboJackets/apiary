<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Nova\Actions\Actionable;

/**
 * Represents a non-member's visit to a recruiting event.
 *
 * @property int $id the database identifier for this RecruitingVisit
 * @property string $visit_token an identifier for this visit
 * @property string $recruiting_email the email address provided by the visitor
 * @property int $user_id the ID of the user, if available
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 */
class RecruitingVisit extends Model
{
    use Actionable;
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
