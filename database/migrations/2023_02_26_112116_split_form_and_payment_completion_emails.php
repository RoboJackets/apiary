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
        Schema::table('travel', static function (Blueprint $table): void {
            $table->dropColumn('completion_email_sent');
            $table->boolean('payment_completion_email_sent')->default(false);
            $table->boolean('form_completion_email_sent')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel', static function (Blueprint $table): void {
            $table->dropColumn('payment_completion_email_sent');
            $table->dropColumn('form_completion_email_sent');
            $table->boolean('completion_email_sent')->default(false);
        });
    }
};
