<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitingCampaignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruiting_campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('status', ['new', 'in_progress', 'completed']);
            $table->unsignedInteger('notification_template_id')->nullable();

            $table->unsignedInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('notification_template_id')->references('id')->on('notification_templates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruiting_campaigns');
    }
}
