<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoogleGroupToTeam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('teams', static function (Blueprint $table): void {
            $table->string('google_group', 255)->after('slack_private_channel_id')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('teams', static function (Blueprint $table): void {
            $table->dropColumn('google_group');
        });
    }
}
