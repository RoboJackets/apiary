<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * This model represents a sponsor entity in the application.
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property array $domain_names
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Sponsor extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'name',
        'start_date',
        'end_date',
        'domain_names',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'domain_names' => 'array',
        ];
    }

    /**
     * Get the active status of the sponsor.
     */
    public function active(): bool
    {
        return $this->end_date > now() && $this->start_date < now();
    }

    /**
     * Check if the given email is authorized for the sponsor.
     */
    public function authorized(string $email): bool
    {
        $domain = substr(strrchr($email, '@'), 1);

        return in_array($domain, $this->domain_names, true);
    }
}
