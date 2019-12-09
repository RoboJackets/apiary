<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Nova\Actions\Actionable;

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
        return $this->hasMany(\App\RecruitingResponse::class);
    }

    /**
     *  Get the organization member who visited at the recruiting event, assuming the record could be linked.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class);
    }

    // phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint

    /**
     * Save the model to the database.
     *
     * @param array<string,string>  $options
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

    // phpcs:enable

    /**
     * Route notifications for the mail channel.
     *
     * @return string
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
