<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFasetResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faset_responses', static function (Blueprint $table): void {
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
        Schema::dropIfExists('faset_responses');
    }
}
