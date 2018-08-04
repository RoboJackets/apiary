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
        factory(App\User::class, 10)->create();
        factory(App\DuesPackage::class, 10)->create();
        factory(App\DuesTransaction::class, 10)->create()->each(function($duesTransaction) {
            $duesTransaction->payment()->save(factory(App\Payment::class)->make());
            $duesTransaction->package();
        });
        factory(App\FasetVisit::class, 20)->create();
    }
}
