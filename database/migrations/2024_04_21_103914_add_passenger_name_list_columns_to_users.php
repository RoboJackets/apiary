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
        Schema::table('users', static function (Blueprint $table): void {
            $table->string('legal_middle_name')->nullable();
            $table->string('legal_gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('delta_skymiles_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('legal_middle_name');
            $table->dropColumn('legal_gender');
            $table->dropColumn('date_of_birth');
            $table->dropColumn('delta_skymiles_number');
        });
    }
};
