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
        Schema::table('docusign_envelopes', static function (Blueprint $table): void {
            $table->unsignedInteger('sent_by')->nullable();

            $table->foreign('sent_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('docusign_envelopes', static function (Blueprint $table): void {
            $table->dropColumn('sent_by');
        });
    }
};
