<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPeopleCounterIdToAttendance extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance', static function (Blueprint $table): void {
            $table->unsignedInteger('people_counter_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', static function (Blueprint $table): void {
            $table->dropColumn('people_counter_id');
        });
    }
}
