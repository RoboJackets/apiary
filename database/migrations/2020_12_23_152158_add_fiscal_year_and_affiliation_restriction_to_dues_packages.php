<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiscalYearAndAffiliationRestrictionToDuesPackages extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dues_packages', static function (Blueprint $table): void {
            $table->foreignId('fiscal_year_id')->nullable()->constrained();
            $table->foreignId('conflicts_with_package_id')->nullable()->constrained('dues_packages');
            $table->boolean('restricted_to_students')->nullable();
            $table->string('name')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dues_packages', static function (Blueprint $table): void {
            $table->dropForeign('dues_packages_fiscal_year_id_foreign');
            $table->dropForeign('dues_packages_conflicts_with_package_id_foreign');
            $table->dropColumn('fiscal_year_id');
            $table->dropColumn('conflicts_with_package_id');
            $table->dropColumn('restricted_to_students');
        });
    }
}
