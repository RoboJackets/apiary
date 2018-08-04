<?php

use Illuminate\Database\Seeder;

class DuesTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=DuesTransactionsSeeder".
     *
     * @return void
     */
    public function run()
    {
        factory(App\DuesPackage::class, 10)->create();
        factory(App\DuesTransaction::class, 10)->create()->each(function($duesTransaction) {
            $duesTransaction->payment()->save(factory(App\Payment::class)->make());
            $duesTransaction->package();
        });
    }
}
