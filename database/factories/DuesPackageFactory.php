<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A factory for DuesPackages.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DuesPackage>
 */
class DuesPackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string,string|float|\DateTime>
     */
    public function definition(): array
    {
        return [
            'name' => bin2hex(openssl_random_pseudo_bytes(16)),
            'effective_start' => $this->faker->dateTimeBetween('-5 years', '-1 year'),
            'effective_end' => $this->faker->dateTimeBetween('-11 months', 'now'),
            'cost' => (string) $this->faker->randomFloat(2, 0, 1000),
        ];
    }
}
