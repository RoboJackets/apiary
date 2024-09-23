<?php

declare(strict_types=1);

use App\Models\ClassStanding;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        ClassStanding::firstOrCreate(
            ['name' => 'jephs'],
            ['rank_order' => -1]
        );
        ClassStanding::firstOrCreate(
            ['name' => 'special'],
            ['rank_order' => 0]
        );
        ClassStanding::firstOrCreate(
            ['name' => 'freshman'],
            ['rank_order' => 1]
        );
        ClassStanding::firstOrCreate(
            ['name' => 'sophomore'],
            ['rank_order' => 2]
        );
        ClassStanding::firstOrCreate(
            ['name' => 'junior'],
            ['rank_order' => 3]
        );
        ClassStanding::firstOrCreate(
            ['name' => 'senior'],
            ['rank_order' => 4]
        );
        ClassStanding::firstOrCreate(
            ['name' => 'masters'],
            ['rank_order' => 5]
        );
        ClassStanding::firstOrCreate(
            ['name' => 'doctorate'],
            ['rank_order' => 6]
        );
        ClassStanding::firstOrCreate(
            ['name' => 'pe'],
            ['rank_order' => 7]
        );
    }

    public function down(): void
    {
        Schema::table('class_standings', static function ($table): void {
            $table->truncate();
        });
    }
};
