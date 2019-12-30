<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartEndDatesToRecruitingCampaign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recruiting_campaigns', static function (Blueprint $table): void {
            $table->date('start_date')->after('notification_template_id')->default('1970-01-01 00:00:01');
            $table->date('end_date')->after('notification_template_id')->default('1970-01-01 00:00:02');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recruiting_campaigns', static function (Blueprint $table): void {
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
        });
    }
}
