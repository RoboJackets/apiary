<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DuesPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A factory for DuesTransactions.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DuesTransaction>
 */
class DuesTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string,int|string>
     */
    #[\Override]
    public function definition(): array
    {
        return [
            'dues_package_id' => DuesPackage::all()->random()->id,
            'user_id' => User::all()->random()->id,
        ];
    }
}
