<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartEndDatesToRecruitingCampaign extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recruiting_campaigns', static function (Blueprint $table): void {
            $table->date('start_date')->after('notification_template_id')->default('1970-01-01 00:00:01');
            $table->date('end_date')->after('notification_template_id')->default('1970-01-01 00:00:02');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruiting_campaigns', static function (Blueprint $table): void {
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
        });
    }
}
