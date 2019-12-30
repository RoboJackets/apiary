<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFasetSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('faset_responses', static function (Blueprint $table): void {
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
        Schema::create('faset_surveys', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('question');
            $table->timestamps();
        });

        Schema::table('faset_responses', static function (Blueprint $table): void {
            $table->foreign('faset_survey_id')->references('id')->on('faset_surveys');
            $table->unsignedInteger('faset_survey_id')->after('response');
        });
    }
}
