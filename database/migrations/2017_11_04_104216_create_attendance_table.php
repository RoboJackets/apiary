<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance', static function (Blueprint $table): void {
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
     */
    public function down(): void
    {
        Schema::drop('attendance');
    }
}
