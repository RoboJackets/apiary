<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dues', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('eligible_for_shirt')->default(false);
            $table->boolean('eligible_for_polo')->default(false);
            $table->boolean('received_shirt')->default(false);
            $table->boolean('received_polo')->default(false);
            $table->timestamp('effective_start');
            $table->timestamp('effective_end');
            $table->unsignedInteger('payment_id')->nullable();
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dues');
    }
}
