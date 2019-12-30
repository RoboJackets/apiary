<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccessOverrideFieldsToUser extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dateTime('access_override_until')->nullable();

            $table->unsignedInteger('access_override_by_id')->nullable()->comment(
                'user_id of the user who entered access override'
            );

            $table->foreign('access_override_by_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('access_override_until');
            $table->dropColumn('access_override_by');
        });
    }
}
