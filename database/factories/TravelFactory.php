<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A factory for Travel.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Travel>
 */
class TravelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string,string|int|\DateTime|bool>
     */
    public function definition(): array
    {
        return [
            'name' => bin2hex(openssl_random_pseudo_bytes(16)),
            'primary_contact_user_id' => User::all()->random()->id,
            'destination' => $this->faker->word(),
            'departure_date' => $this->faker->dateTimeBetween('now', '1 week'),
            'return_date' => $this->faker->dateTimeBetween('1 week', '2 weeks'),
            'fee_amount' => $this->faker->numberBetween(0, 1000),
            'included_with_fee' => $this->faker->paragraph(),
            'is_international' => $this->faker->boolean(),
            'export_controlled_technology' => $this->faker->boolean(),
            'embargoed_destination' => $this->faker->boolean(),
            'biological_materials' => $this->faker->boolean(),
            'equipment' => $this->faker->boolean(),
            'status' => 'draft',
        ];
    }
}
