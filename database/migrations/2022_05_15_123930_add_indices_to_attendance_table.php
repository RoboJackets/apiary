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
            $table->index(['attendable_type', 'attendable_id']);
            $table->index('gtid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', static function (Blueprint $table): void {
            $table->dropIndex(['attendable_type', 'attendable_id']);
            $table->dropIndex('gtid');
        });
    }
};
