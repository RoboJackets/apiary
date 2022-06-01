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
        // two separate calls to work around SQLite limitation
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('personal_email');
        });

        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('slack_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->string('personal_email');
            $table->string('slack_id');
        });
    }
};
