<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Sushi\Sushi;

/**
 * Airports with IATA codes.
 *
 * @property string|null $iata
 * @property string|null $name
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Airport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Airport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Airport query()
 * @method static \Illuminate\Database\Eloquent\Builder|Airport whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Airport whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Airport whereIata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Airport whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Airport whereState($value)
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class Airport extends Model
{
    use Searchable;
    use Sushi;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'iata';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Generate the rows to load into Sushi.
     *
     * @return array<array<string,string>>
     */
    public function getRows(): array
    {
        $raw = json_decode(file_get_contents(base_path('/vendor/mwgg/airports/airports.json')), true);

        $rows = [];

        foreach ($raw as $details) {
            if (! is_string($details['iata']) || ! ctype_alpha($details['iata']) || strlen($details['iata']) !== 3) {
                continue;
            }

            $rows[] = [
                'iata' => strtoupper($details['iata']),
                'name' => $details['name'],
                'city' => $details['city'],
                'state' => str_replace('-', ' ', $details['state']),
                'country' => $details['country'],
            ];
        }

        return $rows;
    }

    protected function sushiShouldCache(): true
    {
        return true;
    }

    protected function sushiCacheReferencePath(): string
    {
        return base_path('/vendor/mwgg/airports/airports.json');
    }
}
