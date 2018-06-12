<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSympaAndSlackLinksToTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('slack_channel_name')->after('description')->nullable();
            $table->string('slack_channel_id')->after('description')->nullable();
            $table->string('mailing_list_name')->after('description')->nullable();
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
            $table->dropColumn('slack_channel_name');
            $table->dropColumn('slack_channel_id');
            $table->dropColumn('mailing_list_name');
        });
    }
}
