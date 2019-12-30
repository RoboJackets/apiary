<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddA4PColumnToDuesPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dues_packages', static function (Blueprint $table): void {
            $table->boolean('available_for_purchase')->after('cost')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dues_packages', static function (Blueprint $table): void {
            $table->dropColumn('available_for_purchase');
        });
    }
}
