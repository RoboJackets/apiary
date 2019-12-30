<?php

use Illuminate\Database\Seeder;

class FasetVisitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=FasetVisitsSeeder".
     *
     * @return void
     */
    public function run()
    {
        factory(App\RecruitingVisit::class, 20)->create();
    }
}
