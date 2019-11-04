<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MultipleChangesToTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('slug')->after('name')->nullable();
            $table->boolean('attendable')->after('name')->default(false);
            $table->boolean('visible')->after('name')->default(false);
            $table->boolean('self_serviceable')->after('name')->default(false);
            $table->dropColumn('founding_semester');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('attendable');
            $table->dropColumn('visible');
            $table->dropColumn('self_serviceable');
            $table->dropColumn('founding_year');
            $table->char('founding_semester', 4)->after('name');
        });
    }
}
