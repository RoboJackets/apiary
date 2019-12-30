<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentProcessorFieldsToPayments extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', static function (Blueprint $table): void {
            $table->longText('notes')->nullable()->after('recorded_by');
            $table->string('unique_id')->nullable()->unique()->after('recorded_by');
            $table->string('server_txn_id')->nullable()->unique()->after('recorded_by');
            $table->string('client_txn_id')->nullable()->unique()->after('recorded_by');
            $table->string('checkout_id')->nullable()->unique()->after('recorded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', static function (Blueprint $table): void {
            $table->dropColumn('notes');
            $table->dropColumn('unique_id');
            $table->dropColumn('server_txn_id');
            $table->dropColumn('client_txn_id');
            $table->dropColumn('checkout_id');
        });
    }
}
