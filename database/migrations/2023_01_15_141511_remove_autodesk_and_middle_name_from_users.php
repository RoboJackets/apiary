<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            if (
                array_key_exists(
                    "users_autodesk_email_unique",
                    Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes("users")
                )
            ) {
                $table->dropUnique('users_autodesk_email_unique');
            }

            $table->dropColumn(['autodesk_email', 'autodesk_invite_pending', 'middle_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
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
