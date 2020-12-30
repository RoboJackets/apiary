<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePaymentsTableForHistoricalDuesImport extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', static function (Blueprint $table): void {
            $table->unsignedInteger('recorded_by')->nullable()->change();
            $table->string('square_cash_transaction_id', 8)->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', static function (Blueprint $table): void {
            $table->unsignedInteger('recorded_by')->nullable(false)->change();
            $table->dropColumn('square_cash_transaction_id');
        });
    }
}
