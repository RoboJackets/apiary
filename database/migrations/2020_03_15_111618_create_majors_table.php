<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMajorsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('majors', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('display_name')->nullable();
            $table->string('gtad_majorgroup_name');
            $table->string('whitepages_ou');
            $table->string('school');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('majors');
    }
}
