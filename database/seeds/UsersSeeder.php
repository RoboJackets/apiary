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
        factory(App\User::class, 10)->create();
    }
}
