<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFasetResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faset_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('response')->nullable();
            $table->unsignedInteger('faset_survey_id');
            $table->unsignedInteger('faset_visit_id');
            $table->timestamps();

            $table->foreign('faset_survey_id')->references('id')->on('faset_surveys');
            $table->foreign('faset_visit_id')->references('id')->on('faset_visits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('faset_responses', function (Blueprint $table) {
            $table->dropForeign(['faset_visit_id']);
            $table->dropForeign(['faset_survey_id']);
        });

        Schema::dropIfExists('faset_responses');
    }
}
