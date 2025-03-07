<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DuesTransaction;
use App\Models\Event;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A factory for Payments.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string,int|float|string|null>
     */
    #[\Override]
    public function definition(): array
    {
        return [
            'payable_id' => $this->faker->numberBetween(0, 200),
            'payable_type' => $this->faker->randomElement(
                [
                    DuesTransaction::getMorphClassStatic(),
                    Event::getMorphClassStatic(),
                ]
            ),
            'amount' => (string) $this->faker->randomFloat(2, 0, 1000),
            'processing_fee' => (string) $this->faker->randomFloat(2, 0, 1000),
            'method' => $this->faker->randomElement(array_keys(Payment::$methods)),
            'recorded_by' => User::all()->random()->id,
            'checkout_id' => null,
            'client_txn_id' => null,
            'server_txn_id' => null,
            'unique_id' => $this->faker->asciify('********************'),
            'notes' => 'Pending square payment',
        ];
    }
}
