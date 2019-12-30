<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccessOverrideFieldsToUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dateTime('access_override_until')->nullable();

            $table->unsignedInteger('access_override_by_id')->nullable()->comment('user_id of the user who entered access override');

            $table->foreign('access_override_by_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('access_override_until');
            $table->dropColumn('access_override_by');
        });
    }
}
