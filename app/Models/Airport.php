<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Sushi\Sushi;

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
            if ($details['iata'] === '' || $details['iata'] === null) {
                continue;
            }

            $rows[] = [
                'iata' => strtoupper($details['iata']),
                'name' => $details['name'],
                'city' => $details['city'],
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
