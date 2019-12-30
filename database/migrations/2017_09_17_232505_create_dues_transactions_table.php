<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDuesTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dues_transactions', static function (Blueprint $table): void {
            $table->increments('id');
            $table->boolean('received_polo')->default(false);
            $table->boolean('received_shirt')->default(false);
            $table->unsignedInteger('dues_package_id');
            $table->unsignedInteger('payment_id')->nullable();
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('dues_package_id')->references('id')->on('dues_packages');
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('dues_transactions');
    }
}
