<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFasetToRecruiting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename("faset_visits", "recruiting_visits");
        Schema::rename("faset_responses", "recruiting_responses");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename("recruiting_visits", "faset_visits");
        Schema::rename("recruiting_responses", "faset_responses");
    }
}
