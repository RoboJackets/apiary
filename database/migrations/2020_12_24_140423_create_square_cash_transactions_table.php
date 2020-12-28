<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSquareCashTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('square_cash_transactions', static function (Blueprint $table): void {
            $table->id();
            $table->string('transaction_id', 8)->unique();
            $table->dateTimeTz('transaction_timestamp');
            $table->float('amount');
            $table->string('note')->nullable();
            $table->string('name_of_sender');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('square_cash_transactions');
    }
}
