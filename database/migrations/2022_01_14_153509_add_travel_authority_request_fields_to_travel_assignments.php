<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTravelAuthorityRequestFieldsToTravelAssignments extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('travel_assignments', static function (Blueprint $table): void {
            $table->boolean('tar_received')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_assignments', static function (Blueprint $table): void {
            $table->dropColumn('tar_received');
        });
    }
}
