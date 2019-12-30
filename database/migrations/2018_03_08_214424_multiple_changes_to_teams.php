<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MultipleChangesToTeams extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', static function (Blueprint $table): void {
            $table->string('slug')->after('name')->nullable();
            $table->boolean('attendable')->after('name')->default(false);
            $table->boolean('visible')->after('name')->default(false);
            $table->boolean('self_serviceable')->after('name')->default(false);
            $table->dropColumn('founding_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', static function (Blueprint $table): void {
            $table->dropColumn('slug');
            $table->dropColumn('attendable');
            $table->dropColumn('visible');
            $table->dropColumn('self_serviceable');
            $table->dropColumn('founding_year');
            $table->char('founding_semester', 4)->after('name');
        });
    }
}
