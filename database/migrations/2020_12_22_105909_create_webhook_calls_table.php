<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWebhookCallsTable extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_calls', static function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->string('name');
            $table->text('payload')->nullable();
            $table->text('exception')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_calls');
    }
}
