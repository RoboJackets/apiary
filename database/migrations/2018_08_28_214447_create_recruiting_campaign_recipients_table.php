<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitingCampaignRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruiting_campaign_recipients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email_address');
            $table->string('source');
            $table->unsignedInteger('recruiting_campaign_id');
            $table->unsignedInteger('recruiting_visit_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamp('notified_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('recruiting_campaign_id')->references('id')->on('recruiting_campaigns');
            $table->foreign('recruiting_visit_id')->references('id')->on('recruiting_visits');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruiting_campaign_recipients');
    }
}
