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
        \App\DuesPackage::factory()->count(10)->create();
        \App\DuesTransaction::factory()->count(10)->create()->each(static function ($duesTransaction): void {
            $duesTransaction->payment()->save(\App\Payment::factory()->make());
            $duesTransaction->package();
        });
    }
}
