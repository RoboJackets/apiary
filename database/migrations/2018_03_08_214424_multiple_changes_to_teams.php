<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->boolean('attendable')->after('founding_semester')->default(false);
            $table->boolean('hidden')->after('founding_semester')->default(false);
            $table->mediumText('short_description')->after('name')->nullable();
            $table->renameColumn('description', 'long_description');
            $table->dropColumn('founding_semester');
            $table->char('founding_year', 4)->after('description')->nullable();
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
            $table->dropColumn('attendable');
            $table->dropColumn('hidden');
            $table->dropColumn('short_description');
            $table->renameColumn('long_description', 'description');
            $table->dropColumn('founding_year');
            $table->char('founding_semester', 4)->after('long_description');
        });
    }
}
