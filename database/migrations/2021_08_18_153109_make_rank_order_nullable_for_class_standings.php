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
        if (config('database.default') !== 'mysql') {
            return;
        }

        Schema::table('class_standings', static function (Blueprint $table): void {
            $table->integer('rank_order')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_standings', static function (Blueprint $table): void {
            $table->integer('rank_order')->nullable(false)->change();
        });
    }
};
