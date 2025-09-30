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
        Schema::create('sponsors', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('end_date');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('sponsor_domains', static function (Blueprint $table) {
            $table->id();
            $table->string('domain_name');
            $table->foreignId('sponsor_id')->constrained('sponsors')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsors');
        Schema::dropIfExists('sponsor_domains');
    }
};
