<?php

declare(strict_types=1);

// phpcs:disable Squiz.PHP.Eval.Discouraged

namespace App\Models;

use App\Observers\MembershipAgreementTemplateObserver;
use Exception;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Blade;
use Throwable;

/**
 * App\Models\MembershipAgreementTemplate.
 *
 * @property int $id
 * @property string $text
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\App\Models\Signature> $signatures
 * @property-read int|null $signatures_count
 *
 * @method static \Database\Factories\MembershipAgreementTemplateFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|MembershipAgreementTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MembershipAgreementTemplate newQuery()
 * @method static \Illuminate\Database\Query\Builder|MembershipAgreementTemplate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MembershipAgreementTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|MembershipAgreementTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MembershipAgreementTemplate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MembershipAgreementTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MembershipAgreementTemplate whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MembershipAgreementTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|MembershipAgreementTemplate withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MembershipAgreementTemplate withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
#[ObservedBy([MembershipAgreementTemplateObserver::class])]
class MembershipAgreementTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Get Signatures for this template.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Signature, self>
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class);
    }

    public function renderForUser(User $user, bool $isElectronic): string
    {
        return self::render(
            $this->text,
            [
                'full_name' => $user->first_name.' '.$user->last_name,
                'is_electronic' => $isElectronic,
            ]
        );
    }

    /**
     * Wrapper around Blade engine stolen from https://stackoverflow.com/a/39802153.
     *
     * @param  array<string,string|bool>  $data  data to pass to the template
     */
    private static function render(string $string, array $data): string
    {
        $php = Blade::compileString($string);

        $obLevel = ob_get_level();
        ob_start();
        extract($data, EXTR_SKIP);

        try {
            eval('?>'.$php);
        } catch (Throwable $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            throw $e;
        }

        $output = ob_get_clean();

        if ($output === false) {
            throw new Exception('Failed to render agreement');
        }

        return $output;
    }
}
