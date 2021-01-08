<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemoteAttendanceLinkIdColumnToAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance', static function (Blueprint $table): void {
            $table->unsignedInteger('remote_attendance_link_id')->nullable();
            $table->foreign('remote_attendance_link_id')->references('id')->on('remote_attendance_links');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', static function (Blueprint $table): void {
            $table->dropForeign('attendance_remote_attendance_link_id_foreign');
            $table->dropColumn('remote_attendance_link_id');
        });
    }
}
