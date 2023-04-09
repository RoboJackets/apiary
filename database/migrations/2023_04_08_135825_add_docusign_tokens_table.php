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
        Schema::create('docusign_tokens', static function (Blueprint $table): void {
            $table->string('type')->nullable(false);
            $table->text('token')->nullable(false);
            $table->timestamps();
            $table->timestamp('expires_at')->nullable(false);

            $table->primary('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docusign_tokens');
    }
};
