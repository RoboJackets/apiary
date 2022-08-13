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
            $table->unsignedInteger('employee_id')->unique()->nullable();
            $table->string('employee_home_department')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('employee_id');
            $table->dropColumn('employee_home_department');
        });
    }
};
