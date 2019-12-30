<?php

declare(strict_types=1);

use Illuminate\Database\Seeder;

class FasetVisitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=FasetVisitsSeeder".
     */
    public function run(): void
    {
        factory(App\RecruitingVisit::class, 20)->create();
    }
}
