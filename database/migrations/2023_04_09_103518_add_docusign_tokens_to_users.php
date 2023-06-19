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
            $table->text('docusign_access_token')->nullable();
            $table->timestamp('docusign_access_token_expires_at')->nullable();

            $table->text('docusign_refresh_token')->nullable();
            $table->timestamp('docusign_refresh_token_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn([
                'docusign_access_token',
                'docusign_access_token_expires_at',
                'docusign_refresh_token',
                'docusign_refresh_token_expires_at',
            ]);
        });
    }
};
