<?php

declare(strict_types=1);

use Illuminate\Database\Seeder;

class DuesPackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=DuesPackagesSeeder".
     */
    public function run(): void
    {
        factory(App\DuesPackage::class, 10)->create();
    }
}
