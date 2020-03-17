<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a response to a recruiting survey.
 *
 * @property string $response the response to the survey
 */
class RecruitingResponse extends Model
{
    use SoftDeletes;

    /**
     *  Get the recruiting visit associated with this recruiting response.
     */
    public function recruitingVisit(): BelongsTo
    {
        return $this->belongsTo(RecruitingVisit::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['recruiting_survey_id', 'response'];

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'recruitingVisit' => 'recruiting-visits',
        ];
    }
}
