<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A factory for Users.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string,string|int|\DateTime|bool|null>
     */
    #[\Override]
    public function definition(): array
    {
        $lastName = $this->faker->lastName();
        $uid = $this->faker->bothify('?'.lcfirst($lastName).'##');

        return [
            'uid' => $uid,
            'gtid' => $this->faker->numerify('#########'),
            'gt_email' => $uid.'@gatech.edu',
            'first_name' => $this->faker->firstName(),
            'last_name' => $lastName,
            'preferred_name' => $this->faker->optional()->firstName(),
            'phone' => $this->faker->numerify('##########'),
            'emergency_contact_name' => null,
            'emergency_contact_phone' => null,
            'join_semester' => null,
            'graduation_semester' => null,
            'shirt_size' => $this->faker->randomElement(['s', 'm', 'l', 'xl']),
            'polo_size' => $this->faker->randomElement(['s', 'm', 'l', 'xl']),
            'gender' => $this->faker->randomElement(['male', 'female', 'nonbinary', 'none']),
            'ethnicity' => $this->faker->randomElement(
                ['white', 'asian', 'hispanic', 'black', 'native', 'islander', 'none']
            ),
            'create_reason' => 'factory',
            'has_ever_logged_in' => $this->faker->boolean(),
        ];
    }
}
