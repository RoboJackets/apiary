<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run the artisan command "php artisan migrate --seed"
     *
     * @return void
     */
    public function run()
    {
        $this->call(TeamsTableSeeder::class);
    }
}
