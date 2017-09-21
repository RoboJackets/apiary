<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropVirtualColumnsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('full_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')
                ->virtualAs("concat_ws(' ',`first_name`,`last_name`)")->nullable()->after('preferred_name');
            $table->string('full_name')
                ->virtualAs("concat_ws(' ',`first_name`,`middle_name`,`last_name`)")->after('preferred_name');
        });
    }
}
