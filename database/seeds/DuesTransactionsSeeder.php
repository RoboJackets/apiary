<?php

declare(strict_types=1);

use Illuminate\Database\Seeder;

class DuesTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=DuesTransactionsSeeder".
     */
    public function run(): void
    {
        factory(App\DuesPackage::class, 10)->create();
        factory(App\DuesTransaction::class, 10)->create()->each(static function ($duesTransaction): void {
            $duesTransaction->payment()->save(factory(App\Payment::class)->make());
            $duesTransaction->package();
        });
    }
}
