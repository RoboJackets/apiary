<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SoftDeleteFasetSurveys extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('faset_surveys', static function (Blueprint $table): void {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faset_surveys', static function (Blueprint $table): void {
            $table->dropColumn('deleted_at');
        });
    }
}
