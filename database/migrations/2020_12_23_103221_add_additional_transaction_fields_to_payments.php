<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalTransactionFieldsToPayments extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', static function (Blueprint $table): void {
            $table->string('card_brand', 255)->nullable();
            $table->string('card_type', 255)->nullable();
            $table->string('last_4', 4)->nullable();
            $table->string('prepaid_type', 255)->nullable();
            $table->string('entry_method', 50)->nullable();
            $table->string('statement_description', 50)->nullable();
            $table->string('receipt_number', 4)->nullable()->unique();
            $table->string('receipt_url', 255)->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', static function (Blueprint $table): void {
            $table->dropColumn('card_brand');
            $table->dropColumn('card_type');
            $table->dropColumn('last_4');
            $table->dropColumn('prepaid_type');
            $table->dropColumn('entry_method');
            $table->dropColumn('statement_description');
            $table->dropColumn('receipt_number');
            $table->dropColumn('receipt_url');
        });
    }
}
