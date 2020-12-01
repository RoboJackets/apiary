<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FasetVisitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=FasetVisitsSeeder".
     */
    public function run(): void
    {
        \App\RecruitingVisit::factory()->count(20)->create();
    }
}
