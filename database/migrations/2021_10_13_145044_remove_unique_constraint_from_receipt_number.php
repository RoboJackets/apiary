<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUniqueConstraintFromReceiptNumber extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if ('mysql' === config('database.default')) {
            Schema::table('payments', static function (Blueprint $table): void {
                $table->dropUnique('payments_receipt_number_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ('mysql' === config('database.default')) {
            Schema::table('payments', static function (Blueprint $table): void {
                $table->unique('receipt_number');
            });
        }
    }
}
