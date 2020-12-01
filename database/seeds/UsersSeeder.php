<?php

declare(strict_types=1);

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=UsersSeeder".
     */
    public function run(): void
    {
        \App\User::factory()->count(10)->create();
    }
}
