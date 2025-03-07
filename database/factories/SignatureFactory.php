<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MembershipAgreementTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A factory for signatures.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Signature>
 */
class SignatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, int|bool>
     */
    #[\Override]
    public function definition(): array
    {
        return [
            'membership_agreement_template_id' => MembershipAgreementTemplate::all()->random()->id,
            'user_id' => User::all()->random()->id,
            'electronic' => false,
        ];
    }
}
