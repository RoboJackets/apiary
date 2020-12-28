<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSquareTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('square_transactions', static function (Blueprint $table): void {
            $table->id();
            $table->dateTimeTz('transaction_timestamp');
            $table->float('amount');
            $table->string('source');
            $table->string('entry_method');
            $table->float('processing_fee');
            $table->string('transaction_id')->unique();
            $table->string('payment_id')->unique();
            $table->string('card_brand');
            $table->string('last_4', 4);
            $table->string('device_name')->nullable();
            $table->string('staff_name')->nullable();
            $table->string('description');
            $table->string('customer_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('square_transactions');
    }
}
