<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SoftDeleteFasetResponses extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('faset_responses', static function (Blueprint $table): void {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faset_responses', static function (Blueprint $table): void {
            $table->dropColumn('deleted_at');
        });
    }
}
