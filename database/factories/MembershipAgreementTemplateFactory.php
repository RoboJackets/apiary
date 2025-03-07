<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A factory for MembershipAgreementTemplates.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MembershipAgreementTemplate>
 */
class MembershipAgreementTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string,string|int|\DateTime|bool|null>
     */
    #[\Override]
    public function definition(): array
    {
        return [
            'text' => $this->faker->paragraph(),
        ];
    }
}
