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
        Schema::table('attendance', static function (Blueprint $table): void {
            $table->integer('gtid')->nullable()->change();
            $table->bigInteger('access_card_number')->nullable();
            $table->index('access_card_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', static function (Blueprint $table): void {
            $table->integer('gtid')->nullable(false)->change();
            $table->dropIndex('attendance_access_card_number_index');
            $table->dropColumn('access_card_number');
        });
    }
};
