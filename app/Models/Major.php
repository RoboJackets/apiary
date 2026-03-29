<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\JoinClause;

/**
 * Represents a single major (e.g. Computer Science).
 *
 * @property int $id
 * @property string|null $display_name
 * @property string $gtad_majorgroup_name
 * @property string $whitepages_ou
 * @property string $school
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $members_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Major newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Major newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Major query()
 * @method static \Illuminate\Database\Eloquent\Builder|Major whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Major whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Major whereGtadMajorgroupName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Major whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Major whereSchool($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Major whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Major whereWhitepagesOu($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\App\Models\User> $members
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class Major extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the Users that are members of this Major.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\User, self>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->whereNull('major_user.deleted_at')->withTimestamps();
    }

    public static function findOrCreateFromGtadGroup(string $gtad_group): self
    {
        $major = self::where('gtad_majorgroup_name', strtolower($gtad_group))->first();

        if ($major === null) {
            $exploded = explode('_', $gtad_group);

            $major = new self();
            $major->gtad_majorgroup_name = strtolower($gtad_group);
            $major->whitepages_ou = strtoupper($exploded[0]);
            $major->school = strtoupper($exploded[1]);
            $major->save();
        }

        return $major;
    }

    /**
     * Returns a list of majors (display names only) for active users who have uploaded resumes.
     *
     * @return array<int, string>
     */
    public static function getResumeMajors(): array
    {
        return User::selectRaw('distinct(majors.display_name) as distinct_display_names')
            ->active()
            ->whereNotNull('resume_date')
            ->where('primary_affiliation', 'student')
            ->where('is_service_account', '=', false)
            ->whereDoesntHave('duesPackages', static function (Builder $q): void {
                $q->where('restricted_to_students', false);
            })
            ->leftJoin('major_user', static function (JoinClause $join): void {
                $join->on('users.id', '=', 'major_user.user_id')
                    ->whereNull('major_user.deleted_at');
            })
            ->leftJoin('majors', 'major_user.major_id', '=', 'majors.id')
            ->orderBy('distinct_display_names')
            ->pluck('distinct_display_names')
            ->toArray();
    }
}
