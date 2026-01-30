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
        Schema::table('access_cards', static function (Blueprint $table): void {
            $table->unsignedBigInteger('access_card_number')->nullable(false)->primary()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_cards', static function (Blueprint $table): void {
            $table->id('access_card_number')->change();
        });
    }
};
