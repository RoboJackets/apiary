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
            $table->boolean('is_international')->default(false);
            $table->boolean('export_controlled_technology')->nullable();
            $table->text('export_controlled_technology_description')->nullable();
            $table->boolean('embargoed_destination')->nullable();
            $table->text('embargoed_countries')->nullable();
            $table->boolean('biological_materials')->nullable();
            $table->text('biological_materials_description')->nullable();
            $table->boolean('equipment')->nullable();
            $table->text('equipment_description')->nullable();
            $table->text('international_travel_justification')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel', static function (Blueprint $table): void {
            $table->dropColumn([
                'is_international',
                'export_controlled_technology',
                'export_controlled_technology_description',
                'embargoed_destination',
                'embargoed_countries',
                'biological_materials',
                'biological_materials_description',
                'equipment',
                'equipment_description',
                'international_travel_justification',
            ]);
        });
    }
};
