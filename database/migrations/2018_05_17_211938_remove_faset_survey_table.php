<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveFasetSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('faset_responses', function (Blueprint $table) {
            $table->dropForeign(['faset_survey_id']);
            $table->dropColumn('faset_survey_id');
        });

        Schema::dropIfExists('faset_surveys');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('faset_surveys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('question');
            $table->timestamps();
        });

        Schema::table('faset_responses', function (Blueprint $table) {
            $table->foreign('faset_survey_id')->references('id')->on('faset_surveys');
            $table->unsignedInteger('faset_survey_id')->after('response');
        });
    }
}
