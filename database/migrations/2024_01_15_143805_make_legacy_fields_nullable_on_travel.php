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
            $table->boolean('is_international')->nullable()->default(null)->change();
            $table->boolean('export_controlled_technology')->nullable()->default(null)->change();
            $table->boolean('embargoed_destination')->nullable()->default(null)->change();
            $table->boolean('biological_materials')->nullable()->default(null)->change();
            $table->boolean('equipment')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel', static function (Blueprint $table): void {
            $table->boolean('is_international')->nullable(false)->change();
            $table->boolean('export_controlled_technology')->nullable(false)->change();
            $table->boolean('embargoed_destination')->nullable(false)->change();
            $table->boolean('biological_materials')->nullable(false)->change();
            $table->boolean('equipment')->nullable(false)->change();
        });
    }
};
