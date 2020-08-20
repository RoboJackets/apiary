<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendRsvpUserAgent extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rsvps', static function (Blueprint $table): void {
            $table->string('user_agent', 1023)->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rsvps', static function (Blueprint $table): void {
            $table->string('user_agent', 255)->default(null)->change();
        });
    }
}
