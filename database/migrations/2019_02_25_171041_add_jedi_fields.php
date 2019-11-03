<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJediFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dues_packages', function (Blueprint $table) {
            $table->timestamp('access_start')->after('effective_end')->nullable();
            $table->timestamp('access_end')->after('access_start')->nullable();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedInteger('project_manager_id')->nullable()->comment('user_id of the project manager');

            $table->foreign('project_manager_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dues_packages', function (Blueprint $table) {
            $table->dropColumn('access_start');
            $table->dropColumn('access_end');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('project_manager_id');
        });
    }
}
