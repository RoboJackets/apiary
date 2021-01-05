<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,string|int|\DateTime|null>
     */
    public function definition(): array
    {
        $lastName = $this->faker->lastName;
        $uid = $this->faker->bothify('?'.lcfirst($lastName).'##');

        return [
            'uid' => $uid,
            'gtid' => $this->faker->numerify('#########'),
            'slack_id' => null,
            'gt_email' => $uid.'@gatech.edu',
            'personal_email' => $this->faker->safeEmail,
            'first_name' => $this->faker->firstName,
            'middle_name' => $this->faker->optional()->lastName,
            'last_name' => $lastName,
            'preferred_name' => $this->faker->optional()->firstName,
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
        ];
    }
}
