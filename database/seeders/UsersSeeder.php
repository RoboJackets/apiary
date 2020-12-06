<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=UsersSeeder".
     */
    public function run(): void
    {
        User::factory()->count(10)->create();
    }
}
