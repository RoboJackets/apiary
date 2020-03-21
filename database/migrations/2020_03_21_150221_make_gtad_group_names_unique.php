<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeGtadGroupNamesUnique extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('majors', static function (Blueprint $table): void {
            $table->string('gtad_majorgroup_name')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('majors', static function (Blueprint $table): void {
            $table->dropIndex(['gtad_majorgroup_name']);
        });
    }
}
