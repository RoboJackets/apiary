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
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'primary_contact_user_id' => User::all()->random()->id,
            'destination' => $this->faker->word(),
            'departure_date' => $this->faker->dateTimeBetween('now', '1 week'),
            'return_date' => $this->faker->dateTimeBetween('1 week', '2 weeks'),
            'fee_amount' => (string) $this->faker->randomFloat(2, 0, 1000),
            'included_with_fee' => $this->faker->paragraph(),
            'is_international' => $this->faker->boolean(),
        ];
    }
}
