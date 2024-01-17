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
            $table->unsignedInteger('meal_per_diem')->nullable();
            $table->unsignedInteger('car_rental_cost')->nullable();
            $table->string('hotel_name')->nullable();
            $table->string('department_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel', static function (Blueprint $table): void {
            $table->dropColumn([
                'meal_per_diem',
                'car_rental_cost',
                'hotel_name',
                'department_number',
            ]);
        });
    }
};
