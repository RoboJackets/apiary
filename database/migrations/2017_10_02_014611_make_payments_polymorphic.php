<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakePaymentsPolymorphic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->integer('payable_id')->after('id');
            $table->string('payable_type')->after('payable_id');
        });

        Schema::table('dues_transactions', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
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
            $table->dropColumn('payable_id');
            $table->dropColumn('payable_type');
        });

        Schema::table('dues_transactions', function (Blueprint $table) {
            $table->unsignedInteger('payment_id')->nullable()->after('dues_package_id');
            $table->foreign('payment_id')->references('id')->on('payments');
        });
    }
}
