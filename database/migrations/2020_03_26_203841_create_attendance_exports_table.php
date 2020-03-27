<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceExportsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_exports', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('secret')->unique();
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->unsignedInteger('download_user_id')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('download_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_exports');
    }
}
