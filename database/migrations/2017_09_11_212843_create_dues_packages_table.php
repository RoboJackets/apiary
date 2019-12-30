<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDuesPackagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dues_packages', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->boolean('eligible_for_shirt')->default(false);
            $table->boolean('eligible_for_polo')->default(false);
            $table->timestamp('effective_start')->useCurrent();
            $table->timestamp('effective_end')->useCurrent();
            $table->decimal('cost');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('dues_packages');
    }
}
