<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSwagFieldsFromDuesPackages extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dues_packages', static function (Blueprint $table): void {
            $table->dropColumn('eligible_for_shirt');
            $table->dropColumn('eligible_for_polo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dues_packages', static function (Blueprint $table): void {
            // This will leave everything as false to be manually fixed later.
            $table->boolean('eligible_for_shirt')->default(false);
            $table->boolean('eligible_for_polo')->default(false);
        });
    }
}
