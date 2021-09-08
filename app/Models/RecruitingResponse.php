<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a response to a recruiting survey.
 *
 * @property string|null $response
 * @property int $recruiting_visit_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\RecruitingVisit $recruitingVisit
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse newQuery()
 * @method static \Illuminate\Database\Query\Builder|RecruitingResponse onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse whereRecruitingVisitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecruitingResponse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|RecruitingResponse withTrashed()
 * @method static \Illuminate\Database\Query\Builder|RecruitingResponse withoutTrashed()
 * @mixin         \Barryvdh\LaravelIdeHelper\Eloquent
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
