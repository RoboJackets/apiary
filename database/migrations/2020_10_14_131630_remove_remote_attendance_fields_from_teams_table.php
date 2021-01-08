<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveRemoteAttendanceFieldsFromTeamsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', static function (Blueprint $table): void {
            $table->dropColumn('attendance_secret');
            $table->dropColumn('attendance_secret_expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', static function (Blueprint $table): void {
            $table->string('attendance_secret')->unique()->nullable();
            $table->timestamp('attendance_secret_expiration')->nullable();
        });
    }
}
