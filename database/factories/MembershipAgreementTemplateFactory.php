<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MembershipAgreementTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class MembershipAgreementTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MembershipAgreementTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,string|int|\DateTime|bool|null>
     */
    public function definition(): array
    {
        return [
            'text' => $this->faker->paragraph(),
        ];
    }
}
