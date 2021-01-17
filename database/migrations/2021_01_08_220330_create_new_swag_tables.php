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
        Schema::create('merchandise', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('fiscal_year_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dues_package_merchandise', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('dues_package_id');
            $table->unsignedBigInteger('merchandise_id');
            $table->string('group', 255);
            $table->timestamps();

            $table->foreign('dues_package_id')->references('id')->on('dues_packages');
            // This has to be manually created because the table name is not a typical plural form ("merchandises").
            $table->foreign('merchandise_id')->references('id')->on('merchandise');
        });

        Schema::create('dues_transaction_merchandise', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('dues_transaction_id');
            $table->unsignedBigInteger('merchandise_id');
            $table->timestamp('provided_at')->nullable();
            $table->unsignedInteger('provided_by')->nullable();
            $table->timestamps();

            $table->foreign('dues_transaction_id')->references('id')->on('dues_transactions');
            // This has to be manually created because the table name is not a typical plural form ("merchandises").
            $table->foreign('merchandise_id')->references('id')->on('merchandise');
            $table->foreign('provided_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dues_transaction_merchandise');
        Schema::dropIfExists('dues_package_merchandise');
        Schema::dropIfExists('merchandise');
    }
}
