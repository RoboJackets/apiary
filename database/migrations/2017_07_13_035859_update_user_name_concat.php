<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserNameConcat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->virtualAs("concat_ws(' ',`first_name`,`last_name`)")->change();
            $table->string('full_name')->virtualAs("concat_ws(' ',`first_name`,`middle_name`,`last_name`)")->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->virtualAs("CONCAT(first_name,' ',last_name)")->change();
            $table->string('full_name')->virtualAs("CONCAT(first_name,' ',middle_name,' ',last_name)")->change();
        });
    }
}
