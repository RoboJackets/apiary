<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A factory for Devices.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function definition(): array
    {
        return [
            'serial_number' => $this->faker->unique()->numberBetween(1000000, 9999999),
            'hardware_version' => $this->faker->word(),
            'software_version' => $this->faker->word(),
            'firmware_version' => $this->faker->word(),
            'manufacturer' => $this->faker->company(),
            'model' => $this->faker->word(),
            'battery_percentage' => $this->faker->numberBetween(0, 100),
            'last_seen_user_id' => User::factory(),
            'last_seen_at' => now(),
            'last_seen_ip_address' => $this->faker->ipv4(),
        ];
    }
}
