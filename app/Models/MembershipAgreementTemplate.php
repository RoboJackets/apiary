<?php

declare(strict_types=1);

// phpcs:disable Squiz.PHP.Eval.Discouraged

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Blade;
use Throwable;

class MembershipAgreementTemplate extends Model
{
    use SoftDeletes;

    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class);
    }

    public function renderForUser(User $user, bool $isElectronic): string
    {
        return self::render(
            $this->text,
            [
                'full_name' => $user->first_name . ' ' . $user->last_name,
                'is_electronic' => $isElectronic,
            ]
        );
    }

    /**
     * Wrapper around Blade engine stolen from https://stackoverflow.com/a/39802153
     *
     * @param array<string,string|bool> $data data to pass to the template
     */
    private static function render(string $string, array $data): string
    {
        $php = Blade::compileString($string);

        $obLevel = ob_get_level();
        ob_start();
        extract($data, EXTR_SKIP);

        try {
            eval('?' . '>' . $php);
        } catch (Throwable $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            throw $e;
        }

        $output = ob_get_clean();

        if (false === $output) {
            throw new Exception('Failed to render agreement');
        }

        return $output;
    }
}
