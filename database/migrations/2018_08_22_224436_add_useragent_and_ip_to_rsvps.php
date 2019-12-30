<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUseragentAndIpToRsvps extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rsvps', static function (Blueprint $table): void {
            $table->ipAddress('ip_address')->after('user_id')->nullable();
            $table->string('user_agent')->after('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rsvps', static function (Blueprint $table): void {
            $table->dropColumn('ip_address');
            $table->dropColumn('user_agent');
        });
    }
}
