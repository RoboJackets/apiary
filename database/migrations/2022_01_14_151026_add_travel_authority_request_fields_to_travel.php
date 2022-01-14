<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTravelAuthorityRequestFieldsToTravel extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('travel', static function (Blueprint $table): void {
            $table->boolean('tar_required');
            $table->json('tar_transportation_mode');
            $table->string('tar_itinerary');
            $table->string('tar_purpose');
            $table->unsignedInteger('tar_airfare');
            $table->unsignedInteger('tar_other_trans');
            $table->unsignedInteger('tar_lodging');
            $table->unsignedInteger('tar_mileage');
            $table->unsignedInteger('tar_registration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel', static function (Blueprint $table): void {
            $table->dropColumn('tar_required');
            $table->dropColumn('tar_transportation_mode');
            $table->dropColumn('tar_itinerary');
            $table->dropColumn('tar_purpose');
            $table->dropColumn('tar_airfare');
            $table->dropColumn('tar_other_trans');
            $table->dropColumn('tar_lodging');
            $table->dropColumn('tar_mileage');
            $table->dropColumn('tar_registration');
        });
    }
}
