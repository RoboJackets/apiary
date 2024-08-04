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
            $table->string('tar_project_number')->nullable();
            $table->string('tar_account_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel', static function (Blueprint $table): void {
            $table->dropColumn('tar_project_number');
            $table->dropColumn('tar_account_code');
        });
    }
};
