<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DuesPackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=DuesPackagesSeeder".
     */
    public function run(): void
    {
        \App\DuesPackage::factory()->count(10)->create();
    }
}
