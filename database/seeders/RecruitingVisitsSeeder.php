<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\RecruitingVisit;
use Illuminate\Database\Seeder;

class FasetVisitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=RecruitingVisitsSeeder".
     */
    public function run(): void
    {
        RecruitingVisit::factory()->count(20)->create();
    }
}
