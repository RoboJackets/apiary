<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DuesPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class DuesPackageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DuesPackage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,string|float|\DateTime>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'effective_start' => $this->faker->dateTimeBetween('-5 years', '-1 year'),
            'effective_end' => $this->faker->dateTimeBetween('-11 months', 'now'),
            'cost' => (string) $this->faker->randomFloat(2, 0, 1000),
        ];
    }
}
