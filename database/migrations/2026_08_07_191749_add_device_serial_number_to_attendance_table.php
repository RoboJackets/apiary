<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance', static function (Blueprint $table): void {
            $table->unsignedInteger('device_serial_number')->nullable();

            $table->foreign('device_serial_number', 'attendance_device_serial_number_foreign')
                ->references('serial_number')
                ->on('devices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', static function (Blueprint $table): void {
            $table->dropForeign('attendance_device_serial_number_foreign');

            $table->dropColumn('device_serial_number');
        });
    }
};
