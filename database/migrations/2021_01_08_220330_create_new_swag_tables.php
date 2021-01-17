<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewSwagTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('merch', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('fiscal_year_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dues_package_merch', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('dues_package_id')->constrained();
            $table->foreignId('merch_id')->constrained();
            $table->string('group', 255);
            $table->timestamps();


            $table->foreign('dues_package_id')->references('id')->on('users');
        });

        Schema::create('dues_transaction_merch', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('dues_transaction_id')->constrained();
            $table->foreignId('merch_id')->constrained();
            $table->timestamp('provided_at')->nullable();
            $table->unsignedInteger('provided_by')->nullable();
            $table->timestamps();

            $table->foreign('dues_transaction_id')->references('id')->on('users');
            $table->foreign('provided_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dues_transaction_merch');
        Schema::dropIfExists('dues_package_merch');
        Schema::dropIfExists('merch');
    }
}
