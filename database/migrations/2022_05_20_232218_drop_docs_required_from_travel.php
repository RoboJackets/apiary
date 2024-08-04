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
        Schema::table('travel', static function (Blueprint $table): void {
            $table->dropColumn('documents_required');
        });

        Schema::table('travel_assignments', static function (Blueprint $table): void {
            $table->dropColumn('documents_received');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel', static function (Blueprint $table): void {
            $table->longText('documents_required')->nullable();
        });

        Schema::table('travel_assignments', static function (Blueprint $table): void {
            $table->boolean('documents_received');
        });
    }
};
