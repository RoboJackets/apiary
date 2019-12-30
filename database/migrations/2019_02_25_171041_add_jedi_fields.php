<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJediFields extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dues_packages', static function (Blueprint $table): void {
            $table->timestamp('access_start')->after('effective_end')->nullable();
            $table->timestamp('access_end')->after('access_start')->nullable();
        });

        Schema::table('teams', static function (Blueprint $table): void {
            $table->unsignedInteger('project_manager_id')->nullable()->comment('user_id of the project manager');

            $table->foreign('project_manager_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dues_packages', static function (Blueprint $table): void {
            $table->dropColumn('access_start');
            $table->dropColumn('access_end');
        });

        Schema::table('teams', static function (Blueprint $table): void {
            $table->dropColumn('project_manager_id');
        });
    }
}
