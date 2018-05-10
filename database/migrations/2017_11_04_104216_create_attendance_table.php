<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->increments('id');
            $table->string('attendable_type');
            $table->unsignedInteger('attendable_id');
            $table->integer('gtid')->length(9)->nullable();
            $table->string('source')->nullable();
            $table->unsignedInteger('recorded_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('recorded_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('attendance');
    }
}
