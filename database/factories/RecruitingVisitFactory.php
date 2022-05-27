<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RecruitingVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

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
