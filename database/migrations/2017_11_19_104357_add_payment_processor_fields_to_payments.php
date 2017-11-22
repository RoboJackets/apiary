<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentProcessorFieldsToPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->longText('notes')->nullable()->after('recorded_by');
            $table->string('unique_id')->nullable()->unique()->after('recorded_by');
            $table->string('server_txn_id')->nullable()->unique()->after('recorded_by');
            $table->string('client_txn_id')->nullable()->unique()->after('recorded_by');
            $table->string('checkout_id')->nullable()->unique()->after('recorded_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('notes');
            $table->dropColumn('unique_id');
            $table->dropColumn('server_txn_id');
            $table->dropColumn('client_txn_id');
            $table->dropColumn('checkout_id');
        });
    }
}
