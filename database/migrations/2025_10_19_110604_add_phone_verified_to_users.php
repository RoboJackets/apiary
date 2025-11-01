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
        Schema::table('users', static function (Blueprint $table): void {
            $table->boolean('phone_verified')
                ->after('phone')
                ->default(false);
            $table->boolean('emergency_contact_phone_verified')
                ->after('emergency_contact_phone')
                ->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('phone_verified');
            $table->dropColumn('emergency_contact_phone_verified');
        });
    }
};
