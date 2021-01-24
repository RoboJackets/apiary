<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DuesTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DuesTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,\DateTime|int|null>
     */
    public function definition(): array
    {
        return [
            'dues_package_id' => DuesPackage::all()->random()->id,
            'user_id' => User::all()->random()->id,
        ];
    }
}
