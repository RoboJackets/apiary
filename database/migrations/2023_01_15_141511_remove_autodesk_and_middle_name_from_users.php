<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireSingleLineCondition.RequiredSingleLineCondition

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
        if (config('database.default') !== 'mysql') {
            return;
        }

        Schema::table('users', static function (Blueprint $table): void {
            $table->dropUnique('users_autodesk_email_unique');
            $table->dropColumn(['autodesk_email', 'autodesk_invite_pending', 'middle_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->string('middle_name')->nullable();
            $table->string('autodesk_email')->unique()->nullable();
            $table->boolean('autodesk_invite_pending')->default(false);
        });
    }
};
