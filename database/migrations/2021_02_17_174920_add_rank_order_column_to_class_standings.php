<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRankOrderColumnToClassStandings extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('class_standings', static function (Blueprint $table): void {
            $table->integer('rank_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_standings', static function (Blueprint $table): void {
            $table->dropColumn('rank_order');
        });
    }
}
