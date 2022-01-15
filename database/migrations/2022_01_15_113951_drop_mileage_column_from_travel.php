<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMileageColumnFromTravel extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('travel', static function (Blueprint $table): void {
            $table->dropColumn('tar_mileage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel', static function (Blueprint $table): void {
            $table->unsignedInteger('tar_mileage')->nullable();
        });
    }
}
