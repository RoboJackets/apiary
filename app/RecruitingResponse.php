<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a response to a recruiting survey.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse newModelQuery()
 * @method \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse whereFasetSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse whereFasetVisitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|RecruitingResponse onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|RecruitingResponse withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|RecruitingResponse withTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $faset_survey_id
 * @property int $faset_visit_id
 * @property string $response the response to the survey
 *
 * @property-read \App\RecruitingVisit $recruitingVisit
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
