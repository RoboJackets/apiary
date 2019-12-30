<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitingCampaignRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recruiting_campaign_recipients', static function (Blueprint $table): void {
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
     */
    public function down(): void
    {
        Schema::dropIfExists('recruiting_campaign_recipients');
    }
}
