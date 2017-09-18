<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDuesTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dues_transactions', function (Blueprint $table) {
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
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dues_transactions');
    }
}
