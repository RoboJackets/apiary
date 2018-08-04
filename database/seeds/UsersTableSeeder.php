<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=UsersSeeder".
     *
     * @return void
     */
    public function run()
    {
        factory(App\User::class, 10)->create();
    }
}
