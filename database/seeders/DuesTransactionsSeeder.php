<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\DuesPackage;
use App\DuesTransaction;
use App\Payment
use Illuminate\Database\Seeder;

class DuesTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run "php artisan db:seed --class=DuesTransactionsSeeder".
     */
    public function run(): void
    {
        DuesPackage::factory()->count(10)->create();
        DuesTransaction::factory()->count(10)->create()->each(static function (DuesTransaction $duesTransaction): void {
            $duesTransaction->payment()->save(Payment::factory()->make());
            $duesTransaction->package();
        });
    }
}
