<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A factory for RecruitingVisits.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecruitingVisit>
 */
class RecruitingVisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string,string>
     */
    public function definition(): array
    {
        return [
            'recruiting_name' => $this->faker->name(),
            'recruiting_email' => $this->faker->safeEmail(),
            'visit_token' => $this->faker->asciify('********************'),
        ];
    }
}
