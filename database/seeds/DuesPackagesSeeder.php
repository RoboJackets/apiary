<?php

use Illuminate\Database\Seeder;

class DuesPackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=DuesPackagesSeeder".
     *
     * @return void
     */
    public function run()
    {
        factory(App\DuesPackage::class, 10)->create();
    }
}
