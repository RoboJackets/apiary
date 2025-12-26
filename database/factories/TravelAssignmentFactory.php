<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Travel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A factory for travel assignments.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelAssignment>
 */
class TravelAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, int|string>
     */
    #[\Override]
    public function definition(): array
    {
        return [
            'travel_id' => Travel::all()->random()->id,
            'user_id' => User::all()->random()->id,
        ];
    }
}
